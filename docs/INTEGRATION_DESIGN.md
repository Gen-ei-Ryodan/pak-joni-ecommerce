# INTEGRATION DESIGN — MForce API → Local DB

## Tujuan

MForce API sebagai **Master Data Provider** untuk produk motor. Data disinkron ke database lokal melalui background job, lalu website membaca dari database lokal.

---

## Arsitektur

```
MForce API ──(scheduler/manual)──→ MforceSyncService ──→ Local DB (items, brands, relasi)
                                                              │
User Browser ──(HTTP)──→ Controller ──(Eloquent)──→ Local DB ──→ Blade View
```

---

## Prinsip Utama

| Aturan | Keterangan |
|--------|-----------|
| API hanya untuk master data | Brand, Product, Variant, Gallery, Viewer360, Specification, Description |
| Tidak untuk operasional | Checkout, Cart, Stock, Harga, Order, Payment tetap milik lokal |
| Sync di background | Tidak memanggil API saat user browsing |
| UPSERT strategy | `updateOrCreate()` — ID stabil, Cart/Order tidak rusak |
| Harga milik lokal | Insert baru: price = null. Admin isi via Filament |
| Image: URL reference | Phase 1 simpan URL MForce. Phase 2 async download |

---

## Entity & Matching Keys

| Table | Matching Key | Note |
|-------|-------------|------|
| `items` | `mforce_id` (unique) | Dari `products[].id` |
| `item_colors` | `(item_id, mforce_id)` | Dari `variants[].id` |
| `item_images` | `(item_id, mforce_id)` | Dari `gallery[].id` |
| `item_specifications` | `(item_id, mforce_id)` | Dari `crc32(group_key)` |
| `item_360_images` | `(item_id, mforce_id)` | Dari `variants[*].viewer360[].id` |
| `brands` | `slug` (unique) | Dari `brands[].slug` |
| `categories` | `(category_type_id, slug)` | Dari `products[].category` |

---

## Sync Flow per Entity

### Brand
1. Fetch `GET /api/brands`
2. `Brand::updateOrCreate(['slug' => api.slug], [name, logo_path, is_active])`

### Item (Motor)
1. Fetch `GET /api/brands/{slug}/products?per_page=50`
2. Fetch `GET /api/motors/{id}` untuk detail (gallery, viewer360, description)
3. `Item::updateOrCreate(['mforce_id' => api.id], [master_fields_only])`
4. UPSERT relasi: colors, images, specifications, 360_images

### Fields yang di-sync (whitelist)

**Saat INSERT baru:**
- `name`, `slug`, `description`, `short_description`, `thumbnail_path`, `year`
- `category_type_id`, `brand_id`, `category_id`
- `status = 'active'`, `is_active = true`, `stock_status = 'ready'`
- `price = null` (admin isi manual)

**Saat UPDATE:**
- Hanya `name`, `slug`, `description`, `short_description`, `thumbnail_path`, `year`
- JANGAN sentuh: `price`, `stock`, `stock_status`, `status`, `is_active`, `sort_order`

### Relasi (UPSERT)

```php
// Colors — updateOrCreate by (item_id, mforce_id)
ItemColor::updateOrCreate(
    ['item_id' => $item->id, 'mforce_id' => $v['id']],
    ['name' => $v['name'], 'color_code' => $v['color'], 'image_path' => $v['image_path'], 'sort_order' => $v['sort_number']]
);

// Gallery — updateOrCreate by (item_id, mforce_id)
ItemImage::updateOrCreate(
    ['item_id' => $item->id, 'mforce_id' => $g['id']],
    ['path' => $g['url'], 'sort_order' => $g['sort_number']]
);

// Specifications — updateOrCreate by (item_id, mforce_id) where mforce_id = crc32(group_key)
ItemSpecification::updateOrCreate(
    ['item_id' => $item->id, 'mforce_id' => crc32($groupKey . '_' . $specKey)],
    ['group' => $groupName, 'key' => $label, 'value' => $value, 'sort_order' => $counter]
);

// 360 Images — updateOrCreate by (item_id, mforce_id)
Item360Image::updateOrCreate(
    ['item_id' => $item->id, 'mforce_id' => $v360['id']],
    ['path' => $v360['url'], 'sort_order' => $counter]
);
```

### Archive (stale records)

Setelah sync, cari entity yang `mforce_id`-nya TIDAK ada di response API → soft-delete:

```php
Item::whereNotNull('mforce_id')
    ->whereNotIn('mforce_id', $activeApiIds)
    ->update(['status' => 'inactive', 'is_active' => false]);
```

---

## Trigger Sync

| Trigger | Cara | Config |
|---------|------|--------|
| CLI | `php artisan mforce:sync` | — |
| CLI filter brand | `php artisan mforce:sync --brand=wmoto` | — |
| CLI dry-run | `php artisan mforce:sync --dry-run` | — |
| Admin Panel | Filament Action button | — |
| Scheduler | Auto via cron | `.env`: `MFORCE_SYNC_ENABLED=true`, `MFORCE_SYNC_INTERVAL=1440` |

---

## .env Configuration

```env
MFORCE_API_BASE_URL=https://mforce.co.id/api
MFORCE_SYNC_ENABLED=true
MFORCE_SYNC_INTERVAL=1440    # menit (1440 = 24 jam)
MFORCE_SYNC_TIME=02:00       # jam eksekusi harian
```

---

## File Manifest

```
app/
├── Services/
│   ├── MforceApiService.php        # HTTP client ke MForce API
│   └── MforceSyncService.php       # Logika UPSERT sync
├── Console/Commands/
│   └── MforceSyncCommand.php       # CLI artisan command
└── Filament/Pages/
    └── Dashboard.php               # Action button sync

config/
└── services.php                    # Config mforce.sync

database/migrations/
└── 2026_07_24_000001_add_mforce_id_to_items_and_relations.php

routes/
└── console.php                     # Scheduler config

.env                                # MFORCE_SYNC_* variables
```

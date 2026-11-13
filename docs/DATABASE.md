# DATABASE.md

## Ringkasan Database
Database relasional **MySQL** di shared hosting (cPanel). Skema dikelola via Eloquent Migrations (39 file). Database berada di `.env` production.

## Peringatan
- **JANGAN jalankan `migrate:fresh` / `db:wipe` di production** — banyak data customer nyata.
- Gunakan migration non-destruktif untuk perubahan skema.

### Tabel Kredit (Leasing) — migration `2026_11_13_000001`
| Tabel/Kolom | Tipe | Keterangan |
|-------------|------|------------|
| `credit_applications` | tabel baru | 1 pengajuan per order. `order_id` (FK cascade), `application_no` (unique, prefix `KRT`), data PO (`leasing_name`, `po_number`, `po_date`, `dp_amount` decimal 12,2), `stage` (tahap berjalan, default `spk`), `status` (`in_progress`/`completed`/`rejected`), 6 timestamp tahapan (`spk_at`, `survey_at`, `survey_approved_at`, `unit_preparation_at`, `payment_at`, `unit_pickup_at`), `notes` text. |
| `credit_payments` | tabel baru | DP & pelunasan dicatat **terpisah**: `credit_application_id` (FK cascade), `type` (`dp`/`pelunasan`), `payer_type` (`customer`/`leasing`), `amount`, `paid_at`, `method`, `reference`, `notes`, `recorded_by` (FK users, nullOnDelete). **Unique `(credit_application_id, type)`** → maks 1 DP + 1 pelunasan per pengajuan. |
| `orders.purchase_type` | string(16), default `cash`, index | `cash` atau `credit`; ditaruh setelah `payment_method`. Order kredit: `payment_method = 'credit'`, `is_indent = false`, `dp_amount = 0` (DP kredit hidup di `credit_applications.dp_amount`). |

### Tabel Orders — kolom `shipping_type`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `shipping_type` | string (default: 'courier') | Jenis pengiriman: `courier` (dikirim via kurir) atau `dealer_pickup` (ambil di dealer) |

### Perubahan `parts` (migration `2026_10_06_000001`)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `part_number` | string(64), nullable, index | Nomor part sesuai katalog pabrikan. Uniqueness divalidasi di form Filament (bukan DB unique, agar data lama tidak bermasalah). |
| `catalog_pdf_path` | string, nullable | Path PDF katalog part di disk `public` (`storage/app/public/parts/catalogs/`). Ditampilkan pada halaman detail sparepart. |

### Katalog Part — Galeri Gambar (migration `2026_10_19_000001`)
| Kolom/Tabel | Tipe | Keterangan |
|-------------|------|------------|
| `item_part_catalogs.brand_id` | FK `brands`, nullable | Merk pemilik katalog. Memungkinkan katalog tampil di alur `/pilih/sparepart` walau belum ada item sparepart. |
| `item_part_catalogs.slug` | string, nullable, index | Slug model untuk URL galeri (`/pilih/{categoryType}/brand-pdf/{brand}/{slug}`). |
| `item_part_catalog_images` | tabel baru | `item_part_catalog_id` (FK cascade), `path`, `sort_order` — halaman gambar hasil split PDF katalog. |

### Perubahan `items` (migration `2026_10_06_000002` + `2026_10_06_000003`)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `part_number` | string(64), nullable, index | Nomor part sesuai katalog pabrikan (sekarang identik dengan Part-side). |
| `catalog_pdf_path` | string, nullable | Path PDF katalog part di disk `public` (`storage/app/public/items/catalogs/`) untuk item sparepart. |
| `item_motor_compatibility` | tabel pivot | `item_id` (sparepart) + `motor_id` (kendaraan kompatibel). Dipakai untuk kompatibilitas sparepart item. |

Migrasi `2026_10_06_000003` menyalin data sparepart dari tabel `parts`/`part_variants`/`item_part` ke `items`/`item_colors`/`item_motor_compatibility` sehingga katalog sparepart hanya dipelihara di satu model (`Item` sparepart). Tabel `parts` tidak dihapus (fallback).

## Tabel Stok (terbaru)

### `stock_mutations` (migration `2026_08_06_071659`)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | |
| `stockable_type` | string(100) | morph — **100 char** agar lolos index MySQL |
| `stockable_id` | bigint | morph |
| `quantity` | int | delta stok (bisa negatif) |
| `previous_stock` | int | stok sebelum |
| `current_stock` | int | stok setelah |
| `type` | string | `manual`, `order`, `restock`, `adjustment` |
| `reference_type` | string|null | morph (misal Order) |
| `reference_id` | bigint|null | |
| `notes` | text|null | |
| `user_id` | bigint|null | admin pengubah |
| `created_at` / `updated_at` | timestamp | |

### Perubahan `item_colors` (migration `2026_08_06_144100`)
- `stock` (integer) — stok per varian warna
- `stock_updated_at` (datetime)

## Mapping Model Utama
- `Item` (items) → `ItemColor` (item_colors), stok di ItemColor.
- `Part` (parts) → `PartVariant` (part_variants), stok di PartVariant.
- `Order` → `OrderItem` (order_items, itemable polymorphic).
- `Cart` → `CartItem` (cart_items, itemable polymorphic).
- `StockMutation` → polymorphic ke Item / ItemColor / PartVariant.
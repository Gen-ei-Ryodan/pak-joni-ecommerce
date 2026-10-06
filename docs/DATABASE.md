# DATABASE.md

## Ringkasan Database
Database relasional **MySQL** di shared hosting (cPanel). Skema dikelola via Eloquent Migrations (38 file). Database berada di `.env` production.

## Peringatan
- **JANGAN jalankan `migrate:fresh` / `db:wipe` di production** — banyak data customer nyata.
- Gunakan migration non-destruktif untuk perubahan skema.

### Tabel Orders — kolom `shipping_type`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `shipping_type` | string (default: 'courier') | Jenis pengiriman: `courier` (dikirim via kurir) atau `dealer_pickup` (ambil di dealer) |

### Perubahan `parts` (migration `2026_10_06_000001`)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `part_number` | string(64), nullable, index | Nomor part sesuai katalog pabrikan. Uniqueness divalidasi di form Filament (bukan DB unique, agar data lama tidak bermasalah). |
| `catalog_pdf_path` | string, nullable | Path PDF katalog part di disk `public` (`storage/app/public/parts/catalogs/`). Ditampilkan pada halaman detail sparepart. |

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
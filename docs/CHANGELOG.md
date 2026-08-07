# CHANGELOG.md

## Catatan Perubahan Proyek

### v1.3.1 (2026-08-07) — Perbaikan Stok Tidak Berkurang Setelah Checkout

#### Diperbaiki
*   Stok varian tidak berkurang saat order dibayar via alur customer/Midtrans. Sebelumnya hanya admin (`Filament`/`Admin/OrderController`) yang memanggil `OrderService::markAsPaid` sehingga stok berkurang; jalur pembayaran customer (`PaymentService::markPaymentSuccess`, `simulateSuccessPayment`, `checkStatusFromMidtrans`) dan `Buyer/OrderController::payRemaining` menandai order `paid` langsung tanpa penurunan stok.
*   Sekarang semua jalur percaya pada status `paid` terpusat di `OrderService::markAsPaid` → `StockService::decreaseStockOnOrder` (ItemColor & PartVariant), sehingga stok selalu berkurang dan tercatat di `stock_mutations`.
*   Dihapus penurunan stok parsial di `CheckoutController::placeOrder` (sebelumnya hanya memotong `PartVariant` tanpa `StockMutation` dan mengabaikan `ItemColor`). Kini stok hanya berkurang saat order `paid`, konsisten dengan BUSINESS_RULES.md.
*   Dihapus `PaymentService::returnStock` pada order expired (tidak lagi relevan karena stok tak pernah direservasi saat placement).

### v1.3.0 (2026-08-06) — Stok Variant-Level + Deploy Fix

#### Ditambahkan
*   Stok dipindah ke **level varian** (`item_colors.stock`) untuk Item (motor/mobil/ATV), generik untuk semua kategori.
*   Tabel `stock_mutations` untuk mencatat seluruh riwayat perubahan stok (polymorphic: Item/ItemColor/PartVariant).
*   `StockService` — operasi terpusat: `adjustStock`, `setStock`, `getCurrentStock`, `getMutationHistory`, `decreaseStockOnOrder`.
*   `OrderService::markAsPaid` → **auto-decrease stok varian** saat order berstatus paid (untuk ItemColor & PartVariant).
*   Modal **"Kelola Stok"** di Filament ItemResource (`resources/views/filament/modals/item-stock-management.blade.php`).
*   Kolom `total_stock` (sum stok varian) & badge `stock_status` di table Item.
*   Storefront motors index/show + category-brand menampilkan stok per varian & total, badge dinamis, add-to-cart disabled jika varian belum dipilih.

#### Diubah
*   `ItemResource` form: input stok dipindah ke dalam Repeater `colors` per varian.
*   `ItemColor` model: `fillable` + cast `stock`/`stock_updated_at`, relasi `stockMutations()`.
*   `composer.json`: requirement PHP `^8.2` → `^8.3`, hapus override `platform.php`.
*   `deploy.sh`: ganti rsync (tidak tersedia di server) dengan git pull + `cp -r`.

#### Diperbaiki
*   `Class "Filament\Tables\Actions\Action" not found` → gunakan `Filament\Actions\Action` (Filament v4).
*   MySQL index key length error pada migration `stock_mutations` → `string('stockable_type', 100)`.
*   `composer install` gagal di server → perbaikan platform & requirement PHP.
*   `deploy.sh` gagal (rsync tidak ada) → pakai `cp -r`.

### v1.2.0 (2026-07-15)
#### Diubah
*   Perbaikan home page (warna icon/tombol, teks, whatsapp contact).
*   Auto-polling status pembayaran setelah Midtrans.
*   Dynamic Midtrans Snap.js URL.

### v1.1.0 (2026-07-15)

#### Ditambahkan
*   Opsi "Ambil di Dealer" pada checkout — buyer bisa memilih mengambil barang di dealer/workshop tanpa biaya ongkir.
*   Kolom `shipping_type` pada tabel orders untuk membedakan pengiriman kurir (`courier`) dan ambil di dealer (`dealer_pickup`).
*   Timeline "Siap Diambil" untuk pesanan dealer pickup (menggantikan "Shipped").
*   Tombol "Siap Diambil" di admin panel untuk pesanan dealer pickup.

#### Diubah
*   Halaman checkout address: Menambahkan card opsi "Ambil di Dealer" di bawah daftar alamat.
*   Alur checkout: Jika memilih dealer pickup, langsung skip ke halaman payment (lewati shipping step).
*   Tampilan order detail (buyer & admin): Menampilkan "Ambil di Dealer" untuk pesanan dealer pickup.
*   Filament OrderResource: Menambahkan kolom shipping_type, penanganan khusus dealer pickup di form dan display.

### v1.0.0 (2024-01-01)

#### Ditambahkan
*   Sistem autentikasi pengguna dengan Laravel.
*   Manajemen produk: CRUD operasi untuk produk.
*   Keranjang belanja: Menambah, menghapus, dan memperbarui item.
*   Checkout dan pembayaran: Integrasi dengan gateway pembayaran (Midtrans).
*   Manajemen pesanan: Pelacakan dan pembaruan status pesanan.
*   Admin dashboard (Filament): Panel kontrol untuk mengelola platform.

#### Diperbaiki
*   Bug validasi stok.
*   Keamanan CSRF pada form.
*   Performance: Optimasi query database.

### v0.9.0 (2023-12-01)

#### Ditambahkan
*   Versi awal platform e-commerce.
*   Fitur dasar: registrasi, login, manajemen produk sederhana.
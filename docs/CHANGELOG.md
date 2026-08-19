# CHANGELOG.md

## Catatan Perubahan Proyek

### Unreleased — Visual Dropdown Produk
*   Dropdown Produk pada navbar diubah menjadi panel visual dengan kartu Motor, ATV, dan Part menggunakan asset gambar lokal.
*   Link brand tetap tersedia di bawah kartu kategori, sementara Daftar Harga dan Part Katalog ditampilkan sebagai kartu katalog yang lebih besar dan mudah diklik.
*   Layout dibuat responsive untuk menu mobile tanpa mengubah route atau struktur menu navbar lainnya.

### v1.4.0 (2026-08-12) — OWASP Security Hardening

#### Kerentanan Kritis Diperbaiki
*   **Payment bypass via Midtrans `finish` redirect** (`MidtransController::finish`). Sebelumnya endpoint GET publik `/payment/midtrans/finish` menerima `order_id` + `transaction_status` dari query string dan memanggil `midtransCallbackHandler()` → `simulateSuccessPayment()` yang menandai order **paid tanpa verifikasi**. Kini endpoint hanya redirect UX; status pembayaran hanya diperbarui lewat webhook `notification` (signature-verified) atau endpoint `status` (verifikasi API server-side). Metode `midtransCallbackHandler` & `simulateSuccessPayment` dihapus.
*   **Open redirect pada login** (`LoginController::store`). `redirect` param dari query di-validasi: hanya path internal/same-host yang diperbolehkan.
*   **Ongkir manipulatif** (`CheckoutController::setShipping`). `shipping_cost` dari client tidak lagi dipercaya — harga ongkir selalu diambil ulang server-side dari Biteship untuk kurir/layanan terpilih.

#### Kerentanan Tinggi Diperbaiki
*   **Dependency CVEs** — `guzzlehttp/guzzle` 7.13.2 → 7.15.3 dan `league/commonmark` 2.8.2 → 2.10.0 (12 advisories termasuk HIGH) via `composer update`; `composer audit` kini clean.

#### Penguatan
*   `deploy.sh`: `.env` kini `chmod 600` (bukan 644/world-readable), storage `chmod 775`.
*   `trustProxies`: tidak lagi `*`; hanya mempercayai proxy yang terdaftar di env `TRUSTED_PROXIES` (mencegah spoofing header X-Forwarded-*).
*   Rate limiting `throttle:auth` (10/menit/IP) pada login/register/forgot-password/reset-password.
*   Middleware baru `SecurityHeaders` (X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS) dipasang ke grup `web`.
*   Validasi kode wilayah (`RegionController`) — mencegah manipulasi path pada API wilayah.
*   Regression tests baru `tests/Feature/SecurityRegressionTest.php` (payment bypass, open redirect, security headers).

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

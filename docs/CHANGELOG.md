# CHANGELOG.md

## Catatan Perubahan Proyek

### Unreleased — Fix Katalog Part PDF 404 & PDF di-track Git
*   **Fix 404/400 "Katalog Part (PDF)"** — symlink `public/storage` sebelumnya mengarah ke path project lain (`pak-joni-ecommerce`); dibuat ulang dengan `php artisan storage:link` sehingga URL `/storage/items/catalogs/*.pdf` berfungsi (HTTP 200).
*   **Perbaikan path katalog** — `items.catalog_pdf_path` untuk 368G dan Letbe Island disamakan dengan nama file fisik (`parts-catalog-368g.pdf`, `parts-catalog-letbe-island.pdf`) di DB dan seeder terkait.
*   **PDF katalog masuk git** — `storage/app/public/.gitignore` diubah agar `items/catalogs/**` ikut di-track; seluruh PDF katalog sparepart kini ter-commit.
*   **deploy.sh** — sync `storage/app/public` ke `PUBLIC_DIR/storage/app/public` dan rebuild symlink `public/storage` di server.

### Unreleased — Sparepart: Part Number, Katalog PDF & Navigasi Produk
*   **Form sparepart di admin** (`PartResource`) menambah field **Part Number** (`parts.part_number`, bisa dicari/diurutkan di tabel, divalidasi unique di form) dan upload **Katalog Part (PDF)** (`parts.catalog_pdf_path`, disk `public`, folder `parts/catalogs`, maks 10 MB, hanya `application/pdf`).
*   **Pilihan motor di form sparepart** — section "Compatible Products" diganti judul menjadi **"Motor & Kendaraan Terkait"** (multi-select per tipe kategori, tersimpan di pivot `item_part` lewat `Part::items()`), sehingga jelas bahwa part dipetakan ke motor/kendaraan yang sesuai.
*   **Detail sparepart** (`resources/views/buyer/parts/show.blade.php` + `Buyer\PartController::show`) menampilkan part number di bawah SKU dan section **"Katalog Part (PDF)"** berisi PDF milik part tersebut serta katalog PDF dari motor yang kompatibel (`item_part_catalogs`, hanya `is_active`).
*   **Navbar > Produk**: label kartu "Part" diganti menjadi **"Part Mechanic Support"**; kartu menu **"Katalog Harga Motor"** dan **"Katalog Harga Part Motor"** dihapus dari navbar storefront (`layouts/buyer.blade.php`) dan navbar halaman auth (`layouts/auth.blade.php`). Route `/daftar-harga` dan `/part-katalog` tetap ada dan link footer tidak diubah.
*   **Fix 500** `GET /kategori/{type}/all` (brand=all, termasuk target redirect `/parts` & `/sparepart`) — `BuyerPageController::categoryBrand` menjalankan closure `withCount` yang memakai `$brandModel->id` tanpa guard saat `$brandModel === null` → *Attempt to read property "id" on null*.
*   **Migration**: `2026_10_06_000001_add_part_number_and_catalog_pdf_to_parts_table`, `2026_10_06_000002_add_part_number_and_catalog_pdf_to_items_table`, dan `2026_10_06_000003_copy_parts_to_sparepart_items`.
*   **Penggabungan data Parts lama ke Item sparepart** (`2026_10_06_000003`): semua lapisan `parts` berkategori sparepart disalin ke `items` berdasarkan slug; termasuk: kategori (`part_categories` → `categories` bertipe sparepart), brand dari item motor kompatibelnya, stok varian (`part_variants` → `item_colors`), relasi daftar motor kompatibel (`item_part` → `item_motor_compatibility`). Migration non-destructive — tabel `parts` tetap utuh (HANYA data yang DISALIN ke items, tidak ada yang dihapus dari `parts`). Halaman sparepart motor (`/motor/{slug}?tab=parts`) kini hanya menampilkan Item sparepart kompatibel (satu sumber data).
*   **Tambahan (catat)**: Form **sparepart di admin Items** (`/admin/items/create?category_type_id=2`, sumber data katalog sparepart) juga sekarang punya section **"Data Sparepart"** (Part Number, upload Katalog PDF, pilih Motor Kompatibel). Halaman detail sparepart item (`/sparepart/{slug}`) menampilkan part number, tombol buka PDF, dan daftar motor kompatibel.
*   **Tests**: `tests/Feature/AdminPartFormTest.php` + `tests/Feature/AdminItemSparepartFormTest.php` — 8 test.

### Unreleased — Fix QRIS Production (order PJ260925TXC4PC)
*   **Alert "Gagal terhubung ke server" saat klik Bayar Sekarang** — root cause: route group pembayaran (`/payment/ocbc/qr`, `/payment/ocbc/status`, Midtrans status/snap-token) memakai `throttle:10,1`, sedangkan halaman finish meng-*poll* status tiap 5 detik (12/menit) dengan bucket yang sama. Request ke-11 balik **429 HTML**, `r.json()` di JS melempar, lalu jatuh ke pesan error generik. Fix: pakai named limiter `payment-actions` (60/menit/user) + JS menangani 429/non-JSON dengan pesan yang bisa ditindaklanjuti.
*   **QRIS kosong di halaman "Pesanan Berhasil Dibuat!"** — library QR bergantung CDN `cdnjs.cloudflare.com`; bila CDN gagal/diblokir, `new QRCode` melempar `ReferenceError` sehingga kotak QR kosong dan polling tidak jalan. Fix: `qrcode.min.js` di-vendor lokal ke `public/assets/js/` dan render dibungkus guard + fallback pesan error.

### Unreleased — Visual Dropdown Produk
*   Dropdown Produk pada navbar diubah menjadi panel visual dengan kartu Motor, ATV, dan Part menggunakan asset gambar lokal.
*   Link brand tetap tersedia di bawah kartu kategori, sementara Daftar Harga dan Part Katalog ditampilkan sebagai kartu katalog yang lebih besar dan mudah diklik.
*   Layout dibuat responsive untuk menu mobile tanpa mengubah route atau struktur menu navbar lainnya.
*   Navbar pada halaman login kini menggunakan panel visual dan asset kategori yang sama dengan navbar storefront (`MOTOR.jpeg`, `ATV.jpeg`, dan `PARTS.jpeg`).

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

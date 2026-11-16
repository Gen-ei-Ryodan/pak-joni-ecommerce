# CHANGELOG.md

## Catatan Perubahan Proyek

### Unreleased — Dokumen Transaksi (Faktur Motor, Faktur Sukucadang, Kwitansi, SPK PDF)
*   **Dependency baru**: `barryvdh/laravel-dompdf ^3.1` untuk render PDF dokumen transaksi.
*   **Migration dokumen**:
    *   `2026_11_09_000001_add_motor_invoice_fields_to_orders_table` — nomor faktur, no rangka, no mesin, NIK, penandatangan.
    *   `2026_11_16_032738_add_receipt_fields_to_orders_table` — kwitansi bukti pembayaran dengan terbilang rupiah otomatis (hanya aktif setelah status bayar lunas).
    *   `2026_11_16_044902_add_spk_fields_to_orders_table` — surat pemesanan motor (SPK) lengkap dengan skema cash/kredit, DP, tenor, angsuran, checklist berkas leasing, dan 9 klausul resmi.
*   **Admin Filament & Buyer**: Aksi cetak PDF untuk 4 dokumen transaksi resmi JOMOTO CENTER (Faktur Motor, SPK, Faktur Sukucadang, Kwitansi).

### Unreleased — Filter Admin Katalog Sparepart
*   **Halaman daftar sparepart admin** (`/admin/items/type/{sparepart}`, `ItemResource::table()`): filter **Motor** baru (`SelectFilter` multi-select pada relasi `compatibleMotors`, pivot `item_motor_compatibility`) — hanya tampil saat tipe halaman = `sparepart`, sembunyi otomatis di halaman motor/mobil/ATV. Filter **Brand** (`brand_id`) dan **search nama sparepart** (kolom `name` + `part_number` `searchable`) sudah ada sebelumnya dan ikut diverifikasi.
*   **Tests**: `tests/Feature/AdminItemSparepartFormTest.php` — 5 test baru: render field filter via HTTP (sparepart tampil, halaman motor tidak), visibilitas filter brand/motor/kategori, filter by brand, filter by motor (kompatibilitas `item_motor_compatibility`), dan search by nama. Baseline: 58 lulus; 7 gagal lama di `OcbcPaymentTest` (private key OCBC tidak ada di environment — sudah gagal sebelum perubahan ini).

### Unreleased — Pembelian Kredit (Leasing)
*   **Checkout kredit**: opsi pembayaran **"Kredit (Leasing)"** pada `/checkout/payment` (hanya bila keranjang memuat kendaraan — `ItemColor`; cart sparepart-only ditolak validasi). Order kredit dibuat dengan `orders.purchase_type = 'credit'`, `payment_method = 'credit'`, tanpa QRIS, tanpa skema indent DP 50%, dan otomatis membuat `credit_applications` (no. `KRT…`, tahap awal `spk`).
*   **Dua pihak pembayar dicatat terpisah** (`credit_payments`): **DP** dibayarkan customer ke dealer (pencatatan otomatis menandai order `paid` → stok berkurang lewat `OrderService::markAsPaid`), **pelunasan** dibayarkan leasing ke dealer (wajib setelah DP). Unique DB `(credit_application_id, type)` → maksimal 1 DP + 1 pelunasan. Tahap "Pembayaran" selesai hanya saat **keduanya** tercatat (`payment_at`).
*   **Timeline 6 tahap berurutan** (tidak boleh melompat, divalidasi `CreditService` + `CreditException`): **SPK → Survey → Persetujuan Survey → Persiapan Unit → Pembayaran → Pengambilan Unit** — tiap tahap punya timestamp sendiri (`spk_at` … `unit_pickup_at`), kolom `stage` = tahap pertama yang belum selesai. Persetujuan survey bisa **ditolak** → pengajuan `rejected` dan proses berhenti. PO leasing (nomor, tanggal, nama leasing, **nominal DP**) dicatat di tahap Persiapan Unit dan terkunci setelah DP tercatat.
*   **Admin Filament**: resource baru **Pengajuan Kredit** (`CreditApplicationResource`) — tabel + filter + badge navigasi, halaman view dengan section timeline, ringkasan PO, dan daftar pembayaran; 9 aksi header (Terbitkan SPK, Catat Survey, Setujui/Tolak Survey, Catat PO Leasing, Siapkan Unit, Catat DP, Catat Pelunasan, Konfirmasi Pengambilan Unit, Buka Pesanan). `OrderResource` menambah kolom/filter `purchase_type`, detail kredit per baris, section "Pembelian Kredit" di infolist, dan menyembunyikan aksi mark-paid manual untuk order kredit.
*   **Buyer**: banner status kredit + kartu **"Timeline Kredit"** di halaman `/my/orders/{order_no}` (status tahap, DP & pelunasan, deskripsi tahap); tombol "Bayar Sekarang" QRIS disembunyikan untuk order kredit; konfirmasi "Barang Sudah Diterima" ikut menyelesaikan pengajuan kredit (`CreditService::confirmPickupIfReady`). Halaman finish checkout menampilkan konfirmasi pengajuan kredit (tanpa QRIS).
*   **Config**: `config/leasing.php` — `subdomain` (`LEASING_SUBDOMAIN`), `access_mode` (`admin`/`portal`, default **`admin`**), stub `portal.*` untuk persiapan subdomain leasing. Keputusan apakah leasing mendapat login sendiri **masih tertunda** — seluruh tahap saat ini dikelola admin dealer. Riset lengkap: `docs/LEASING_RESEARCH.md`.
*   **Migration**: `2026_11_13_000001_create_credit_tables` (tabel `credit_applications`, `credit_payments`, kolom `orders.purchase_type`) — non-destructive, sudah dijalankan di DB lokal.
*   **Tests**: `tests/Feature/CreditPurchaseTest.php` — 10 test (checkout kredit + pembuatan pengajuan, opsi kredit tidak tersedia untuk cart part, DP & pelunasan terpisah + stok berkurang, tahap tidak bisa dilompati, survey ditolak menghentikan proses, pengambilan menyelesaikan order, timeline tampil di halaman buyer, halaman admin render, aksi admin maju per tahap, aksi admin menjalankan timeline penuh). Baseline: 52 lulus; 3 gagal lama di `OcbcPaymentTest` (private key OCBC tidak ada di environment).

### Unreleased — Part Katalog: Galeri Gambar per Model
*   **Alur baru part catalog**: `/pilih/sparepart` → pilih merk (WMOTO, SM SPORT, CF MOTO, ZONTES, ZEEHO) → `/pilih/sparepart/brand-pdf/{brand}` menampilkan **daftar model (list/tabel)** dengan tombol Lihat Gambar & Buka PDF → `/pilih/sparepart/brand-pdf/{brand}/{catalog}` menampilkan seluruh halaman katalog sebagai **grid kotak-kotak kecil** (klik untuk memperbesar lewat **lightbox**: navigasi prev/next, tombol Esc/panah) plus tombol download PDF asli.
*   **PDF dipecah menjadi gambar**: 1.339 halaman JPG (27 model, 5 merk) disimpan di `storage/app/public/items/part-catalogs/pages/{slug}/`. Sumber: folder `PART CATALOG` hasil split PDF katalog.
*   **Data**: tabel `item_part_catalogs` mendapat kolom `brand_id` + `slug`; tabel baru `item_part_catalog_images` menyimpan urutan halaman. Seeder `PartCatalogGallerySeeder` mengimpor 27 katalog + 1.339 gambar dengan pola **kosongkan dulu → insert ulang** (per merk, `brand_id` terkait saja) sehingga aman dijalankan berulang tanpa data dobel. Jika sebuah merk tidak punya gambar, data lama merk itu dibiarkan (tidak dihapus).
*   **Halaman `/part-katalog`** kini menampilkan tombol **Lihat Gambar (n)** di samping tombol download PDF.
*   **Admin Filament** (`PartCatalogResource`): tambah pilihan **Merk**, input **Slug URL**, kolom jumlah gambar, dan PDF tidak lagi wajib di-upload ulang saat edit.
*   **Deploy (cukup 3 langkah)**: gambar binary (~520 MB) kini **ikut di-track git** (`storage/app/public/.gitignore` diubah agar `items/part-catalogs/pages/**` ikut), jadi cukup `git pull` → `php artisan migrate --force` → `php artisan db:seed --force`. `PartCatalogGallerySeeder` dipanggil oleh `DatabaseSeeder`, dan **di production `php artisan db:seed` HANYA mengisi katalog part ini** (data dummy hanya jalan di local/development/testing) — tidak menyentuh item/order/data nyata lain.
*   **Tests**: `tests/Feature/PartCatalogGalleryTest.php` — 5 test (halaman pilih merk, daftar model, galeri, scoping brand, admin list/create/edit).

### Unreleased — Harga Sparepart dari Master Price List (satu seeder)
*   **Harga sparepart kini terisi** — 6.941 sparepart (dari 15 motor yang punya master price list resmi) mendapat `items.price` (HET) hasil ekstraksi 22 file `master-price-list-*.pdf` (per Oktober 2026).
*   **Satu seeder untuk production**: `database/seeders/SparepartPriceSeeder.php` — berisi seluruh data harga di dalam file (tidak butuh PDF/JSON eksternal), idempotent (aman dijalankan berulang). Jalankan di cPanel dengan `php artisan db:seed --class=SparepartPriceSeeder`.
*   Pemetaan harga per motor: `porter-125→porter`, `gy-150-aries`, `v16-plus`, `papio-x0-1/2`, `cfmoto-250-sr-lite→250-sr`, `cfmoto-250-clc`, `cfmoto-450-mt`, `morbius`, `greta`, `letbe-series-neon→letbe-neon`, `velora-150→velora`, `zeeho-ae6`, `zeeho-ae8`, `e-classic`, plus `cfmoto-450-clc→450-clc`, `cfmoto-clc-450-bobber→450-clc-bobber`, `cfmoto-450-sr` & `450sr-double-arm→450-sr` (4 motor ini belum punya sparepart, otomatis dilewati sampai katalognya diimpor).
*   4 price list (`250-clx`, `draco`, `sm-master`, `sm3`) tidak dimasukkan karena tidak ada motor dengan nama tersebut di database.
*   Motor yang belum punya master price list (368-E/K/G, Swiftbee, Island, City Sport, AE4 ABS, 800 MTX/Explore, 500 SR Voom, Papio Racer, Dual Lite) tetap `price = 0` — belum ada sumber harga resmi.

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

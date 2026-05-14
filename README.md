# E-Commerce Spare Part Motor

Aplikasi e-commerce spesialis spare part motor berbasis Laravel dengan fitur buyer dan admin panel.

## Tech Stack

| Lapisan | Teknologi |
|---------|-----------|
| Backend | Laravel 12.x |
| Database | MySQL (via Laravel migration) |
| Frontend | Blade templates, CSS murni, Vanilla JS |
| Image | WebP conversion via GD Library |

---

## Fitur Lengkap

### Status Label
| Label | Arti |
|-------|------|
| ✅ **Matang** | Sudah dikerjakan dengan baik, fungsional penuh |
| ⚠️ **Kurang Legit** | Sudah dikerjakan tapi masih ada kekurangan/bug |
| ❌ **Belum** | Belum dikerjakan sama sekali |

---

### 1. Buyer — Public Pages

| No | Fitur | Status | Keterangan |
|----|-------|--------|------------|
| 1.1 | **Homepage** | ✅ Matang | Banner carousel + highlights grid, navigasi lengkap |
| 1.2 | **About Page** | ✅ Matang | Halaman statis tentang toko |
| 1.3 | **Products Page** | ⚠️ Kurang Legit | Menampilkan 4 motor & 4 parts tanpa pagination, link ke halaman masing-masing |
| 1.4 | **Motor Catalog** | ✅ Matang | Grid motor dengan search, pagination 4 item per halaman |
| 1.5 | **Motor Detail** | ⚠️ Kurang Legit | Menampilkan info motor + gallery + parts terkompatibel dalam tab dinamis. Terdapat error "undefined array key" yang sudah diperbaiki |
| 1.6 | **Parts Catalog** | ✅ Matang | Grid part dengan search + filter kategori, pagination 4 item per halaman |
| 1.7 | **Part Detail** | ✅ Matang | Gallery gambar, varian selector, price display, add to cart, toggle wishlist, info motor kompatibel |

---

### 2. Authentication

| No | Fitur | Status | Keterangan |
|----|-------|--------|------------|
| 2.1 | **Register** | ✅ Matang | Form registrasi dengan validasi, redirect ke dashboard |
| 2.2 | **Login** | ✅ Matang | Form login dengan remember me, redirect intended |
| 2.3 | **Logout** | ✅ Matang | POST logout, session regenerate |
| 2.4 | **Forgot Password** | ⚠️ Kurang Legit | Halaman ada, route ada, tapi tidak terintegrasi dengan mail server |
| 2.5 | **Reset Password** | ⚠️ Kurang Legit | Halaman ada, route ada, tapi mail tidak terkirim (no mail config) |

---

### 3. Buyer — Dashboard & Akun

| No | Fitur | Status | Keterangan |
|----|-------|--------|------------|
| 3.1 | **Dashboard Buyer** | ⚠️ Kurang Legit | Menampilkan navigasi sidebar + KPI cards (jumlah orders, wishlist, addresses). Data statis tanpa chart |
| 3.2 | **Address Management** | ✅ Matang | CRUD penuh: create, edit, update, delete, set default address. Validasi lengkap |
| 3.3 | **Cart** | ✅ Matang | Add item dari halaman detail, update qty, remove item. Subtotal otomatis |
| 3.4 | **Wishlist** | ✅ Matang | Toggle add/remove dari halaman detail part, listing wishlist dengan pagination |

---

### 4. Checkout & Order

| No | Fitur | Status | Keterangan |
|----|-------|--------|------------|
| 4.1 | **Checkout — Address** | ⚠️ Kurang Legit | Pilih alamat dari daftar, bisa tambah alamat baru. Tapi tidak ada pilihan "kirim ke alamat berbeda" |
| 4.2 | **Checkout — Shipping** | ❌ Belum | Form input manual untuk courier, service, dan biaya kirim. **Tidak terintegrasi dengan API ongkir** (JNE/J&T/SiCepat dll) |
| 4.3 | **Checkout — Payment** | ❌ Belum | Hanya menampilkan ringkasan + tombol "Place Order". **Tidak terintegrasi dengan payment gateway** (Midtrans) |
| 4.4 | **Place Order** | ⚠️ Kurang Legit | Menyimpan order ke database, mengurangi stock, menghapus cart items. Tapi **tidak ada pembayaran riil** |
| 4.5 | **Order Finish** | ✅ Matang | Halaman konfirmasi order berhasil dengan nomor order |
| 4.6 | **Order List (Buyer)** | ✅ Matang | Tabel daftar order dengan status, total, link detail |
| 4.7 | **Order Detail (Buyer)** | ✅ Matang | Detail lengkap: items, subtotal, shipping, total. Tapi **tidak ada tracking pengiriman** |

---

### 5. Admin Panel

| No | Fitur | Status | Keterangan |
|----|-------|--------|------------|
| 5.1 | **Admin Dashboard** | ❌ Belum | Hanya menampilkan "Halo, name" tanpa data statistik (total orders, revenue, dsb) |
| 5.2 | **Motor Management** | ✅ Matang | CRUD penuh: create, edit, update, delete. Upload thumbnail & gallery images. Search, pagination |
| 5.3 | **Part Categories Management** | ⚠️ Kurang Legit | CRUD dengan field `group` (part/refitting/wearing), `name`, `sort_order`. Tapi seeder menggunakan group baru (oli, ban, dll) yang tidak sesuai dengan validation rule `in:part,refitting,wearing` |
| 5.4 | **Part Management** | ✅ Matang | CRUD penuh: SKU, nama, kategori, variants (ganda), gallery images, motor compatibility. Validasi kompleks |
| 5.5 | **Banner Management** | ✅ Matang | CRUD penuh dengan upload image WebP, sort order, active/inactive |
| 5.6 | **Order Management (Admin)** | ⚠️ Kurang Legit | List & detail order, update status (pending→confirmed→processing→shipped→delivered). Tapi **tidak ada filter status**, tidak ada通知 ke buyer |

---

### 6. Fitur Teknis & Infrastruktur

| No | Fitur | Status | Keterangan |
|----|-------|--------|------------|
| 6.1 | **Image Service (WebP)** | ✅ Matang | Service untuk konversi otomatis JPEG/PNG ke WebP saat upload. Digunakan di admin panel |
| 6.2 | **Database Migration** | ✅ Matang | 4 migration files (users, cache, jobs, ecommerce) dengan relasi lengkap |
| 6.3 | **Database Seeder** | ⚠️ Kurang Legit | Seeder komprehensif (users, banners, categories, motors, parts, variants, images, orders). Tapi path image menggunakan hardcoded string, bukan ImageService |
| 6.4 | **Role-based Middleware** | ✅ Matang | Middleware `role:admin` untuk proteksi route admin |
| 6.5 | **Dark Theme** | ✅ Matang | CSS variables untuk dark mode (`data-theme="dark"`) |
| 6.6 | **CSS Architecture** | ⚠️ Kurang Legit | 10 file CSS terpisah (reset, variables, layout, navbar, button, card, homepage, product, auth, dashboard). Beberapa CSS mungkin tidak terpakai |
| 6.7 | **Error Handling** | ⚠️ Kurang Legit | Validation errors tampil di view. Tapi tidak ada custom error pages (403, 404, 500) |
| 6.8 | **Asset Structure** | ✅ Matang | CSS, JS terpisah rapi di `public/assets/` |

---

### 7. Fitur yang Belum Dikerjakan Sama Sekali

| No | Fitur | Keterangan |
|----|-------|------------|
| 7.1 | **Payment Gateway Integration (Midtrans)** | Tabel `payments` sudah ada, provider diset "midtrans", tapi tidak ada integrasi API Midtrans sama sekali |
| 7.2 | **Shipping API Integration (Biteship)** | Tabel `shipments` sudah ada, provider diset "biteship", tapi tidak ada integrasi API ongkir |
| 7.3 | **Email Notification** | Tidak ada konfigurasi mail, tidak ada notifikasi order status via email |
| 7.4 | **Product Reviews & Ratings** | Tidak ada fitur review/rating untuk part |
| 7.5 | **Search Part by Motor Compatibility** | Tidak ada fitur "cari part yang cocok untuk motor tertentu" dari sisi buyer |
| 7.6 | **Product Lightbox / Zoom** | Gambar di detail hanya ditampilkan kecil tanpa preview besar |
| 7.7 | **Cart & Wishlist Badge (Navbar)** | Navbar tidak menampilkan jumlah item di cart/wishlist |
| 7.8 | **Sort Products** | Tidak ada opsi sorting (termurah, termahal, terbaru, A-Z) |
| 7.9 | **Stock Status Badge** | Tidak ada indikator visual "stock tersedia / habis" |
| 7.10 | **SEO Meta Tags** | Tidak ada meta description/OG tags dinamis per halaman |
| 7.11 | **Sitemap & Robots.txt** | robots.txt standar Laravel, tidak ada sitemap.xml |
| 7.12 | **REST API** | Tidak ada API endpoints untuk mobile/web service |
| 7.13 | **Multi-language** | Hanya bahasa Indonesia, tidak ada mekanisme translasi |
| 7.14 | **Unit / Feature Tests** | Hanya 3 file test minimal (AuthFlowTest, CartCheckoutTest, ExampleTest) dengan coverage sangat rendah |
| 7.15 | **Privacy & Terms Pages** | Link ada di footer, route tidak didefinisikan |
| 7.16 | **Pagination on Products Page** | Halaman `/produk` hanya menampilkan 4 item without pagination |
| 7.17 | **Order Tracking** | Tidak ada halaman tracking pengiriman untuk buyer |
| 7.18 | **Invoice PDF** | Tidak ada fitur download invoice PDF |
| 7.19 | **Recently Viewed Products** | Tidak ada fitur riwayat produk yang dilihat |
| 7.20 | **Related Products** | Tidak ada rekomendasi part terkait di halaman detail |
| 7.21 | **Bulk Stock Management** | Admin tidak bisa update stock secara batch |
| 7.22 | **Sales Report / Analytics** | Tidak ada grafik penjualan, laporan pendapatan, dll |
| 7.23 | **Export Data (CSV/Excel)** | Tidak ada fitur export order/part ke CSV atau Excel |

---

## Struktur Database

```
users
  ├── addresses (user_id)
  ├── carts (user_id)
  │   └── cart_items (cart_id, part_variant_id)
  ├── orders (user_id)
  │   ├── order_items (order_id, part_id, part_variant_id)
  │   ├── payments (order_id)
  │   └── shipments (order_id)
  └── wishlists (user_id, part_id)

part_categories
  └── parts (part_category_id)
      ├── part_images (part_id)
      ├── part_variants (part_id)
      └── motor_part (part_id, motor_id)

motors
  ├── motor_images (motor_id)
  └── motor_part (motor_id, part_id)

banners
```

## Relasi Key

| Model | Relasi | Type |
|-------|--------|------|
| Motor ↔ Part | `motor_part` | BelongsToMany |
| Part ↔ PartVariant | `part_id` | HasMany |
| Part → PartVariant (default) | `is_default = true` | HasOne |
| Part ↔ Motor | `motor_part` | BelongsToMany |
| User ↔ Order | `user_id` | HasMany |
| Order ↔ OrderItem | `order_id` | HasMany |
| Cart ↔ CartItem | `cart_id` | HasMany |

---

## Catatan Penting

### Image Path Convention
Semua gambar publik diakses melalui symlink `public/storage/`. Format path di database menggunakan prefix `storage/`:
- Banner: `storage/banner/wallpaper1.jpg`
- Product/Part: `storage/products/produk1.jpeg`
- Upload via admin: `storage/parts/thumbnails/uuid.webp`

### Error yang Sudah Diperbaiki
1. **"Undefined array key 'part'"** di halaman motor detail → disebabkan oleh tab yang hardcoded `['part', 'refitting', 'wearing']` sementara seeder menggunakan group kategori berbeda (`oli`, `ban`, dll). Diperbaiki dengan membuat tab dinamis dari key `$parts` collection.

2. **Gambar tidak muncul** → disebabkan oleh path tanpa prefix `storage/` dan `thumbnail_path` yang tidak di-set di seeder.

### Potensi Masalah
1. **PartCategory group validation** — Admin controller memvalidasi group harus `in:part,refitting,wearing`, tapi seeder menggunakan nilai lain (`oli`, `ban`, `kelistrikan`, `rem`, `body`). Admin tidak bisa membuat kategori dengan group tersebut melalui form.

---

## Cara Setup

```bash
# 1. Clone & install dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
# Edit .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate:fresh --seed

# 4. Storage link
php artisan storage:link

# 5. Run
php artisan serve
```

### Login Default
| Role | Email | Password |
|------|-------|----------|
| Admin | lihat `.env` (`ADMIN_EMAIL`) | lihat `.env` (`ADMIN_PASSWORD`) |
| Buyer | `budi@example.com` (atau buyer lain) | `password` |

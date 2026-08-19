# ARCHITECTURE.md

## Arsitektur Umum
Proyek menggunakan arsitektur **MVC (Model-View-Controller)** di atas **Laravel 13**, dengan **Service Layer** untuk logika bisnis. Admin panel memakai **Filament v4**, storefront memakai Blade + Livewire.

```
Laravel 13 (JOMOTO Center)
├── Admin Panel       app/Filament/Resources/*   (Filament v4)
├── Storefront        app/Http/Controllers/Buyer/* + resources/views/buyer/*
├── Services          app/Services/*             (logika bisnis terpusat)
├── Models            app/Models/*               (40+ Eloquent models)
├── Migrations        database/migrations/*      (38 migration)
└── Routes            routes/web.php
```

## Frontend
*   **Blade Templates:** Template engine Laravel untuk storefront (`resources/views/buyer/`).
*   **Livewire:** Komponen reaktif tertentu.
*   **Tailwind + Vite:** Styling dan asset bundling.
*   **Admin:** Filament v4 (render sendiri); modal custom `resources/views/filament/modals/`.
*   **Storefront navigation:** Navbar buyer berada di `resources/views/layouts/buyer.blade.php`, dengan perilaku dropdown di `public/assets/js/app.js` dan styling di `public/assets/css/navbar.css`. Dropdown Produk memakai kartu visual kategori dan kartu katalog, tanpa mengubah route kategori yang sudah ada.

## Backend
*   **Laravel Framework 13** dengan Eloquent ORM, routing, auth (session).
*   **Service Layer (pola utama):** logika bisnis terpusat di `app/Services/`, Controller tetap tipis.
*   **TIDAK memakai Repository Pattern** — ikuti Service Layer yang sudah ada.

### Services Utama
| Service | Fungsi |
|---------|--------|
| `StockService` | Semua operasi stok varian + mutasi (adjust, set, get history, decrease on order) |
| `OrderService` | Update status order, cancel, markAsPaid, auto-decrease stok saat paid |
| `PaymentService` | Integrasi pembayaran (Midtrans) |
| `BiteshipService` | Integrasi pengiriman |
| `ImageService` | Pengolahan gambar |

## Model Data Utama
- `Item` → variasi warna `ItemColor` (stok di ItemColor). Item = motor/mobil/ATV.
- `Part` → variasi `PartVariant` (stok). Part = sparepart.
- `StockMutation` — polymorphic ke Item/ItemColor/PartVariant, mencatat riwayat.
- `Order` / `OrderItem` — order item terhubung via polymorphic `itemable`.
- `Cart` / `CartItem` — polymorphic `itemable`.
- Content: Banner, Event, News, Career, Dealer, HeroVideo, ShowroomGallery, MapsLocation, WhyChooseUs, StoreAddress.

## Database
*   MySQL; Eloquent Migrations (38 file). **Jangan `migrate:fresh` di production.**
*   Tabel stok: `stock_mutations`, kolom `stock`/`stock_updated_at` di `item_colors`. Lihat DATABASE.md.

## Pola Desain
1.  **MVC** arsitektur utama.
2.  **Service Layer** memisahkan logika bisnis dari Controller.
3.  **Polymorphic relations** (`stockable`, `itemable`, `reference`) untuk generalisasi antar tipe.
4.  **Dependency Injection** di Service & Controller.
5.  **DB Transactions** untuk operasi yang konsisten (pengurangan stok).

## Alur Request
1.  Request masuk ke `routes/web.php`.
2.  Router → Controller (Buyer/* atau Filament).
3.  Controller/Service → Model untuk logika bisnis.
4.  Response: Blade view (storefront), Filament (admin).

## Alur Khusus — Stok
1.  Admin kelola stok per varian → `StockService::adjustStock` → update stok + `StockMutation`.
2.  Order dibayar (`OrderService::markAsPaid`) → `StockService::decreaseStockOnOrder` per item itemable ke `ItemColor`/`PartVariant`.
3.  **Semua jalur yang menandai order menjadi `paid` WAJIB lewat `OrderService::markAsPaid`** agar penurunan stok selalu tercatat. Jalur tersebut: admin (Filament `OrderResource` & `Admin/OrderController`), `PaymentService` (webhook `markPaymentSuccess`, `checkStatusFromMidtrans`) dan `Buyer/OrderController::payRemaining`. **Tidak ada lagi jalur simulasi payment** (`simulateSuccessPayment`/`midtransCallbackHandler` dihapus pada v1.4.0).
4.  Stok **tidak** dikurangi saat order dibuat (`CheckoutController::placeOrder`) — hanya saat order `paid`, sesuai BUSINESS_RULES.md.

## Security Middleware
*   `app/Http/Middleware/SecurityHeaders.php` — set baseline security headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS) pada grup `web`.
*   `app/Http/Middleware/RoleMiddleware.php` — alias `role` (unused saat ini; admin diatur via `User::canAccessPanel`).
*   Rate limiters di `AppServiceProvider`: `auth` (10/menit/IP), `midtrans-webhook`, `payment-actions`.
*   Trusted proxies dikonfigurasi via env `TRUSTED_PROXIES` (comma-separated) — bukan `*`.

## Deployment
*   `deploy.sh` → SSH ke `emerald.hidden-server.net:31988`, git pull + `cp -r` (rsync tidak ada).
*   PHP server 8.4.23 di path `/opt/alt/php84/usr/bin`.
*   Public dir: `/home/alurelab/jomotocenter.com`. Lihat DEPLOYMENT.md.

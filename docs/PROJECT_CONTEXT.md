# PROJECT_CONTEXT.md

## Nama Proyek
**JOMOTO Center** — E-commerce Platform Dealer Motor, Mobil, ATV & Sparepart (Client: Pak Joni)

## Tujuan Proyek
Platform e-commerce untuk dealer motor/mobil/ATV dan sparepart (JOMOTO Center). Menyediakan katalog produk, keranjang, checkout, pembayaran (Midtrans), pesanan, pengiriman (Biteship), serta admin panel Filament untuk pengelolaan master data, order, dan stok. Berjalan di production: `https://jomotocenter.com`.

## Lokasi & Repo
- Source: `/Users/10969sosho/PROJECT/CVSS/ON_PROGRES/PAK JONI/ECOMMERCE/`
- Git: `https://github.com/Gen-ei-Ryodan/pak-joni-ecommerce.git` (branch `main`)
- Production remote: `alurelab@emerald.hidden-server.net:31988`

## Tech Stack (Aktual)
*   **Backend:** PHP 8.3+ (local 8.5, server 8.4.23), **Laravel 13** (`laravel/framework ^13.0`)
*   **Admin Panel:** **Filament v4** (`filament/filament ^4.0`)
*   **Frontend:** Blade Templates + Livewire, Tailwind CSS, Vite
*   **Database:** MySQL (cPanel shared hosting)
*   **Auth:** Laravel (session based), Dusk untuk browser test
*   **Payment:** Midtrans Snap.js
*   **Shipping:** Biteship

## Modul Utama
1.  **Manajemen Produk (Item & Part):** Items = motor/mobil/ATV dengan varian warna (`ItemColor`), Parts = sparepart dengan varian (`PartVariant`). Relasi: images, specifications, 360 images, catalog, price list, kategori.
2.  **Manajemen Stok:** Stok per varian (`ItemColor`/`PartVariant`), riwayat mutasi (`stock_mutations`), auto-decrease saat order paid, kelola via modal di admin.
3.  **Keranjang & Wishlist:** Polymorphic (Item/ItemColor/Part/PartVariant).
4.  **Checkout & Pembayaran:** Midtrans, opsi ambil di dealer (`dealer_pickup`) atau kurir (`courier`).
5.  **Manajemen Pesanan:** Status lifecycle (pending → processing → shipped → completed; cancellable).
6.  **Manajemen Pengiriman:** Biteship.
7.  **Admin Dashboard (Filament v4):** 30+ resources (produk, part, order, banner, event, news, career, dealer, showroom, dll).

## Peran Pengguna
*   **Admin:** Mengelola seluruh platform via Filament (produk, stok, order, pengguna, kategori, konten).
*   **Pelanggan (Customer):** Menjelajah produk, cart, wishlist, checkout, melacak pesanan, quotation request.
*   **Guest:** Melihat katalog (termasuk stok), bisa lihat detail produk.

## Alur Bisnis Singkat
1.  Pelanggan menjelajah produk dan melihat stok per varian.
2.  Tambah ke keranjang (varian dipilih; jika stok 0, item tidak bisa ditambahkan).
3.  Checkout — pilih alamat + metode pengiriman (kurir / ambil di dealer).
4.  Pembayaran via Midtrans → status `paid`.
5.  Saat `paid`, **stok varian otomatis berkurang** + dicatat di `stock_mutations`.
6.  Admin proses order → shipped → completed.

## Struktur Folder Utama
*   `app/Filament/Resources/`: Admin resources (30+)
*   `app/Http/Controllers/Buyer/`: Storefront controllers (Motor, Part, Cart, Checkout, Order, dll.)
*   `app/Services/`: Logika bisnis (StockService, OrderService, PaymentService, BiteshipService, ImageService)
*   `app/Models/`: 40+ Eloquent models
*   `resources/views/`: Blade (buyer/ + filament modals)
*   `database/migrations/`: 38 migration
*   `routes/web.php`: Definisi rute
*   `docs/`: Dokumen proyek
*   `tests/`: Unit & feature tests

## Deployment
- Skrip: `deploy.sh` (SSH ke emerald.hidden-server.net port 31988, git pull + `cp -r` — rsync TIDAK tersedia di server).
- Public dir: `/home/alurelab/jomotocenter.com`. Lihat DEPLOYMENT.md untuk detail.
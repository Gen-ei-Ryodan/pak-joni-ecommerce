# API_REFERENCE.md

## Catatan Penting
Project ini **bukan API-only**. Tidak ada `routes/api.php` / REST API untuk public. Aplikasi adalah full-stack web (Blade storefront + Filament admin). Berikut endpoint HTTP yang tersedia (routes/web.php).

## Endpoint Publik (Guest)
| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `/` | Home |
| GET | `/about` | Tentang |
| GET | `/kategori/{categoryType}/{brand}` | Daftar produk per kategori/brand (menampilkan stok varian) |
| GET | `/cari` | Pencarian |
| GET | `/motors/{slug}` | Detail motor (varian, stok, gambar) |
| GET | `/parts/{part:slug}` | Detail part |
| GET | `/daftar-harga` | Daftar harga |
| GET | `/part-katalog` | Katalog part |
| GET | `/berita`, `/acara`, `/karir`, `/kegiatan-internal`, `/showroom` | Halaman konten |
| GET | `/whatsapp/{type}/{id}` | Redirect chat whatsapp |
| GET | `/regions/provinces`, `/regions/regencies/{provinceCode}`, `/regions/districts/{regencyCode}`, `/regions/villages/{districtCode}` | Wilayah Indonesia (JSON) |

## Endpoint Pembayaran (Midtrans)
| Method | URL | Fungsi |
|--------|-----|--------|
| POST | `/payment/midtrans/notification` | Webhook notifikasi status pembayaran dari Midtrans |
| GET | `/payment/midtrans/finish` | Redirect setelah pembayaran selesai |
| GET | `/payment/midtrans/unfinish` | Redirect jika pembayaran dibatalkan |

## Auth & Area Customer
- Registrasi/login, profil, address, cart, wishlist, checkout, order — semua via web session di `routes/web.php` (controllers `app/Http/Controllers/Buyer/`).

## Admin (Filament)
- Akses panel via Filament di path `/admin` (auth session, role admin).

## Catatan Integrasi
- **Midtrans** — webhook notification memicu pembaruan status pembayaran; saat `paid`, OrderService memanggil StockService untuk auto-decrease stok varian.
- **Biteship** — untuk perhitungan & pengiriman kurir.
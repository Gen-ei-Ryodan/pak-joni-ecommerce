# DEPLOYMENT.md

## Environment Target (Production)
| Item | Nilai |
|------|-------|
| SSH Host | `alurelab` (alias), server `emerald.hidden-server.net` |
| Port | `31988` |
| SSH login | `alurelab` |
| Repository dir | `/home/alurelab/repositories/pak-joni-ecommerce` |
| Public/Deploy dir | `/home/alurelab/jomotocenter.com` |
| Git remote | `https://github.com/Gen-ei-Ryodan/pak-joni-ecommerce.git` |
| Branch | `main` |
| URL | `https://jomotocenter.com` |

## Lingkungan Server (Shared Hosting cPanel)
- PHP server: **8.4.23** di `/opt/alt/php84/usr/bin` — skrip harus export PATH.
- **`rsync` TIDAK tersedia** di server → gunakan `cp -r` (bukan rsync).
- Docroot deploy: `/home/alurelab/jomotocenter.com` (bukan `/public_html`).

## Skrip Deploy: `deploy.sh` (dijalankan dari root project)
Alur dalam skrip:
1. SSH login ke `alurelab@emerald.hidden-server.net:31988`.
2. Set PATH php84.
3. `cd repository` → `git fetch origin main` → `git reset --hard origin/main`.
4. Copy via `cp -r`: `app bootstrap config database public resources routes` → public dir.
5. Copy `storage/app/public/.` → `PUBLIC_DIR/storage/app/public/` (PDF + gambar katalog yang di-track git), lalu rebuild symlink `public/storage`.
6. `cp` file: `artisan composer.json composer.lock package.json vite.config.js`.
7. `chmod -R 755 storage bootstrap/cache`.
8. `rm -f bootstrap/cache/packages.php services.php`.
9. `composer install --no-dev --optimize-autoloader --no-interaction`.
10. `php artisan optimize:clear`, `config:cache`, `route:cache`, `view:cache`, `event:cache`, `migrate --force` (belum termasuk `db:seed` — jalankan `php artisan db:seed --force` setelahnya untuk katalog part).

## Alur Deploy Lengkap (Lokal → Produksi)
```
LOKAL:
  git add -A && git commit -m "pesan"
  git push origin main
  ./deploy.sh

SERVER (otomatis oleh deploy.sh):
  git fetch && git reset --hard origin/main
  cp -r app/bootstrap/config/... → /home/alurelab/jomotocenter.com
  composer install --no-dev
  php artisan optimize + migrate --force
```

## Deploy Katalog Part (Galeri Gambar `/pilih/sparepart`)
Gambar halaman katalog (~520 MB, 1.339 JPG) sudah **di-track git**, jadi ikut `git pull` — tidak perlu upload manual.
Setelah kode + gambar ter-pull ke docroot, cukup 3 langkah:
```
php artisan migrate --force
php artisan db:seed --force
```
Catatan:
- `php artisan db:seed` di production **hanya** menjalankan `PartCatalogGallerySeeder` (data dummy hanya jalan di `local/development/testing`), jadi aman — tidak menyentuh item/order/data nyata lain.
- Seeder bersifat *kosongkan dulu → insert ulang* per merk (idempotent, tidak dobel) dan **tidak menghapus** data kalau gambar tidak ditemukan.
- Kalau memakai `./deploy.sh` (sudah menjalankan `git fetch/reset` + `migrate --force`), tinggal tambahkan `php artisan db:seed --force` setelahnya.
- Gambar dibaca dari `storage/app/public/items/part-catalogs/pages/{slug}/` di docroot.

## Migrate manual (jika perlu)
```
ssh -p 31988 alurelab "cd /home/alurelab/jomotocenter.com && php artisan migrate:status"
ssh -p 31988 alurelab "cd /home/alurelab/jomotocenter.com && php artisan migrate --force"
```
`[RUN]` = sudah jalan; `[Pending]` = belum. Hindari rollback di production.

## Verifikasi Post-Deploy
1. Buka halaman admin & storefront → pastikan HTTP 200.
2. Cek `storage/logs/laravel.log` untuk error baru.
3. Cek `migrate:status` — pastikan semua migration `[ * ] Ran`.

## Checklist (terakhir diverifikasi)
- [x] deploy.sh memakai `cp -r` (bukan rsync).
- [x] Gambar katalog part (`storage/app/public/items/part-catalogs/pages/**`) di-track git; `db:seed` di production hanya mengisi katalog part.
- [x] composer.json PHP `^8.3`, tanpa platform override.
- [x] Migration `stock_mutations` (batch 7) & `add_stock_to_item_colors` (batch 8) running di prod.
- [x] File ItemResource/StockService/OrderService/modal terdeploy sesuai commit terkini.
- [x] Prod pages 200, tanpa error di laravel.log.

## Keamanan & Catatan
- **Jangan commit `.env.dusk`** (mengandung APP_KEY). Pastikan di `.gitignore`.
- **Jangan jalankan `migrate:fresh` / `db:wipe`** di production (38 migration).
- Jangan copy berkas env lokal ke server (server pakai `.env` sendiri).
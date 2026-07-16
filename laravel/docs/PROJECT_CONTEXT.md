# PROJECT_CONTEXT.md

## Nama Proyek
E-commerce Platform

## Tujuan Proyek
Platform e-commerce ini bertujuan untuk menyediakan solusi lengkap bagi penjual dan pembeli untuk melakukan transaksi jual beli produk secara online. Ini mencakup fitur-fitur mulai dari penelusuran produk, keranjang belanja, pembayaran, hingga manajemen pesanan dan pengiriman.

## Tech Stack
*   **Backend:** PHP (Laravel Framework)
*   **Frontend:** Blade Templates (dengan kemungkinan integrasi JavaScript/Vue.js untuk interaktivitas)
*   **Database:** MySQL/PostgreSQL (relasional)
*   **Server:** Nginx/Apache
*   **Deployment:** Docker (opsional)

## Modul Utama
1.  **Manajemen Produk:** Penambahan, pengeditan, penghapusan, dan penelusuran produk.
2.  **Manajemen Pengguna:** Registrasi, login, profil pengguna, dan manajemen peran (Admin, Pelanggan).
3.  **Keranjang Belanja:** Menambah, menghapus, dan memperbarui item di keranjang.
4.  **Checkout & Pembayaran:** Proses checkout, integrasi gateway pembayaran.
5.  **Manajemen Pesanan:** Pelacakan pesanan, pembaruan status pesanan.
6.  **Manajemen Pengiriman:** Integrasi dengan layanan pengiriman.
7.  **Admin Dashboard:** Panel kontrol untuk mengelola produk, pesanan, pengguna, dll.

## Peran Pengguna
*   **Admin:** Mengelola seluruh platform (produk, pesanan, pengguna, kategori, dll.).
*   **Pelanggan:** Menjelajahi produk, melakukan pembelian, melacak pesanan.
*   **(Opsional) Penjual:** Mengelola produk dan pesanan mereka sendiri (jika ini adalah platform multi-vendor).

## Alur Bisnis Singkat
1.  **Registrasi/Login:** Pelanggan mendaftar atau masuk ke akun mereka.
2.  **Penelusuran Produk:** Pelanggan mencari dan melihat detail produk.
3.  **Tambah ke Keranjang:** Pelanggan menambahkan produk yang diinginkan ke keranjang belanja.
4.  **Checkout:** Pelanggan melanjutkan ke proses checkout, memilih alamat pengiriman dan metode pembayaran.
5.  **Pembayaran:** Pelanggan menyelesaikan pembayaran melalui gateway yang terintegrasi.
6.  **Konfirmasi Pesanan:** Pesanan dikonfirmasi dan penjual/admin diberitahu.
7.  **Pengiriman:** Produk dikemas dan dikirim ke pelanggan.
8.  **Penerimaan Pesanan:** Pelanggan menerima produk.

## Struktur Folder Utama
*   `app/`: Logika aplikasi utama (Models, Controllers, Providers, dll.)
*   `resources/`: View (Blade templates), aset (CSS, JS)
*   `database/`: Migrasi, seeder, factory
*   `public/`: File yang dapat diakses publik
*   `routes/`: Definisi rute aplikasi
*   `config/`: File konfigurasi
*   `storage/`: File yang diunggah, cache, log
*   `docs/`: Dokumentasi proyek (yang sedang kita buat ini)
*   `tests/`: Unit dan fitur tes

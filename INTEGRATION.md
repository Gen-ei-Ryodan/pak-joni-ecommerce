Integrasi API MForce
Tujuan

API MForce hanya digunakan sebagai Master Data Provider.

API tidak digunakan untuk:

Checkout
Stock
Harga
Cart
Order
Payment

Semua proses tersebut tetap menggunakan sistem yang sudah ada.

Penggunaan API

API hanya dipakai untuk mengambil:

Brand
Product
Variant
Gallery
Viewer 360
Specification
Video
Audio
Description
Saat Produk Ditampilkan

Jangan memanggil API MForce ketika user membuka halaman produk.

Website harus membaca data dari database lokal agar performa tetap cepat.

Saat Sinkronisasi

Buat service sinkronisasi yang berjalan di background (Scheduler/Queue).

Service ini bertugas mengambil perubahan dari MForce lalu memperbarui database lokal.

Jika MForce Menambah Produk Baru

Ketika sinkronisasi berjalan:

Ambil daftar produk terbaru.
Cek apakah produk tersebut sudah ada pada database lokal.
Jika belum ada:
Tambahkan produk ke database lokal.
Sinkronkan seluruh master data (gambar, spesifikasi, deskripsi, dll.).
Jangan mengubah logika checkout yang sudah ada.
Jika MForce Mengubah Produk

Ketika sinkronisasi berjalan:

Cari produk berdasarkan identitas unik (mforce_id, slug, atau mapping lain yang dipilih).
Jika ditemukan:
Perbarui hanya data master dari MForce.
Jangan mengubah data operasional sistem lokal.

Contoh data yang boleh diperbarui:

Nama
Deskripsi
Gallery
Cover
Video
Audio
Viewer 360
Specification
Data Lokal

Data yang menjadi milik sistem lokal tidak boleh diubah oleh proses sinkronisasi, misalnya:

Harga
Stock
Promo
Status Penjualan
Order
Checkout
Cart
Prinsip Utama

Sistem MForce adalah penyedia master data, sedangkan sistem kita adalah pemilik operasional bisnis.

Artinya:

Perubahan katalog mengikuti MForce.
Operasional toko tetap mengikuti database lokal.
Website dan checkout tidak bergantung pada kecepatan API MForce.
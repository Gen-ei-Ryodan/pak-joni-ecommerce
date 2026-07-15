# CHANGELOG.md

## Catatan Perubahan Proyek

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
*   Sistem autentikasi pengguna dengan Laravel Sanctum.
*   Manajemen produk: CRUD operasi untuk produk.
*   Keranjang belanja: Menambah, menghapus, dan memperbarui item.
*   Checkout dan pembayaran: Integrasi dengan gateway pembayaran.
*   Manajemen pesanan: Pelacakan dan pembaruan status pesanan.
*   Admin dashboard: Panel kontrol untuk mengelola platform.

#### Diubah
*   Struktur database: Optimasi indeks untuk query yang sering digunakan.
*   API response: Standardisasi format response untuk semua endpoint.
*   UI/UX: Perbaikan tampilan dan pengalaman pengguna.

#### Diperbaiki
*   Bug validasi stok: Memperbaiki issue dimana stok tidak divalidasi dengan benar saat checkout.
*   Keamanan: Memperbaiki vulnerability CSRF pada form tertentu.
*   Performance: Optimasi query database yang lambat.

### v0.9.0 (2023-12-01)

#### Ditambahkan
*   Versi awal platform e-commerce.
*   Fitur dasar: registrasi, login, manajemen produk sederhana.

#### Diketahui
*   Beberapa bug pada validasi stok.
*   UI/UX masih perlu banyak perbaikan.

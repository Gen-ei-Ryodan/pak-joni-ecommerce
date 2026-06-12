# CHANGELOG.md

## Catatan Perubahan Proyek

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

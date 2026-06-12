# BUSINESS_RULES.md

## Aturan Bisnis Umum

### 1. Manajemen Produk
*   **Produk tidak aktif:** Produk dengan status `inactive` tidak dapat ditambahkan ke keranjang atau dibeli.
*   **Stok habis:** Produk dengan stok `0` tidak dapat ditambahkan ke keranjang.
*   **Harga:** Harga produk tidak boleh negatif.
*   **Kategori:** Setiap produk harus memiliki setidaknya satu kategori.

### 2. Keranjang Belanja
*   **Kuantitas maksimum:** Kuantitas item di keranjang tidak boleh melebihi stok yang tersedia.
*   **Validasi stok:** Saat checkout, sistem harus memvalidasi bahwa stok masih tersedia untuk semua item di keranjang.
*   **Waktu kedaluwarsa:** Keranjang belanja dapat kedaluwarsa setelah periode waktu tertentu (misalnya, 30 menit).

### 3. Pesanan
*   **Pesanan tidak dapat dihapus:** Setelah pesanan dibuat, tidak dapat dihapus. Hanya dapat dibatalkan atau dikembalikan sesuai dengan kebijakan.
*   **Status pesanan:** Alur status pesanan harus mengikuti urutan: `pending` → `confirmed` → `processing` → `shipped` → `delivered` → `completed`. Status `cancelled` dapat terjadi kapan saja sebelum `shipped`.
*   **Pembatalan pesanan:** Pelanggan dapat membatalkan pesanan hanya jika statusnya masih `pending` atau `confirmed`.
*   **Pembaruan status:** Hanya admin yang dapat mengubah status pesanan setelah `confirmed`.

### 4. Pembayaran
*   **Konfirmasi pembayaran:** Pesanan hanya diproses setelah pembayaran dikonfirmasi.
*   **Waktu pembayaran:** Pembayaran harus diselesaikan dalam waktu tertentu (misalnya, 24 jam) setelah pesanan dibuat, atau pesanan akan dibatalkan secara otomatis.
*   **Metode pembayaran:** Hanya metode pembayaran yang aktif yang dapat digunakan.

### 5. Pengiriman
*   **Alamat pengiriman:** Alamat pengiriman harus lengkap dan valid sebelum pesanan dapat diproses.
*   **Biaya pengiriman:** Biaya pengiriman dihitung berdasarkan lokasi dan berat produk.
*   **Pelacakan pengiriman:** Nomor pelacakan harus diisi oleh admin setelah pesanan dikirim.

### 6. Pengguna & Peran
*   **Admin:** Memiliki akses penuh ke semua fitur dan data.
*   **Pelanggan:** Hanya dapat melihat dan mengelola data mereka sendiri (pesanan, profil, keranjang).
*   **Registrasi:** Email harus unik untuk setiap pengguna.
*   **Verifikasi email:** Pengguna harus memverifikasi email mereka sebelum dapat melakukan pembelian.

### 7. Diskon & Promosi
*   **Diskon berlaku:** Diskon hanya berlaku untuk produk yang memenuhi syarat dan dalam periode waktu yang ditentukan.
*   **Diskon tumpang tindih:** Jika ada beberapa diskon yang berlaku, sistem harus menerapkan diskon dengan nilai tertinggi (atau sesuai aturan prioritas).
*   **Kode promo:** Kode promo harus unik dan memiliki batas penggunaan.

### 8. Inventaris
*   **Pengurangan stok:** Stok produk dikurangi saat pesanan dikonfirmasi (bukan saat ditambahkan ke keranjang).
*   **Peningkatan stok:** Stok dapat ditingkatkan melalui manajemen inventaris admin.
*   **Stok rendah:** Sistem harus memberi peringatan ketika stok produk di bawah ambang batas tertentu.

### 9. Laporan & Analitik
*   **Data penjualan:** Hanya admin yang dapat mengakses laporan penjualan dan data analitik.
*   **Privasi data:** Data pelanggan tidak boleh dibagikan kepada pihak ketiga tanpa izin.

### 10. Keamanan
*   **Kata sandi:** Kata sandi harus memenuhi persyaratan keamanan minimum (panjang, karakter khusus).
*   **Sesi:** Sesi pengguna harus kedaluwarsa setelah periode tidak aktif.
*   **API rate limiting:** API harus memiliki batasan kecepatan untuk mencegah penyalahgunaan.

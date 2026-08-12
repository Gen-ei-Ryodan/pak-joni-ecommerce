# BUSINESS_RULES.md

## Aturan Bisnis Umum

### 1. Manajemen Produk
*   **Produk tidak aktif:** Produk dengan status `inactive` tidak dapat ditambahkan ke keranjang atau dibeli.
*   **Kategori:** Setiap produk harus memiliki setidaknya satu kategori.
*   **Item (motor/mobil/ATV):** memiliki varian warna (`ItemColor`); stok di level varian.
*   **Part (sparepart):** memiliki varian (`PartVariant`); stok di level varian.

### 2. Inventaris & Stok (TERBARU — variant-level)
*   **Unit stok terkecil:** stok dikelola pada varian (`ItemColor` untuk Item, `PartVariant` untuk Part), bukan pada produk utama.
*   **Stok tidak boleh negatif:** `StockService::adjustStock` melempar `InvalidArgumentException` jika hasil akhir negatif (dicek dalam DB transaction).
*   **Total stok tampilan:** total stok produk = sum stok semua varian.
*   **Stok habis:** Varian dengan stok `0` tidak dapat ditambahkan ke keranjang; tombol add-to-cart disabled sampai varian stok > 0 dipilih.
*   **Semua perubahan tercatat:** setiap perubahan stok menghasilkan `StockMutation` (riwayat: previous_stock, current_stock, quantity, type, user, reference).
*   **Tipe mutasi:** `manual` (admin), `order` (auto saat paid), `restock`, `adjustment`.
*   **Pengurangan stok saat order:** stok dikurangi **saat order berstatus `paid`** (bukan saat masuk keranjang), di `OrderService::markAsPaid` → `StockService::decreaseStockOnOrder`.
*   **Gagal penurunan stok tidak menggagalkan order:** jika stok kurang, exception di-catch + `report()`, order tetap berlanjut.
*   **Kuantitas maksimum keranjang:** kuantitas item di keranjang tidak boleh melebihi stok varian yang tersedia.
*   **Validasi stok saat checkout:** sistem harus memvalidasi bahwa stok masih tersedia untuk semua item di keranjang.

### 3. Keranjang Belanja
*   **Polymorphic:** cart item dapat berupa Item/ItemColor/Part/PartVariant.
*   **Waktu kedaluwarsa:** keranjang belanja dapat kedaluwarsa setelah periode waktu tertentu.

### 4. Pesanan
*   **Pesanan tidak dapat dihapus:** Setelah pesanan dibuat, tidak dapat dihapus. Hanya dapat dibatalkan atau dikembalikan sesuai kebijakan.
*   **Status pesanan (aktual):** `pending` → `processing` → `shipped` → `completed`. Status `cancelled` dapat terjadi; ada penanganan `waiting_stock` untuk part indent.
*   **Pembatalan pesanan:** pelanggan dapat membatalkan pesanan hanya jika statusnya masih memungkinkan (via `canTransitionTo`).
*   **Pembaruan status:** admin yang mengubah status (via OrderService).

### 5. Pembayaran
*   **Konfirmasi pembayaran:** pesanan hanya diproses setelah pembayaran dikonfirmasi (status `paid`).
*   **Stok berkurang saat paid:** transisi ke `paid` memicu pengurangan stok varian.
*   **Metode pembayaran:** Midtrans (Snap.js) dengan auto-poll status pembayaran.
*   **Verifikasi pembayaran (WAJIB):** status pembayaran **hanya** diperbarui via (a) webhook `notification` yang signature-nya diverifikasi, atau (b) endpoint `status` yang memverifikasi ke API Midtrans server-side. Endpoint redirect `finish`/`unfinish`/`error` **tidak boleh** mengubah status pembayaran — ia murni redirect UX.
*   **Tidak ada pembayaran simulasi:** `simulateSuccessPayment`/`midtransCallbackHandler` tidak digunakan lagi.

### 6. Pengiriman
*   **Alamat pengiriman:** harus lengkap dan valid sebelum pesanan diproses.
*   **Ambil di Dealer:** pelanggan dapat memilih "Ambil di Dealer" saat checkout; tidak dikenakan biaya ongkir dan melewati langkah pemilihan kurir.
*   **Shipping type:** `courier` (dikirim) atau `dealer_pickup` (ambil di dealer).
*   **Pelacakan pengiriman:** nomor resi diisi admin saat pesanan dikirim.
*   **Ongkir (WAJIB):** harga ongkir **tidak pernah** diambil dari input client. Nilai `shipping_cost` selalu dihitung ulang server-side dari Biteship untuk kurir/layanan yang dipilih (`CheckoutController::serverShippingCost`). Jika tidak ada rate yang cocok, checkout ditolak.

### 7. Pengguna & Peran
*   **Admin:** akses penuh ke semua fitur dan data (termasuk kelola stok per varian).
*   **Pelanggan:** hanya mengelola data sendiri (pesanan, profil, keranjang, wishlist).
*   **Registrasi:** email harus unik.

### 8. Konten & Lainnya
*   Banner, promo, event, news, career, dealer, showroom, maps location dikelola via Filament.
*   Harga tidak boleh negatif.

### 9. Keamanan
*   Kata sandi aman, CSRF protection, validasi input, hindari XSS dengan Blade escaping.
# DATABASE.md

## Ringkasan Database
Database untuk platform e-commerce ini menggunakan struktur relasional.

### Tabel Orders
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `shipping_type` | string (default: 'courier') | Jenis pengiriman: `courier` (dikirim via kurir) atau `dealer_pickup` (ambil di dealer)

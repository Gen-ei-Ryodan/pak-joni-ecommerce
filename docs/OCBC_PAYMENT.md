# OCBC/MTI QRIS Payment

## Status

Integrasi ini menggantikan Midtrans dengan QRIS MPM dari PT Mitra Transaksi Indonesia (MTI/Yokke). Implementasi kode sudah tersedia di branch `main`; production belum diaktifkan sampai credential UAT dan aturan signature callback dikonfirmasi.

Pemeriksaan hosting pada 9 September 2026:

- Host: `emerald.hidden-server.net` sebagai user `alurelab`.
- Laravel production: `13.19.0`.
- Repository: `/home/alurelab/repositories/pak-joni-ecommerce`.
- Deployment aktif: `/home/alurelab/jomotocenter.com`.
- Route OCBC, konfigurasi OCBC, public key, dan dokumen ini belum terdeploy.
- Production saat ini masih hanya memiliki route Midtrans.

## URL Merchant

URL production yang diberikan ke OCBC/MTI sesuai technical document:

```text
https://jomotocenter.com/v1.0/qr/qr-mpm-notify
```

Endpoint tersebut akan menerima `POST` notifikasi pembayaran dari MTI dan harus mengembalikan:

```json
{
  "responseCode": "2005200",
  "responseMessage": "Successful"
}
```

Untuk staging, gunakan domain HTTPS publik yang aktif, misalnya:

```text
https://staging.jomotocenter.com/v1.0/qr/qr-mpm-notify
```

Jangan gunakan `localhost` karena tidak dapat diakses MTI.

## URL API MTI

| Environment | Base URL |
|---|---|
| DEV | `https://dev.yokke.co.id:7778` |
| TEST | `https://tst.yokke.co.id:7778` |
| PROD | `https://api.yokke.co.id:7778` |

Endpoint:

```text
POST /qr/v2.0/access-token/b2b
POST /v2.0/qr/qr-mpm-generate
POST /v3.0/qr/qr-mpm-query
POST /v3.0/qr/qr-mpm-cancel
```

## Key Pair

Key pair RSA 2048-bit lokal dibuat di:

```text
storage/app/private/ocbc-client-private.pem
storage/app/private/ocbc-client-public.pem
```

Fingerprint SHA-256 public key saat ini:

```text
3c8c5938b51f03b4bf3a1e9fc3f88defd8a2de865f466eaf9c8d465c252535aa
```

Kirim isi `ocbc-client-public.pem` kepada OCBC/MTI untuk registrasi. Private key hanya disimpan di server dan tidak boleh:

- Dikirim ke OCBC/MTI.
- Masuk ke Git.
- Dimasukkan ke `public/`, Blade, atau JavaScript.
- Dikirim melalui chat/email biasa.

Private key saat ini sudah berada di direktori yang di-ignore Git. Saat deployment, transfer melalui kanal aman dan set permission `600`.

## Environment Variable

Tambahkan nilai berikut ke `.env` server, bukan ke repository:

```dotenv
OCBC_BASE_URL=https://tst.yokke.co.id:7778
OCBC_CLIENT_KEY=
OCBC_CLIENT_SECRET=
OCBC_MERCHANT_ID=
OCBC_TERMINAL_ID=
OCBC_PARTNER_ID=
OCBC_CHANNEL_ID=02
OCBC_PRIVATE_KEY_PATH=/absolute/path/to/ocbc-client-private.pem
OCBC_NOTIFY_URL=https://jomotocenter.com/v1.0/qr/qr-mpm-notify
```

`OCBC_CLIENT_KEY` adalah identifier merchant, sedangkan private key digunakan untuk signature RSA. `OCBC_CLIENT_SECRET` digunakan untuk signature transaksi jika memang dikonfirmasi oleh OCBC/MTI.

## Alur Pembayaran

1. Order dibuat dengan status `unpaid`.
2. Server meminta access token dengan `X-CLIENT-KEY`, timestamp, dan signature RSA.
3. Server memanggil QR generation dengan service code `47`.
4. `qrContent` ditampilkan kepada buyer.
5. MTI mengirim notifikasi ke URL merchant.
6. Status `latestTransactionStatus = "00"` diverifikasi dan diproses melalui `OrderService::markAsPaid()`.
7. Status selain `00` tidak boleh menandai order sebagai paid.

## X-EXTERNAL-ID

`X-EXTERNAL-ID` hanya dikirim **client → Yokke**; Yokke tidak mengembalikannya di response header, jadi `generateQr()` membuat nilainya sendiri (format `YmdHis` + digit = 15 digit numeric) lalu menyimpannya ke `payments.payload.external_id`. Nilai itulah yang dikirim ulang sebagai `originalExternalId` pada `query()`.

Bila `payload.external_id` kosong (pembayaran dibuat sebelum fix, atau format non-numeric), `query()` fallback ke external id baru berformat tanggal. Gejala lama: `4005101 Invalid Field Format` pada serviceCode `51`.

## Webhook `qr-mpm-notify`

`OcbcController::notify()` mencatat `request()->all()`, seluruh headers, dan raw body ke log (`OCBC notify received` / `OCBC notify raw body`) supaya payload persis dari MTI selalu bisa direview.

`processNotification()` mencari payment bertingkat (berhenti di kecocokan pertama, semua tier wajib lolos cek jumlah `amount`):

1. Exact: `originalReferenceNo` / `referenceNo` / `originalPartnerReferenceNo` / `partnerReferenceNo` / `additionalInfo.*` / header `X-EXTERNAL-ID` dicocokkan ke `provider_reference`, `payload.partner_reference_no`, dan `payload.external_id`. Nilai dinormalisasi bila terkirim sebagai JSON string/array.
2. Fuzzy: referensi beda ≤ 2 digit (termasuk beda leading zero), dibatasi pembayaran 2 hari terakhir + amount cocok.
3. Fallback terakhir: `additionalInfo.merchantId` + `terminalId` vs `payload.merchant_id` / `payload_terminal_id` (logged sebagai warning).

Tier 2/3 hanya jalan kalau amount cocok, supaya notification tidak pernah mengkaitkan order yang salah.

Dokumen MTI memiliki dua aturan signature yang berbeda:

- Request transaksi: HMAC-SHA512 menggunakan `ClientSecret`.
- Notify merchant: tertulis `SHA256withRSA(clientSecret)`.

Konfirmasi tertulis dari OCBC/MTI diperlukan untuk memastikan apakah callback diverifikasi menggunakan public key MTI atau signature RSA merchant. Jangan mengaktifkan pembayaran production sebelum hal ini jelas.

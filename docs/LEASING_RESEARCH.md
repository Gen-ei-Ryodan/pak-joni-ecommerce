# LEASING_RESEARCH.md — Riset Leasing (Pembelian Kredit / Kredit Kendaraan)

Dokumen riset untuk fitur **pembelian kredit (leasing)** JOMOTO Center.
Menjelaskan: alur kerja sama dealer ↔ leasing, proses PO / kredit / DP / pelunasan,
serta data & dokumen yang dibutuhkan dari pihak leasing.

Terkait: `config/leasing.php`, `App\Services\CreditService`, resource admin
`CreditApplicationResource`, migration `2026_11_13_000001_create_credit_tables`.

---

## 1. Ringkasan Alur (Overview)

Pembelian kredit melibatkan **dua pihak pembayar** ke dealer:

| Pihak | Membayar | Kapan | Dicatat sebagai |
|-------|----------|-------|-----------------|
| **Customer** | DP (uang muka) ke dealer | Setelah leasing menerbitkan PO (nominal DP fix) & unit disiapkan | `credit_payments.type = dp`, `payer_type = customer` |
| **Leasing** | Pelunasan (pelunasan sisa harga unit) ke dealer | Setelah DP tercatat | `credit_payments.type = pelunasan`, `payer_type = leasing` |

Alur besar:

```
Customer checkout kredit
   → Pengajuan kredit terbentuk (stage: SPK)
   → [1] SPK diterbitkan
   → [2] Survey oleh leasing
   → [3] Persetujuan Survey (disetujui / ditolak)
   → [4] Persiapan Unit (+ PO leasing: nominal DP ditentukan)
   → [5] Pembayaran (DP oleh customer → pelunasan oleh leasing)
   → [6] Pengambilan Unit (selesai)
```

---

## 2. Alur Kerja Sama Dealer ↔ Leasing

### 2.1 Peran
- **Dealer (JOMOTO)**: menerima order, menyiapkan unit, menerima DP & pelunasan, menyerahkan unit.
- **Customer**: mengajukan kredit, membayar DP ke dealer, mengambil unit.
- **Leasing**: memproses pengajuan (survey, kredit approval), menerbitkan PO berisi nominal DP, membayar pelunasan ke dealer.

### 2.2 Uraian Tahapan (6 tahap — urutan wajib, tidak boleh melompat)

| # | Tahap | Siapa yang bertindak | Keterangan |
|---|-------|----------------------|------------|
| 1 | **SPK** | Dealer/admin | Surat Pemesanan Kendaraan diterbitkan sebagai bukti pemesanan unit untuk proses kredit. |
| 2 | **Survey** | Leasing (dicatat admin) | Survey ke lokasi/dokumen customer selesai dilakukan. |
| 3 | **Persetujuan Survey** | Leasing (dicatat admin) | Hasil survey **disetujui** → lanjut; atau **ditolak** → pengajuan berstatus `rejected` dan proses berhenti. |
| 4 | **Persiapan Unit** | Dealer/admin | Unit disiapkan. **PO leasing dicatat di tahap ini** (nomor PO, tanggal, nama leasing, **nominal DP**). |
| 5 | **Pembayaran** | Customer (DP) + Leasing (pelunasan) | DP dibayarkan customer ke dealer lebih dulu; pelunasan menyusul dari leasing. Tahap ini selesai hanya jika **keduanya** tercatat. |
| 6 | **Pengambilan Unit** | Customer | Unit diserahkan; pengajuan kredit `completed` dan order ikut `completed`. |

Aturan tahapan diimplementasikan di `CreditService` (validasi `assertStage`) dan
diuji di `tests/Feature/CreditPurchaseTest.php`.

### 2.3 Dua jalur pembayaran (dicatat TERPISAH)
1. **DP** — customer bayar langsung ke dealer. Saat DP dicatat:
   - order ditandai `paid` lewat `OrderService::markAsPaid` (stok unit berkurang),
   - nominal DP mengikuti hasil PO leasing (`credit_applications.dp_amount`).
2. **Pelunasan** — leasing bayar ke dealer. **Wajib setelah DP tercatat.**
   - Hanya boleh satu kali, sama seperti DP (unique DB: `(credit_application_id, type)`).
   - Setelah keduanya ada, tahap "Pembayaran" selesai (`payment_at` terisi).

Order status `paid` ≠ tahap pembayaran selesai: order `paid` saat DP tercatat,
tahap "Pembayaran" selesai hanya saat DP **dan** pelunasan sama-sama tercatat.

---

## 3. PO, Kredit Approval, DP, Pelunasan

### 3.1 PO (Purchase Order) Leasing
PO adalah dokumen dari leasing yang **menentukan nominal DP** yang harus dibayarkan
customer ke dealer. Dicatat via aksi admin **"Catat PO Leasing"** pada
`/admin/credit-applications/{id}`:

| Field | Keterangan |
|-------|------------|
| `leasing_name` | Nama perusahaan leasing (wajib) |
| `po_number` | Nomor PO (wajib) |
| `po_date` | Tanggal PO (wajib) |
| `dp_amount` | Nominal DP > 0 dan ≤ total order (wajib) |

PO **boleh diperbaiki** selama DP belum tercatat; setelah DP tercatat, PO terkunci.

### 3.2 Kredit Approval (Persetujuan Kredit)
- **Disetujui** → tahap "Persetujuan Survey" selesai, proses lanjut ke persiapan unit.
- **Ditolak** → `credit_applications.status = rejected`, notes berisi alasan.
  Pengajuan yang ditolak **tidak dapat dilanjutkan** (semua aksi berikutnya ditolak
  dengan `CreditException`).

### 3.3 DP (Down Payment)
- Dibayarkan **customer → dealer** (transfer/cash, dicatat manual oleh admin
  beserta reference/bukti).
- Nominal mengikuti PO leasing; validasi: > 0, tidak melebihi total pesanan.
- Sekali catat → tidak bisa dicatat ulang.
- Pencatatan DP otomatis menandai order `paid` (pemicu pengurangan stok).

### 3.4 Pelunasan (Pelunasan oleh Leasing)
- Dibayarkan **leasing → dealer**, dicatat terpisah dari DP dengan `payer_type = leasing`.
- Prasyarat: DP sudah tercatat.
- Sekali catat → tidak bisa dicatat ulang; setelah DP + pelunasan ada, tahap
  "Pembayaran" selesai.

---

## 4. Data & Dokumen yang Dibutuhkan dari Leasing

### 4.1 Per pengajuan (perlu disampaikan leasing ke dealer)
- **PO leasing**: nomor PO, tanggal PO, nama leasing, **nominal DP**.
- **Hasil survey**: status setujui/tolak + alasan (untuk catatan notes).
- **Bukti pembayaran pelunasan**: nominal, tanggal bayar, metode, no. referensi transfer.

### 4.2 Data customer yang umum diminta leasing (untuk survey & kredit analysis)
> Belum diinput ke sistem — dicatat manual/di luar aplikasi saat mode `admin`.

- KTP pemohon (+ pasangan jika ada), KK.
- Bukti penghasilan (slip gaji / surat keterangan usaha / rekening koran 3–6 bulan).
- NPWP, bukti tempat tinggal (tagihan listrik/PDAM/sewa), dokumen agunan (jika ada).
- Data unit yang dipesan (dari SPK: model, varian warna, harga, DP, tenor — tenor &
  angsuran dicatat di `notes` bila perlu).

### 4.3 Struktur data di sistem
- **`credit_applications`** — 1 baris per order: `application_no` (KRT…), data PO
  (`leasing_name`, `po_number`, `po_date`, `dp_amount`), `stage`, `status`, 6 timestamp
  tahapan, `notes`.
- **`credit_payments`** — baris per pembayaran: `type` (`dp`|`pelunasan`),
  `payer_type` (`customer`|`leasing`), `amount`, `paid_at`, `method`, `reference`,
  `recorded_by`. Unique `(credit_application_id, type)` → maksimal 1 DP + 1 pelunasan.

---

## 5. Subdomain Leasing (Persiapan)

Konfigurasi disiapkan di **`config/leasing.php`**:

| Key | Env | Default | Keterangan |
|-----|-----|---------|------------|
| `subdomain` | `LEASING_SUBDOMAIN` | `leasing` | Acuan DNS/vhost (mis. `leasing.jomotocenter.com`) bila portal leasing diaktifkan. |
| `access_mode` | `LEASING_ACCESS_MODE` | `admin` | `admin` = seluruh proses dikelola admin dealer (mode aktif saat ini). `portal` = leasing punya login sendiri. |
| `portal.enabled` | `LEASING_PORTAL_ENABLED` | `false` | Stub portal — belum diaktifkan. |
| `portal.path` | `LEASING_PORTAL_PATH` | `leasing-portal` | Path portal bila diaktifkan. |

**Status keputusan:** apakah leasing mendapat **login sendiri** masih
**belum diputuskan** (lihat catatan tugas). Karena itu sistem saat ini berjalan
penuh dengan mode **`admin`**: seluruh tahapan (SPK, survey, PO, DP, pelunasan,
pengambilan) dicatat oleh admin dealer melalui Filament. Stub `portal` disiapkan
agar keputusan nanti tidak memerlukan perubahan skema.

---

## 6. Referensi Implementasi

| Komponen | Lokasi |
|----------|--------|
| Service (logika bisnis) | `app/Services/CreditService.php` |
| Exception bisnis | `app/Exceptions/CreditException.php` |
| Model | `app/Models/CreditApplication.php`, `app/Models/CreditPayment.php` |
| Migration | `database/migrations/2026_11_13_000001_create_credit_tables.php` |
| Admin resource | `app/Filament/Resources/CreditApplicationResource.php` (+ Pages) |
| Checkout (opsi kredit) | `app/Http/Controllers/Buyer/CheckoutController.php` + view `buyer/checkout/payment.blade.php` |
| Halaman buyer | `app/Http/Controllers/Buyer/OrderController.php` + view `buyer/orders/show.blade.php` |
| Konfigurasi | `config/leasing.php` |
| Tests | `tests/Feature/CreditPurchaseTest.php` (10 test) |

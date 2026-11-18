# SPESIFIKASI TEKNIS & SPRINT PLAN REVISI JOMOTOCENTER
**Dokumen Referensi:** `docs/SPEC_REVISI_20261118.md`  
**Proyek:** JOMOTOCENTER (`pak-joni-ecommerce`)  
**Tanggal:** 18 November 2026  
**Penanggung Jawab:** @Kira (Project Manager & Workspace Admin — Jalur 1)  
**Metodologi:** Superpowers 5 Jalur, Ponytail (Anti-Bloat & Deep Audit), Addy Osmani Clean Architecture  

---

## 1. RINGKASAN EKSEKUTIF & LATAR BELAKANG
Berdasarkan arahan Mas TIAN dan orkestrasi @ROBOBUBI, sprint ini fokus pada perbaikan fungsionalitas operasional dealer pada sistem e-commerce JOMOTOCENTER, mencakup manajemen stok admin, tata letak filter katalog kendaraan/sukucadang, standardisasi dokumen legal (Faktur Penjualan Sepeda Motor & SPK), pemisahan faktur customer secara granular, penanganan motor indent, serta otomasi rilis produksi ke server Emerald (`alurelab`).

---

## 2. ANALISIS PERMASALAHAN SISTEM (ROOT CAUSE ANALYSIS)

### 2.1 Action "Kelola Stok" di Admin (Motor, Sparepart, ATV)
- **Kondisi Saat Ini:** Pada `ItemResource.php`, action `manage_stock` hanya me-render blade modal `filament.modals.item-stock-management` dengan `modalSubmitAction(false)`. Terdapat teks instruksi statis *"Edit item ini, lalu ubah nilai Stok pada masing-masing varian warna"*. Admin tidak bisa mengubah stok langsung dari tabel.
- **Dampak:** Admin harus bolak-balik membuka menu Edit Item lengkap yang lambat dan membebani alur kerja harian.
- **Solusi:** Ubah action `manage_stock` menjadi interactive form modal berbasis Filament Action dengan schema dinamis yang memuat seluruh varian warna (`ItemColor`), input kuantitas stok baru/penyesuaian, input catatan mutasi, dan memicu `StockService::recordMutation()` secara atomik.

### 2.2 Filter Index Katalog (Motor, Sparepart, ATV)
- **Kondisi Saat Ini:** Filter bawaan Filament tersimpan di dalam popover (dropdown tersembunyi di samping search input).
- **Dampak:** Admin tidak dapat melihat parameter filter aktif secara sekilas dan membutuhkan klik berulang.
- **Solusi:** Terapkan `filtersLayout(Tables\Enums\FiltersLayout::AboveContent)` pada `ItemResource::table()` sehingga form filter tampil permanen di atas tabel index.

### 2.3 Tabel Menu Sparepart
- **Kondisi Saat Ini:** Kolom index pada menu Sparepart belum menampilkan relasi motor yang kompatibel, dan filter di atas index belum memfasilitasi pencarian berdasarkan Motor Kompatibel secara optimal.
- **Solusi:** Tambahkan kolom `compatibleMotors.name` dengan format badge terorganisir (`limitList`, `expandableLimitedList`) khusus saat context category type adalah `sparepart`. Tempatkan filter Kategori dan Motor Kompatibel di section atas index.

### 2.4 Alur Order, Faktur Kendaraan, dan SPK
- **Kondisi Saat Ini:** 
  1. Tombol `Cetak Faktur Sukucadang` muncul tanpa memvalidasi apakah order berisi sparepart atau kendaraan, menyebabkan checkout motor menampilkan faktur sparepart.
  2. Tombol `Cetak Invoice` generic lama masih tampil membingungkan customer.
  3. Form SPK (Surat Pesanan Kendaraan) belum terintegrasi di Filament Admin `OrderResource`.
  4. Belum ada gerbang verifikasi admin (`is_admin_verified`). Customer bisa mencetak dokumen sebelum admin mengisi nomor rangka dan nomor mesin fisik.
  5. Format nomor faktur motor belum mengikuti standar dealer (`{seq:03d}/FPSM-T/{year}`, contoh: `002/FPSM-T/2026`).
- **Solusi:** 
  1. Implementasikan gerbang verifikasi admin (`is_admin_verified` & `admin_verified_at`).
  2. Susun form Section Faktur Motor dan Form Section SPK lengkap dengan checklist dokumen persyaratan di `OrderResource`.
  3. Perbaiki logika view `resources/views/buyer/orders/show.blade.php` agar hanya menampilkan faktur motor jika order memiliki unit kendaraan, faktur sukucadang jika memiliki part, dan keduanya jika gabungan (faktur terpisah).
  4. Nonaktifkan / kunci tombol cetak bagi customer sampai admin selesai melakukan verifikasi.

### 2.5 Pembelian Motor Stok Habis (Indent)
- **Kondisi Saat Ini:** Checkout indent sudah didukung pada backend (`is_indent`, `dp_amount` 50%), namun antarmuka storefront perlu memastikan tombol aksi pada motor berstok 0 tetap dapat diklik dengan label "Pesan Indent", menyertakan syarat & ketentuan DP 50%, serta menampilkan rincian pelunasan yang transparan pada order detail.

---

## 3. SPESIFIKASI SKEMA DATABASE (MIGRATIONS)

Migration baru: `database/migrations/2026_11_18_000001_add_verification_and_spk_enhancements_to_orders_table.php`

```php
Schema::table('orders', function (Blueprint $table) {
    // Gerbang Verifikasi Admin
    if (!Schema::hasColumn('orders', 'is_admin_verified')) {
        $table->boolean('is_admin_verified')->default(false)->after('status')->index();
    }
    if (!Schema::hasColumn('orders', 'admin_verified_at')) {
        $table->timestamp('admin_verified_at')->nullable()->after('is_admin_verified');
    }
    if (!Schema::hasColumn('orders', 'admin_verified_by')) {
        $table->foreignId('admin_verified_by')->nullable()->constrained('users')->nullOnDelete()->after('admin_verified_at');
    }

    // Penyempurnaan Field SPK & Dokumen (jika belum ada)
    if (!Schema::hasColumn('orders', 'spk_order_date')) {
        $table->date('spk_order_date')->nullable()->after('spk_date');
    }
});
```

### Logika Penomoran Faktur Motor (`Order::generateMotorInvoiceNumber()`):
- **Format:** `{seq:03d}/FPSM-T/{year}`
- **Contoh:** `001/FPSM-T/2026`, `002/FPSM-T/2026`
- **Algoritma:**
  ```php
  public static function generateMotorInvoiceNumber(): string
  {
      $year = now()->format('Y');
      $pattern = "%/FPSM-T/{$year}";
      
      $lastInvoice = self::where('invoice_no', 'like', $pattern)
          ->orderBy('invoice_no', 'desc')
          ->value('invoice_no');
      
      $seq = 1;
      if ($lastInvoice && preg_match('/^(\d+)\/FPSM-T\/' . $year . '$/', $lastInvoice, $m)) {
          $seq = (int) $m[1] + 1;
      }
      
      return sprintf('%03d/FPSM-T/%s', $seq, $year);
  }
  ```

---

## 4. DETIL SPESIFIKASI ARSITEKTUR & LOGIKA SISTEM

### 4.1 Model Relationships & Helper Methods (`app/Models/Order.php` & `OrderItem.php`)
Tambahkan method klasifikasi item pada `OrderItem`:
- `isVehicle(): bool`: Mengembalikan `true` jika `itemable_type === ItemColor::class` dan kategori item bertipe Motor atau ATV.
- `isPart(): bool`: Mengembalikan `true` jika `itemable_type === PartVariant::class` atau `part_id !== null` atau kategori bertipe Sparepart.

Tambahkan method koleksi pada `Order`:
- `vehicleItems(): Collection`: Filter `$this->items->filter->isVehicle()`.
- `partItems(): Collection`: Filter `$this->items->filter->isPart()`.
- `hasVehicle(): bool`: `$this->vehicleItems()->isNotEmpty()`.
- `hasParts(): bool`: `$this->partItems()->isNotEmpty()`.
- `isDocumentsVerified(): bool`: `(bool) $this->is_admin_verified`.

### 4.2 Filament Admin - Interactive Modal "Kelola Stok" (`ItemResource.php`)
Action `manage_stock` diubah dari view statis menjadi modal form interaktif:
1. Form modal memuat `Repeater` atau sekumpulan `TextInput` dinamis untuk tiap `ItemColor`.
2. Input:
   - Nama Varian (Disabled)
   - Stok Saat Ini (Disabled)
   - Stok Baru / Jumlah Penyesuaian (Numeric, Required, min: 0)
   - Catatan Mutasi (TextInput, optional, default: "Penyesuaian stok langsung oleh admin")
3. Eksekusi `action`:
   - Loop varian warna, hitung selisih mutasi.
   - Panggil `StockService::recordMutation($itemColor, $newStock, $notes, auth()->id())`.
   - Update `$itemColor->update(['stock' => $newStock, 'stock_updated_at' => now()])`.
   - Update status stok item: jika total stok > 0 set `ready`, jika 0 set `indent`.
   - Tampilkan Filament Notification sukses.

### 4.3 Filament Admin - Filter AboveContent & Kolom Sparepart
Pada `ItemResource::table()`:
1. Tambahkan `->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)`.
2. Kolom "Motor Kompatibel":
   ```php
   Tables\Columns\TextColumn::make('compatibleMotors.name')
       ->label('Motor Kompatibel')
       ->badge()
       ->separator(', ')
       ->limitList(3)
       ->expandableLimitedList()
       ->visible(fn ($livewire) => $livewire instanceof Pages\ListItemsByType 
           && $livewire->getCategoryType()?->slug === 'sparepart')
       ->searchable(),
   ```
3. Filter section di atas index untuk Sparepart:
   - Filter Kategori (`category_id`)
   - Filter Motor Kompatibel (`compatibleMotors`)

### 4.4 Filament Admin - Form Faktur & SPK (`OrderResource.php`)
Di dalam `OrderResource::form()`:
1. **Section "Faktur Pembelian Sepeda Motor":**
   - `invoice_no`: TextInput, default auto `{seq:03d}/FPSM-T/{year}`, unique.
   - `invoice_date`: DatePicker, default `today`.
   - `buyer_name`: TextInput, auto-fill nama pemesan / STNK.
   - `buyer_nik`: TextInput, max 32.
   - `buyer_address`: Textarea, auto-fill alamat pemesan.
   - `chassis_no`: TextInput, **Wajib diisi Admin** (Nomor Rangka).
   - `engine_no`: TextInput, **Wajib diisi Admin** (Nomor Mesin).
   - `notes`: Textarea, default catatan OTR + PPN.
   - `signatory_name`: TextInput, default "JONI".
2. **Section "Surat Pesanan Kendaraan (SPK)":**
   - `spk_no`: TextInput, default `{order_no}`.
   - `spk_date`: DatePicker, default `today`.
   - **Data Pemesan & STNK:**
     * `stnk_name`: TextInput (Nama di STNK/BPKB, auto-fill nama buyer, editable).
     * `stnk_address`: Textarea (Alamat di STNK/BPKB, auto-fill alamat buyer, editable).
     * `buyer_nik`: TextInput (No. KTP pemesan).
   - **Data Spesifikasi Unit:**
     * Model & Merk (Display info dari `vehicleItems()`).
     * `motor_color`: TextInput (Warna kendaraan).
     * `manufacturing_year`: TextInput (Tahun rakitan, default tahun saat ini).
   - **Skema Pembayaran:**
     * `spk_payment_type`: Select (`cash` / `credit`).
     * `leasing_provider`: TextInput (Pegadaian, Adira, dsb. Tampil jika kredit).
     * `spk_dp_amount`: TextInput / numeric (Nominal DP).
     * `credit_tenor`: TextInput (Contoh: 12 Bulan, 24 Bulan).
     * `monthly_installment`: TextInput / numeric (Angsuran per bulan).
     * `booking_fee`: TextInput / numeric (Tanda jadi).
   - **Checklist Berkas Persyaratan (CheckboxList):**
     * Options:
       - `ktp_suami`: KTP Suami
       - `ktp_istri`: KTP Istri
       - `kartu_keluarga`: Kartu Keluarga
       - `slip_gaji`: Slip Gaji
       - `rekening_listrik`: Rekening Listrik
       - `bukti_pbb`: Bukti PBB
       - `rekening_tabungan_3_bulan`: Rekening Tabungan 3 Bulan
   - `spk_notes`: Textarea.
3. **Status Verifikasi Admin:**
   - Toggle / Checkbox `is_admin_verified`: "Verifikasi Data Faktur & SPK (Rilis Cetak ke Customer)".
   - Saat toggle bernilai true dan form disimpan, isi `admin_verified_at = now()` dan `admin_verified_by = auth()->id()`.

### 4.5 Customer Order Detail (`resources/views/buyer/orders/show.blade.php`)
1. **Hapus Tombol:** Hilangkan tombol `Cetak Invoice` (`route('buyer.orders.invoice')`).
2. **Kondisi Tampil Tombol:**
   - **Jika Order Berisi Kendaraan (`$order->hasVehicle()`):**
     * Tampilkan tombol `Cetak Faktur Motor` dan `Cetak SPK`.
   - **Jika Order Berisi Sparepart (`$order->hasParts()`):**
     * Tampilkan tombol `Cetak Faktur Sukucadang`.
   - **Jika Order Berisi Keduanya (`$order->hasVehicle() && $order->hasParts()`):**
     * Tampilkan tombol `Cetak Faktur Motor`, `Cetak Faktur Sukucadang`, dan `Cetak SPK` (2 faktur terpisah + SPK).
3. **State Verifikasi Admin:**
   - Jika `$order->is_admin_verified === false`:
     * Tombol cetak di-disable dengan visual disabled / tooltip.
     * Tampilkan banner informasi:
       > ℹ️ **Dokumen Faktur & SPK sedang dalam proses verifikasi dan pengisian data nomor rangka/mesin oleh Admin Dealer.** Tombol cetak akan aktif otomatis setelah verifikasi selesai.
   - Jika `$order->is_admin_verified === true`:
     * Tombol cetak berstatus aktif (clickable).
4. **Proteksi Controller (Backend Guard):**
   - Di `MotorInvoiceController`, `PartsInvoiceController`, dan `SpkController`:
     * Jika request berasal dari buyer (bukan admin) dan `$order->is_admin_verified == false`, hentikan eksekusi dengan `abort(403, 'Dokumen belum diverifikasi oleh admin dealer.')`.

### 4.6 Pemisahan Item di Template PDF Faktur
- Di `resources/views/pdf/parts-invoice.blade.php`:
  * Ubah `$items = $order->items;` menjadi `$items = $order->partItems();`.
  * Hitung subtotal dan PPN hanya berdasarkan part items.
- Di `resources/views/pdf/motor-invoice.blade.php`:
  * Pastikan hanya mengambil `$order->vehicleItems()`.

---

## 5. RENCANA SPRINT 5 JALUR (SUPERPOWERS & PONYTAIL)

### Jalur 1: Spec & Project Governance (@Kira)
- [x] Analisis menyeluruh codebase Laravel 11, Filament v4, dan struktur database.
- [x] Pemetaan 100% kebutuhan revisi Mas TIAN ke arsitektur teknis.
- [x] Penyusunan dokumen `docs/SPEC_REVISI_20261118.md`.
- [x] Handoff resmi ke Jalur 2 (@Akane) dan Jalur 3 (@Kenji).

### Jalur 2: UI/UX & Antarmuka (@Akane)
- [ ] **Task 2.1:** Rancang modal interactive kelola stok varian di Filament Admin.
- [ ] **Task 2.2:** Konfigurasi tata letak filter `AboveContent` di tabel index Motor, Sparepart, ATV.
- [ ] **Task 2.3:** Integrasikan kolom badge `Motor Kompatibel` di index Sparepart.
- [ ] **Task 2.4:** Rancang layout Form Section Faktur & SPK di `OrderResource` (termasuk CheckboxList berkas).
- [ ] **Task 2.5:** Redesign header action pada `buyer/orders/show.blade.php` (hapus cetak invoice, pisahkan faktur, tambahkan state menunggu verifikasi admin).
- [ ] **Task 2.6:** Pastikan UI tombol "Pesan Indent" pada PDP Motor berstok 0 terlihat konsisten dan komunikatif.

### Jalur 3: Lean TDD Code Implementation (@Kenji)
- [ ] **Task 3.1:** Buat migration `2026_11_18_000001_add_verification_and_spk_enhancements_to_orders_table.php` dan jalankan migrasi lokal.
- [ ] **Task 3.2:** Implementasikan generator nomor faktur otomatis `{seq:03d}/FPSM-T/{year}` di model `Order`.
- [ ] **Task 3.3:** Buat helper method pembeda kendaraan vs sparepart di `OrderItem` dan `Order` (`isVehicle()`, `isPart()`, `vehicleItems()`, `partItems()`, `hasVehicle()`, `hasParts()`).
- [ ] **Task 3.4:** Implementasikan interactive action `manage_stock` di `ItemResource` dengan sinkronisasi `StockService`.
- [ ] **Task 3.5:** Update `OrderResource` Form & Infolist dengan Section Faktur, Section SPK, dan toggle verifikasi admin.
- [ ] **Task 3.6:** Update `MotorInvoiceController`, `PartsInvoiceController`, dan `SpkController` dengan proteksi verifikasi dan pemisahan item spesifik.
- [ ] **Task 3.7:** Update filter dan query pada `ListItemsByType`.
- [ ] **Task 3.8:** Tulis feature unit test (TDD) untuk memvalidasi seluruh alur dokumen dan stok.

### Jalur 4: Quality Assurance & Audit (@Haru & @Yuki)
- [ ] **Task 4.1:** Verifikasi modal kelola stok: perubahan stok langsung tersimpan, mutasi tercatat, total stok dan badge status terupdate.
- [ ] **Task 4.2:** Verifikasi filter `AboveContent` pada menu Motor, Sparepart, dan ATV berfungsi presisi.
- [ ] **Task 4.3:** Verifikasi kolom Motor Kompatibel tampil hanya pada menu Sparepart dan filter motor berjalan benar.
- [ ] **Task 4.4:** Verifikasi alur order baru: order baru tidak bisa dicetak buyer sebelum admin verifikasi (HTTP 403 / UI disabled).
- [ ] **Task 4.5:** Verifikasi admin edit order: nomor rangka, nomor mesin, checklist berkas tersimpan rapi; setelah diverifikasi, buyer bisa mengunduh PDF.
- [ ] **Task 4.6:** Verifikasi pemisahan faktur: order motor menghasilkan Faktur Sepeda Motor & SPK; order sparepart menghasilkan Faktur Sukucadang; order campuran menghasilkan 2 faktur terpisah.
- [ ] **Task 4.7:** Verifikasi checkout indent: motor stok 0 bisa di-checkout dengan DP 50% dan status tertera jelas di order detail.
- [ ] **Task 4.8:** Deep audit performa, Web Vitals, dan kepatuhan standar Ponytail (zero code bloat).

### Jalur 5: DevOps & Deployment (@Yuuji & @Kira)
- [ ] **Task 5.1:** Menjalankan linting dan seluruh test suite PHPUnit (`php artisan test`).
- [ ] **Task 5.2:** Menyiapkan commit git terstruktur dan sinkronisasi ke branch `main`.
- [ ] **Task 5.3:** Menjalankan script deployment `./deploy.sh` ke server Emerald (`alurelab@emerald.hidden-server.net:31988`).
- [ ] **Task 5.4:** Verifikasi pasca-deploy di live environment: migration status, view cache, asset bundle, dan uji cetak dokumen live.

---

## 6. PROTOKOL ACCEPTANCE CRITERIA
1. **Kelola Stok Modal:** Mengklik action "Kelola Stok" pada baris motor/part langsung memunculkan modal input stok varian, dan saat disimpan, stok varian + status produk terupdate tanpa perlu masuk form Edit.
2. **Filter Index:** Filter kategori, brand, dan kompatibilitas motor tampil di section atas tabel tanpa perlu membuka ikon popover.
3. **Kolom Sparepart:** Tabel sparepart memuat kolom Motor Kompatibel yang informatif.
4. **Proteksi & Pemisahan Faktur:**
   - Checkout hanya motor: Tersedia Faktur Sepeda Motor & SPK (setelah admin isi no. rangka & mesin + klik verifikasi). Tidak ada Faktur Sukucadang.
   - Checkout hanya sparepart: Tersedia Faktur Sukucadang (setelah verifikasi). Tidak ada Faktur Motor & SPK.
   - Checkout kombinasi: Tersedia kedua faktur secara terpisah dengan perhitungan matematis terisolasi.
   - Tombol "Cetak Invoice" lama 100% hilang.
5. **Format Faktur Motor:** Nomor faktur ter-generate otomatis mengikuti format `{seq:03d}/FPSM-T/{year}` (misal: `002/FPSM-T/2026`).
6. **Form SPK Admin:** Tersedia field lengkap pemesan, STNK, skema finansial (tunai/kredit), dan 7 checklist berkas fisik.
7. **Indent Motor:** Motor stok 0 dapat di-checkout dengan mekanisme DP 50% tanpa terblokir sistem stok.
8. **Deployment:** Perubahan ter-deploy sempurna ke server Emerald tanpa downtime dan lolos seluruh test feature.

---

## 7. HANDOFF PROTOCOL
Format penyerahan tugas resmi Jalur 1:
`[SPEC_READY] Proyek: JOMOTOCENTER (pak-joni-ecommerce) | Spec: docs/SPEC_REVISI_20261118.md | Task List: 1. Modal interaktif Kelola Stok, 2. Filter AboveContent & Motor Kompatibel, 3. Form Faktur FPSM-T & SPK lengkap, 4. Gerbang verifikasi admin & pemisahan faktur, 5. Deploy Emerald | Next: @Akane (UI) / @Kenji (Dev)`

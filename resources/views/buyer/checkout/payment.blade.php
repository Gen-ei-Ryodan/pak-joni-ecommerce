@extends('layouts.buyer')

@section('title', 'Checkout - Payment')

@section('content')
    <section class="section">
        <div class="container">
            @if (session('status'))
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(255,77,77,0.35);background:rgba(255,77,77,0.08);">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Checkout — Payment</div>
                <a class="btn" href="{{ route('buyer.checkout.shipping') }}">Back</a>
            </div>

            <div style="height:14px;"></div>

            <div style="display:grid;grid-template-columns:1fr 420px;gap:16px;">
                {{-- LEFT COLUMN: FORM & DETAILS --}}
                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Items</div>
                    <div style="height:10px;"></div>

                    <div style="display:grid;gap:10px;margin-bottom:16px;">
                        @foreach ($cart->items as $it)
                            <div class="panel" style="padding:12px;border-radius:12px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                                <div>
                                    @if ($it->itemable_type === 'App\Models\PartVariant')
                                        <div style="font-weight:600;">{{ $it->itemable->part->name ?? 'N/A' }}</div>
                                        <div class="muted" style="margin-top:6px;">{{ $it->variant_name }} — {{ $it->itemable->sku ?? 'N/A' }}</div>
                                    @elseif ($it->itemable_type === 'App\Models\ItemColor')
                                        <div style="font-weight:600;">{{ $it->itemable->item->name ?? 'N/A' }}</div>
                                        <div class="muted" style="margin-top:6px;">{{ $it->variant_name }}</div>
                                    @endif
                                </div>
                                <div style="font-family:var(--mono);">
                                    {{ $it->quantity }} x {{ number_format((float) $it->price_snapshot, 2, '.', ',') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form method="post" action="{{ route('buyer.checkout.place') }}" enctype="multipart/form-data" id="checkout-form">
                        @csrf

                        {{-- POINT 3: PILIHAN FAKTUR PAJAK --}}
                        <div style="margin-bottom:16px;padding:12px;border-radius:10px;border:1px solid var(--line);background:rgba(255,255,255,0.03);">
                            <div style="font-weight:600;margin-bottom:8px;font-size:14px;">Pilihan Faktur Pajak</div>
                            <div style="display:grid;gap:8px;">
                                <label class="panel" style="padding:8px 12px;display:flex;gap:10px;align-items:center;cursor:pointer;margin:0;">
                                    <input type="radio" name="needs_tax_invoice" value="0" {{ old('needs_tax_invoice', '0') == '0' ? 'checked' : '' }}>
                                    <span style="font-size:13px;">Tidak membutuhkan faktur pajak</span>
                                </label>
                                <label class="panel" style="padding:8px 12px;display:flex;gap:10px;align-items:center;cursor:pointer;margin:0;">
                                    <input type="radio" name="needs_tax_invoice" value="1" {{ old('needs_tax_invoice') == '1' ? 'checked' : '' }}>
                                    <span style="font-size:13px;font-weight:500;">Membutuhkan faktur pajak</span>
                                </label>
                            </div>
                        </div>

                        {{-- POINT 5: FORM SPK & UPLOAD KTP JIKA PESANAN BERISI KENDARAAN (MOTOR / ATV) --}}
                        @if(!empty($hasVehicle))
                            <div class="panel" style="padding:16px;margin-bottom:16px;border-radius:12px;border:1px solid #93c5fd;background:rgba(239,246,255,0.7);">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:8px;">
                                    <div style="font-weight:700;font-size:15px;color:#1e3a8a;">Data Surat Pemesanan Kendaraan (SPK)</div>
                                    <span class="badge bg-blue" style="font-size:11px;padding:4px 8px;">Wajib Motor & ATV</span>
                                </div>
                                <p class="muted" style="font-size:12.5px;margin-bottom:14px;line-height:1.4;">
                                    Data di bawah ini wajib dilengkapi untuk penerbitan SPK, Kwitansi DP, Faktur Pembelian, dan administrasi STNK/BPKB.
                                </p>

                                <div style="display:grid;gap:12px;">
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                        <div class="field">
                                            <label style="display:block;color:var(--muted);font-size:11.5px;margin-bottom:4px;font-weight:600;">
                                                Nama Pemesan (Otomatis dari Profil)
                                            </label>
                                            <input class="input" style="width:100%;min-width:0;padding:8px 10px;background:rgba(0,0,0,0.03);border:1px solid var(--line);border-radius:6px;" 
                                                   value="{{ auth()->user()->name }}" readonly>
                                        </div>
                                        <div class="field">
                                            <label style="display:block;color:var(--muted);font-size:11.5px;margin-bottom:4px;font-weight:600;">
                                                Nomor Telepon / WA <span style="color:#ef4444;">*</span>
                                            </label>
                                            <input class="input" style="width:100%;min-width:0;padding:8px 10px;border:1px solid var(--line);border-radius:6px;" 
                                                   name="phone" value="{{ old('phone', auth()->user()->phone) }}" required placeholder="Contoh: 08123456789">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label style="display:block;color:var(--muted);font-size:11.5px;margin-bottom:4px;font-weight:600;">
                                            Nomor KTP (NIK) <span style="color:#ef4444;">*</span>
                                        </label>
                                        <input class="input" style="width:100%;min-width:0;padding:8px 10px;border:1px solid var(--line);border-radius:6px;" 
                                               name="ktp_no" value="{{ old('ktp_no', auth()->user()->ktp_no) }}" required placeholder="16 digit NIK sesuai KTP">
                                    </div>

                                    <div class="field">
                                        <label style="display:block;color:var(--muted);font-size:11.5px;margin-bottom:4px;font-weight:600;">
                                            Nama Lengkap di STNK & BPKB <span style="color:#ef4444;">*</span>
                                        </label>
                                        <input class="input" style="width:100%;min-width:0;padding:8px 10px;border:1px solid var(--line);border-radius:6px;" 
                                               name="stnk_name" value="{{ old('stnk_name', auth()->user()->name) }}" required placeholder="Nama pemilik kendaraan pada STNK & BPKB">
                                    </div>

                                    <div class="field">
                                        <label style="display:block;color:var(--muted);font-size:11.5px;margin-bottom:4px;font-weight:600;">
                                            Alamat Lengkap di STNK & BPKB <span style="color:#ef4444;">*</span>
                                        </label>
                                        <textarea class="input" style="width:100%;min-width:0;padding:8px 10px;border:1px solid var(--line);border-radius:6px;resize:vertical;" 
                                                  name="stnk_address" rows="2" required placeholder="Alamat domisili sesuai KTP untuk dokumen STNK/BPKB">{{ old('stnk_address', !empty($address) ? $address->address_line1 . ($address->address_line2 ? ', ' . $address->address_line2 : '') . ', ' . $address->city . ', ' . $address->province : '') }}</textarea>
                                    </div>

                                    <div class="field">
                                        <label style="display:block;color:var(--muted);font-size:11.5px;margin-bottom:4px;font-weight:600;">
                                            Upload Foto KTP Asli <span style="color:#ef4444;">*</span>
                                        </label>
                                        <input type="file" name="ktp_photo" accept="image/*" class="input" style="width:100%;min-width:0;padding:8px 10px;border:1px solid var(--line);border-radius:6px;" required>
                                        <div class="muted" style="font-size:11px;margin-top:4px;">Format file: JPG, PNG, atau JPEG (Maks. 5MB). Foto KTP digunakan untuk validasi data SPK oleh Admin Dealer.</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- METODE PEMBAYARAN --}}
                        <div style="font-weight:600;margin-bottom:8px;">Metode Pembayaran</div>
                        <div style="display:grid;gap:8px;margin-bottom:14px;">
                            <label class="panel" style="padding:10px 12px;display:flex;gap:10px;align-items:flex-start;cursor:pointer;">
                                <input type="radio" name="payment_method" id="pm-cash" value="qris" checked style="margin-top:3px;">
                                <div>
                                    <div style="font-weight:600;">Tunai / QRIS</div>
                                    @if(!empty($hasVehicle))
                                        <div style="font-size:12px;margin-top:2px;color:#1e40af;font-weight:500;">
                                            Skema Cash Motor & ATV: Pembayaran 2 tahap (DP Rp 500.000 sekarang, pelunasan sisa saat unit siap).
                                        </div>
                                    @else
                                        <div class="muted" style="font-size:12px;margin-top:2px;">Bayar penuh sekarang melalui QRIS.</div>
                                    @endif
                                </div>
                            </label>

                            @if(!empty($canCredit))
                                <label class="panel" style="padding:10px 12px;display:flex;gap:10px;align-items:flex-start;cursor:pointer;">
                                    <input type="radio" name="payment_method" id="pm-credit" value="credit" style="margin-top:3px;">
                                    <div>
                                        <div style="font-weight:600;">Kredit (Leasing)</div>
                                        <div class="muted" style="font-size:12px;margin-top:2px;">
                                            Ajukan pembelian kredit. DP dibayarkan ke dealer setelah nominal DP ditentukan
                                            leasing (PO), pelunasan dibayarkan oleh leasing ke dealer.
                                        </div>
                                    </div>
                                </label>
                                <div id="credit-note" style="display:none;margin-top:2px;padding:8px 10px;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.3);border-radius:8px;font-size:12px;color:#1d4ed8;">
                                    Tahapan kredit: SPK → Survey → Persetujuan Survey → Persiapan Unit → Pembayaran (DP + Pelunasan) → Pengambilan Unit.
                                    Nominal DP mengikuti keputusan leasing.
                                </div>
                                <script>
                                    (function () {
                                        var creditNote = document.getElementById('credit-note');
                                        var cashRadio = document.getElementById('pm-cash');
                                        var creditRadio = document.getElementById('pm-credit');
                                        if (!creditNote || !cashRadio || !creditRadio) return;
                                        function sync() { creditNote.style.display = creditRadio.checked ? 'block' : 'none'; }
                                        cashRadio.addEventListener('change', sync);
                                        creditRadio.addEventListener('change', sync);
                                        sync();
                                    })();
                                </script>
                            @endif
                        </div>

                        <button class="btn btn-primary" id="btn-place-order" type="submit" style="width:100%;padding:12px;font-size:15px;font-weight:600;">Place Order</button>
                    </form>

                    <script>
                        (function() {
                            var form = document.getElementById('checkout-form');
                            var btn = document.getElementById('btn-place-order');
                            if (!form || !btn) return;
                            form.addEventListener('submit', function() {
                                btn.disabled = true;
                                btn.innerText = 'Memproses Pesanan...';
                                btn.style.opacity = '0.7';
                                btn.style.cursor = 'not-allowed';
                            });
                        })();
                    </script>
                </div>

                {{-- RIGHT COLUMN: SUMMARY --}}
                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Summary</div>
                    <div style="height:10px;"></div>

                    <div class="muted" style="display:grid;gap:8px;">
                        <div>Subtotal: <span style="font-family:var(--mono);">{{ number_format((float) $subtotal, 2, '.', ',') }}</span></div>
                        <div>Ongkos Kirim: <span style="font-family:var(--mono);">@if($isDealerPickup)<span style="color:#4ade80;">Gratis</span>@else{{ number_format((float) $shippingCost, 2, '.', ',') }}@endif</span></div>

                        {{-- CASH MOTOR DP & REMAINING SCHEME --}}
                        @if(!empty($isCashMotor))
                            <div style="margin-top:6px;padding:10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:12px;color:#1e40af;">
                                <div style="font-weight:700;margin-bottom:6px;font-size:13px;">Skema Pembayaran Cash Sepeda Motor & ATV:</div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                    <span>Total Harga Unit:</span>
                                    <span style="font-family:var(--mono);font-weight:600;">Rp {{ number_format((float) $fullTotal, 0, ',', '.') }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:4px;color:#059669;font-weight:600;">
                                    <span>Tahap 1 (DP dibayar sekarang):</span>
                                    <span style="font-family:var(--mono);">Rp 500.000</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;color:#d97706;font-weight:600;">
                                    <span>Tahap 2 (Pelunasan saat unit siap):</span>
                                    <span style="font-family:var(--mono);">Rp {{ number_format((float) $remainingAmount, 0, ',', '.') }}</span>
                                </div>
                                <div style="margin-top:6px;font-size:11px;color:#475569;border-top:1px dashed #cbd5e1;padding-top:6px;">
                                    * Setelah pembayaran DP Rp500.000 sukses, sistem otomatis menerbitkan SPK & Kwitansi DP. Faktur Pembelian dan Kwitansi Pelunasan diterbitkan setelah pelunasan berhasil.
                                </div>
                            </div>
                        @elseif($hasIndent)
                            <div style="margin-top:6px;padding:8px;background:#fff3cd;border-radius:8px;font-size:12px;color:#856404;">
                                <div style="font-weight:600;margin-bottom:4px;">Indent Order - DP 50%</div>
                                <div>DP (dibayar sekarang): <span style="font-family:var(--mono);">{{ number_format($dpAmount, 2, '.', ',') }}</span></div>
                                <div>Sisa (saat barang ready): <span style="font-family:var(--mono);">{{ number_format($remainingAmount, 2, '.', ',') }}</span></div>
                            </div>
                        @endif

                        @if(isset($itemDiscount) && $itemDiscount > 0)
                            <div style="margin-top:6px;padding:8px;background:#e0f7fa;border-radius:8px;font-size:12px;color:#01579b;">
                                <div style="font-weight:600;margin-bottom:4px;">Diskon Item</div>
                                <div>Diskon: <span style="color:#0277bd;">Rp {{ number_format($itemDiscount, 0, ',', '.') }}</span></div>
                                <div>Harga Akhir: <span style="color:#0277bd;font-weight:500;">Rp {{ number_format($finalPriceAfterItemDiscount, 2, '.', ',') }}</span></div>
                            </div>
                        @endif

                        @if(isset($voucherDiscount) && $voucherDiscount > 0)
                            <div style="margin-top:6px;padding:8px;background:#f3e5f5;border-radius:8px;font-size:12px;color:#4a148c;">
                                <div style="font-weight:600;margin-bottom:4px;">Voucher</div>
                                <div>Voucher: <span style="color:#4a148c;">{{ $voucherCode }}</span></div>
                                <div>Diskon Voucher: <span style="color:#4a148c;">Rp {{ number_format($voucherDiscount, 2, '.', ',') }}</span></div>
                                <div>Total: <span style="color:#4a148c;font-weight:500;">Rp {{ number_format($finalPriceAfterVoucher, 2, '.', ',') }}</span></div>
                            </div>
                        @endif

                        {{-- Voucher Input Form --}}
                        @if(! $isDealerPickup)
                        <div style="margin-top:14px;padding:12px;border:1px solid var(--line);border-radius:8px;">
                            <div style="font-weight:600;margin-bottom:8px;">Masukkan Voucher</div>
                            @if(isset($voucherCode) && $voucherCode)
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin-bottom:8px;">
                                    <div>
                                        <span style="font-weight:700;color:#166534;">{{ $voucherCode }}</span>
                                        <div style="font-size:11px;color:#15803d;">Potongan: Rp {{ number_format($voucherDiscount, 0, ',', '.') }}</div>
                                    </div>
                                    <form method="post" action="{{ route('buyer.checkout.removeVoucher') }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline" style="padding:4px 8px;font-size:11px;color:#dc2626;border-color:#fca5a5;">Hapus</button>
                                    </form>
                                </div>
                            @else
                                <form method="post" action="{{ route('buyer.checkout.applyVoucher') }}" style="margin-top:8px;">
                                    @csrf
                                    <div style="display:grid;gap:8px;">
                                        <input type="text" name="voucher_code" value="{{ old('voucher_code') }}" placeholder="Kode voucher" class="form-input" style="padding:8px 12px;border:1px solid var(--line);border-radius:4px;text-transform:uppercase;">
                                        <button type="submit" class="btn btn-primary" style="width:100%;padding:8px;">Apply Voucher</button>
                                    </div>
                                </form>
                            @endif
                            @if(session('voucher_error'))
                                <div style="margin-top:6px;color:#dc2626;font-size:12px;">{{ session('voucher_error') }}</div>
                            @endif
                            @if(session('voucher_success'))
                                <div style="margin-top:6px;color:#16a34a;font-size:12px;">{{ session('voucher_success') }}</div>
                            @endif
                        </div>
                        @endif

                        <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--line);font-size:15px;font-weight:700;color:var(--text, #111);">
                            Tagihan Saat Ini: <span style="font-family:var(--mono);color:#2563eb;">Rp {{ number_format((float) $total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($isDealerPickup)
                        <div style="height:14px;"></div>
                        <div style="font-weight:600;">Pengambilan</div>
                        <div style="height:8px;"></div>
                        <div class="muted" style="line-height:1.8;">
                            Ambil di Dealer / Workshop<br>
                            <span style="color:#4ade80;font-weight:500;">Gratis Ongkir — Tidak ada biaya pengiriman</span>
                        </div>
                    @else
                        <div style="height:14px;"></div>
                        <div style="font-weight:600;">Address</div>
                        <div style="height:8px;"></div>
                        <div class="muted" style="line-height:1.8;">
                            {{ $address->recipient_name }} — {{ $address->phone }}<br>
                            {{ $address->address_line1 }} {{ $address->address_line2 }}<br>
                            {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                        </div>

                        <div style="height:14px;"></div>
                        <div style="font-weight:600;">Shipping</div>
                        <div style="height:8px;"></div>
                        <div class="muted">{{ $shipping['courier'] }} — {{ $shipping['service'] }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

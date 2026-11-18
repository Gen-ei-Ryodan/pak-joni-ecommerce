@extends('layouts.buyer-dashboard')

@section('title', 'Profile Customer')

@section('dashboard-content')
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h1 style="font-size:20px;font-weight:700;">Profile Customer</h1>
    </div>

    <div class="panel" style="padding:20px;border-radius:12px;background:var(--card-bg, #fff);">
        <div style="margin-bottom:18px;">
            <div style="font-weight:600;font-size:15px;margin-bottom:4px;">Kelengkapan Data Customer</div>
            <div class="muted" style="font-size:13px;line-height:1.5;">
                Data ini digunakan sebagai sumber data resmi untuk transaksi, penerbitan Surat Pemesanan Kendaraan (SPK), dan Faktur Pembelian.
            </div>
        </div>

        <form method="post" action="{{ route('buyer.profile.update') }}">
            @csrf
            @method('PUT')

            <div style="display:grid;gap:16px;max-width:560px;">
                <div class="field">
                    <label style="display:block;color:var(--muted, #666);font-size:12px;margin-bottom:6px;font-weight:600;">
                        Nama Lengkap <span style="color:#ef4444;">*</span>
                    </label>
                    <input class="input" style="width:100%;min-width:0;padding:10px 12px;border:1px solid var(--line, #e5e7eb);border-radius:8px;" 
                           name="name" value="{{ old('name', $user->name) }}" required placeholder="Contoh: Budi Santoso">
                </div>

                <div class="field">
                    <label style="display:block;color:var(--muted, #666);font-size:12px;margin-bottom:6px;font-weight:600;">
                        Alamat Email <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="email" class="input" style="width:100%;min-width:0;padding:10px 12px;border:1px solid var(--line, #e5e7eb);border-radius:8px;" 
                           name="email" value="{{ old('email', $user->email) }}" required placeholder="budi@example.com">
                </div>

                <div class="field">
                    <label style="display:block;color:var(--muted, #666);font-size:12px;margin-bottom:6px;font-weight:600;">
                        Nomor Telepon / WhatsApp <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="tel" class="input" style="width:100%;min-width:0;padding:10px 12px;border:1px solid var(--line, #e5e7eb);border-radius:8px;" 
                           name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="Contoh: 081234567890">
                </div>

                <div class="field">
                    <label style="display:block;color:var(--muted, #666);font-size:12px;margin-bottom:6px;font-weight:600;">
                        Nomor KTP (NIK) <span style="color:#ef4444;">*</span>
                    </label>
                    <input class="input" style="width:100%;min-width:0;padding:10px 12px;border:1px solid var(--line, #e5e7eb);border-radius:8px;" 
                           name="ktp_no" value="{{ old('ktp_no', $user->ktp_no) }}" required placeholder="16 digit NIK sesuai KTP">
                    <div class="muted" style="font-size:11.5px;margin-top:4px;">Wajib diisi untuk verifikasi identitas pemesanan kendaraan & administrasi STNK/BPKB.</div>
                </div>

                <div style="margin-top:8px;">
                    <button class="btn btn-primary" type="submit" style="padding:10px 24px;">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
@endsection

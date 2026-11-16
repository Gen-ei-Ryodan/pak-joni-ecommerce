<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SURAT PEMESANAN MOTOR - {{ $spk['spk_no'] ?? $order->order_no }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 12mm 8mm 12mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #000;
        }

        /* Tabel Utama Grid SPK */
        table.spk-grid {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        /* Header Judul */
        .title-bar {
            background-color: #ffffff;
            border-bottom: 2px solid #000;
            text-align: center;
            font-size: 14.5px;
            font-weight: bold;
            padding: 5px 0;
            letter-spacing: 0.5px;
        }

        /* Border Helper */
        .b-right { border-right: 1.5px solid #000; }
        .b-bottom { border-bottom: 1.5px solid #000; }
        .b-bottom-thick { border-bottom: 2px solid #000; }

        /* Kop Surat */
        .kop-box {
            padding: 5px 8px;
            vertical-align: middle;
        }
        .kop-box img {
            width: 100%;
            max-height: 65px;
            display: block;
        }

        /* Cell Styling */
        td.cell-label {
            font-size: 9px;
            font-weight: bold;
            padding: 4px 6px;
            vertical-align: middle;
            background-color: #fafafa;
        }
        td.cell-value {
            padding: 4px 7px;
            font-size: 10px;
            vertical-align: middle;
        }

        /* Checkbox Box */
        .chk-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.5px solid #000;
            text-align: center;
            line-height: 11px;
            font-size: 10px;
            font-family: DejaVu Sans, sans-serif;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 4px;
        }

        /* Baris Checklist Dokumen Bawah */
        table.docs-checklist-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.docs-checklist-table td {
            border-right: 1px solid #000;
            padding: 4px 5px;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
        }
        table.docs-checklist-table td:last-child {
            border-right: none;
        }

        /* Bagian Ketentuan Pemesanan */
        .ketentuan-title {
            font-size: 8.5px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .ketentuan-list {
            font-size: 7.5px;
            line-height: 10.5px;
        }
        .ketentuan-list ol {
            padding-left: 14px;
            margin: 0;
        }

        /* Kolom Tanda Tangan */
        .sign-title {
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            padding-top: 4px;
        }
        .sign-area {
            height: 80px;
            position: relative;
        }
        .sign-name {
            position: absolute;
            bottom: 4px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <table class="spk-grid">
        <!-- 1. Header Judul -->
        <tr>
            <td colspan="4" class="title-bar">
                SURAT PEMESANAN MOTOR
            </td>
        </tr>

        <!-- 2. Baris Kop Surat (Kiri) dan Nomor SPK (Kanan) -->
        <tr>
            <td colspan="2" class="kop-box b-right b-bottom" style="width: 53%;">
                @php
                    $bannerPath = public_path('assets/images/jomoto_banner_brands.jpg');
                    $bannerBase64 = file_exists($bannerPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($bannerPath)) : '';
                @endphp
                @if($bannerBase64)
                    <img src="{{ $bannerBase64 }}" alt="JOMOTO CENTER BRANDS">
                @else
                    <div style="font-size: 12px; font-weight: bold;">JOMOTO CENTER</div>
                    <div style="font-size: 8px;">Jl. Kapasari No. 73 Surabaya - 60141</div>
                @endif
            </td>
            <td class="cell-label b-right b-bottom" style="width: 17%;">NOMOR</td>
            <td class="cell-value b-bottom" style="width: 30%; font-size: 13px; font-weight: bold; letter-spacing: 0.5px;">
                {{ $spk['spk_no'] }}
            </td>
        </tr>

        <!-- 3. Tanggal Pemesanan -->
        <tr>
            <td class="cell-label b-right b-bottom" style="width: 18%;">NAMA PEMESAN</td>
            <td class="cell-value b-right b-bottom" style="width: 35%; font-weight: bold;">
                {{ strtoupper($spk['buyer_name']) }}
            </td>
            <td class="cell-label b-right b-bottom">TANGGAL PEMESANAN</td>
            <td class="cell-value b-bottom" style="font-size: 11px;">
                {{ \Illuminate\Support\Carbon::parse($spk['spk_date'])->translatedFormat('d - m - Y') }}
            </td>
        </tr>

        <!-- 4. Nomor Kontak & Merek/Tipe/Model -->
        <tr>
            <td class="cell-label b-right b-bottom">NOMOR KONTAK</td>
            <td class="cell-value b-right b-bottom">
                {{ $spk['buyer_phone'] }}
            </td>
            <td class="cell-label b-right b-bottom">MEREK & TIPE/MODEL</td>
            <td class="cell-value b-bottom" style="font-weight: bold;">
                {{ strtoupper($spk['motor_model']) }}
            </td>
        </tr>

        <!-- 5. Nama STNK & BPKB dan Warna / Tahun Rakitan -->
        <tr>
            <td class="cell-label b-right b-bottom">NAMA STNK & BPKB</td>
            <td class="cell-value b-right b-bottom">
                {{ strtoupper($spk['stnk_name']) }}
            </td>
            <td class="cell-label b-right b-bottom">WARNA</td>
            <td class="cell-value b-bottom" style="padding: 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 7px; border-right: 1px solid #000; width: 60%;">
                            {{ strtoupper($spk['motor_color']) }}
                        </td>
                        <td class="cell-label" style="width: 40%; font-size: 7.5px; text-align: center; border: none; padding: 2px;">
                            TAHUN RAKITAN: <strong style="font-size: 9px;">{{ $spk['manufacturing_year'] }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 6. Alamat STNK & BPKB dan Harga OTR -->
        <tr>
            <td class="cell-label b-right b-bottom">ALAMAT STNK & BPKB</td>
            <td class="cell-value b-right b-bottom" style="font-size: 9px; line-height: 12px;">
                {{ strtoupper($spk['stnk_address']) }}
            </td>
            <td class="cell-label b-right b-bottom">
                HARGA ON THE ROAD<br>
                <span style="font-size: 7.5px; font-weight: normal;">INCL PPN 11%</span>
            </td>
            <td class="cell-value b-bottom" style="text-align: right; padding-right: 15px; font-weight: bold; font-size: 11px;">
                {{ number_format((float) $spk['otr_price'], 0, ',', '.') }}
            </td>
        </tr>

        <!-- 7. Nomor KTP dan Diskon -->
        <tr>
            <td class="cell-label b-right b-bottom">NOMOR KTP</td>
            <td class="cell-value b-right b-bottom" style="letter-spacing: 0.5px;">
                {{ $spk['buyer_nik'] }}
            </td>
            <td class="cell-label b-right b-bottom">DISKON</td>
            <td class="cell-value b-bottom" style="text-align: right; padding-right: 15px; font-size: 11px;">
                {{ $spk['discount'] > 0 ? number_format((float) $spk['discount'], 0, ',', '.') : '-' }}
            </td>
        </tr>

        <!-- 8. Pembayaran (Cash/Kredit) dan Harga Setelah Diskon -->
        @php
            $isCash = ($spk['payment_type'] ?? 'cash') === 'cash';
            $isKredit = !$isCash;
            $checkedDocs = (array) ($spk['checklist_docs'] ?? []);
        @endphp
        <tr>
            <td class="cell-label b-right b-bottom">PEMBAYARAN</td>
            <td class="cell-value b-right b-bottom">
                <span class="chk-box">{!! $isCash ? '&#10004;' : '&nbsp;' !!}</span> CASH &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="chk-box">{!! $isKredit ? '&#10004;' : '&nbsp;' !!}</span> KREDIT
                @if($isKredit && !empty($spk['leasing_provider']))
                    &nbsp; ({{ $spk['leasing_provider'] }})
                @endif
            </td>
            <td class="cell-label b-right b-bottom">
                HARGA SETELAH DISKON<br>
                <span style="font-size: 7.5px; font-weight: normal;">INCL PPN 11%</span>
            </td>
            <td class="cell-value b-bottom" style="text-align: right; padding-right: 15px; font-weight: bold; font-size: 11px;">
                {{ number_format((float) $spk['price_after_discount'], 0, ',', '.') }}
            </td>
        </tr>

        <!-- 9. Uang Muka Kredit & Catatan Permintaan -->
        <tr>
            <td class="cell-label b-right b-bottom">UANG MUKA KREDIT</td>
            <td class="cell-value b-right b-bottom">
                Rp &nbsp;{{ $spk['dp_amount'] > 0 ? number_format((float) $spk['dp_amount'], 0, ',', '.') : '-' }}
            </td>
            <td rowspan="4" colspan="2" class="b-bottom" style="vertical-align: top; padding: 6px 8px;">
                <div style="font-size: 8.5px; font-weight: bold; margin-bottom: 4px;">CATATAN:</div>
                <div style="font-size: 9.5px; line-height: 14px;">
                    {!! nl2br(e($spk['spk_notes'] ?: '-')) !!}
                </div>
            </td>
        </tr>

        <!-- 10. Tenor Pembayaran -->
        <tr>
            <td class="cell-label b-right b-bottom">TENOR PEMBAYARAN</td>
            <td class="cell-value b-right b-bottom">
                {{ $spk['credit_tenor'] ?: '-' }}
            </td>
        </tr>

        <!-- 11. Angsuran Perbulan -->
        <tr>
            <td class="cell-label b-right b-bottom">ANGSURAN PERBULAN</td>
            <td class="cell-value b-right b-bottom">
                Rp &nbsp;{{ $spk['monthly_installment'] ? number_format((float) $spk['monthly_installment'], 0, ',', '.') : '-' }}
            </td>
        </tr>

        <!-- 12. Uang Tanda Jadi -->
        <tr>
            <td class="cell-label b-right b-bottom">UANG TANDA JADI</td>
            <td class="cell-value b-right b-bottom" style="font-weight: bold;">
                Rp &nbsp;{{ number_format((float) ($spk['booking_fee'] ?? 0), 0, ',', '.') }}
            </td>
        </tr>

        <!-- 13. Baris Checklist Persyaratan Kredit -->
        <tr>
            <td colspan="4" class="b-bottom-thick" style="padding: 0;">
                <table class="docs-checklist-table">
                    <tr>
                        <td>
                            <span class="chk-box">{!! in_array('ktp_suami', $checkedDocs) ? '&#10004;' : '&nbsp;' !!}</span> KTP SUAMI
                        </td>
                        <td>
                            <span class="chk-box">{!! in_array('ktp_istri', $checkedDocs) ? '&#10004;' : '&nbsp;' !!}</span> KTP ISTRI
                        </td>
                        <td>
                            <span class="chk-box">{!! in_array('kartu_keluarga', $checkedDocs) ? '&#10004;' : '&nbsp;' !!}</span> KARTU KELUARGA
                        </td>
                        <td>
                            <span class="chk-box">{!! in_array('slip_gaji', $checkedDocs) ? '&#10004;' : '&nbsp;' !!}</span> SLIP GAJI
                        </td>
                        <td>
                            <span class="chk-box">{!! in_array('rekening_listrik', $checkedDocs) ? '&#10004;' : '&nbsp;' !!}</span> REKENING LISTRIK
                        </td>
                        <td>
                            <span class="chk-box">{!! in_array('bukti_pbb', $checkedDocs) ? '&#10004;' : '&nbsp;' !!}</span> BUKTI PBB
                        </td>
                        <td style="border-right: none;">
                            <span class="chk-box">{!! in_array('rekening_tabungan', $checkedDocs) ? '&#10004;' : '&nbsp;' !!}</span> REKENING TABUNGAN 3 BLN
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 14. Bagian Bawah: Ketentuan Pemesanan (Kiri) & Tanda Tangan Penjual / Pemesan (Kanan) -->
        <tr>
            <td colspan="2" class="b-right" style="vertical-align: top; padding: 6px 10px; width: 62%;">
                <div class="ketentuan-title">KETENTUAN PEMESANAN:</div>
                <div class="ketentuan-list">
                    <ol>
                        <li>Surat Pesanan ini bukan merupakan bukti pembayaran kepada CV. JOWIMAR MANDIRI MAKMUR.</li>
                        <li>Surat Pesanan ini berlaku bila CV. JOWIMAR MANDIRI MAKMUR telah menerima tanda jadi minimal Rp. 500.000,- per unit.</li>
                        <li>Surat Pesanan harus ditandatangani oleh pemesan dan pimpinan dealer serta berlaku 1(satu) bulan sejak pemesanan.</li>
                        <li>Uang Jaminan Pemesanan tidak dapat dikembalikan bila pihak pemesan membatalkan pesanan secara sepihak.</li>
                        <li>Penyerahan kendaraan dilakukan bila pihak pemesan telah melunasi seluruh harga kendaraan.</li>
                        <li>Harga Kendaraan tidak mengikat dan sewaktu-waktu dapat berubah tanpa pemberitahuan terlebih dahulu.</li>
                        <li>Surat Pesanan ini dapat dibatalkan bila terjadi Force Majeur seperti embargo, bencana alam, perang dan lainnya.</li>
                        <li>Transaksi pembayaran harus melalui rekening an. <strong>CV. JOWIMAR MANDIRI MAKMUR Bank BCA ac. 2141703333</strong></li>
                        <li>Untuk Aplikasi Kredit maka Nama STNK/BPKB harus sama dengan Nama Pengajuan di Bank/leasing.<br>
                            Dokumen untuk pengajuan kredit: KTP, KK, Akta Nikah, Slip Gaji dan Copy Rekening Tabungan 3 bulan terakhir.
                        </li>
                    </ol>
                    <div style="margin-top: 4px; font-weight: bold; font-size: 7px;">
                        Pemesan telah membaca dan memahami ketentuan di atas.
                    </div>
                </div>
            </td>
            <td style="vertical-align: top; width: 19%; padding: 0;" class="b-right">
                <div class="sign-title">Penjual</div>
                <div class="sign-area">
                    <div class="sign-name">{{ $spk['seller_name'] }}</div>
                </div>
            </td>
            <td style="vertical-align: top; width: 19%; padding: 0;">
                <div class="sign-title">Pemesan</div>
                <div class="sign-area">
                    <div class="sign-name">{{ strtoupper($spk['buyer_name']) }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>

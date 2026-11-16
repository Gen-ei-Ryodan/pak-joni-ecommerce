<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FAKTUR PEMBELIAN SEPEDA MOTOR</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 10mm 12mm 8mm 12mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
        }

        /* Tabel Utama Terpadu */
        table.main-grid {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        /* Header Judul */
        .title-cell {
            background-color: #cccccc;
            border-bottom: 2px solid #000;
            text-align: center;
            font-size: 14.5px;
            font-weight: bold;
            padding: 4px 0;
            letter-spacing: 0.5px;
        }

        /* Kop & Data Pembeli */
        .kop-cell {
            width: 53%;
            border-right: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 8px 10px 6px 10px;
            vertical-align: middle;
        }
        table.kop-content {
            width: 100%;
            border-collapse: collapse;
        }
        table.kop-content td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-logo-col {
            width: 90px;
            text-align: left;
            vertical-align: middle !important;
        }
        .kop-logo-col img {
            width: 82px;
            height: 82px;
            display: block;
        }
        .kop-text-col {
            vertical-align: middle !important;
            padding-left: 8px !important;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.2px;
            margin-bottom: 3px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .company-address {
            font-size: 10px;
            color: #111;
            margin-bottom: 2px;
        }
        .company-contact {
            font-size: 9.5px;
            color: #111;
            margin-bottom: 2px;
        }
        .company-web {
            font-size: 9.5px;
            color: #111;
            margin-bottom: 4px;
        }
        .kop-line-wrapper {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-line-wrapper td {
            border: none;
            padding: 0;
        }
        .kop-red-bar {
            height: 2.5px;
            background-color: #ff0000;
            width: 72%;
        }
        .kop-slogan-text {
            width: 28%;
            text-align: right;
            font-style: italic;
            font-size: 9.5px;
            color: #111;
            padding-left: 8px !important;
            white-space: nowrap;
        }

        .meta-cell {
            width: 47%;
            border-bottom: 2px solid #000;
            padding: 0;
            vertical-align: top;
        }

        table.meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.meta-table td {
            padding: 4px 7px;
            font-size: 10px;
            border-bottom: 1px solid #000;
            vertical-align: top;
        }
        table.meta-table td.lbl {
            width: 110px;
            font-weight: bold;
            border-right: 1px solid #000;
        }
        table.meta-table tr.last td {
            border-bottom: none;
        }

        /* Header Kolom Barang */
        .th-bar {
            border-bottom: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            background-color: #fff;
        }
        .b-right {
            border-right: 1px solid #000;
        }

        /* Item Body */
        .item-row td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10.5px;
            height: 155px;
        }

        /* Baris Total */
        .total-row td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10.5px;
        }

        /* Area Bawah */
        table.bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        /* Catatan Hitam */
        table.catatan-box {
            width: 295px;
            background-color: #000;
            border-collapse: separate;
            border-radius: 14px;
            padding: 9px 12px;
        }
        table.catatan-box td {
            color: #fff;
            border: none;
        }

        /* Kotak Tanda Tangan */
        table.sign-box {
            width: 330px;
            height: 110px;
            border: 2px solid #000;
            border-collapse: collapse;
            float: right;
        }
        table.sign-box td {
            vertical-align: bottom;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            padding-bottom: 5px;
            border: none;
        }
    </style>
</head>
<body>

    <table class="main-grid">
        <!-- 1. Header Judul -->
        <tr>
            <td colspan="4" class="title-cell">
                FAKTUR PEMBELIAN SEPEDA MOTOR
            </td>
        </tr>

        <!-- 2. Kop Surat (Kiri) dan Data Pembeli (Kanan) -->
        <tr>
            <td colspan="2" class="kop-cell">
                @php
                    $logoPath = public_path('assets/images/logo_jowimar_round.png');
                    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
                @endphp
                <table class="kop-content">
                    <tr>
                        <td class="kop-logo-col">
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Logo CV. JOWIMAR MANDIRI MAKMUR">
                            @endif
                        </td>
                        <td class="kop-text-col">
                            <div class="company-name">CV. JOWIMAR MANDIRI MAKMUR</div>
                            <div class="company-address">Jl. Kapasari No. 73 Surabaya - 60141</div>
                            <div class="company-contact">
                                <span>&#9742; 031 - 58288425</span> &nbsp;&nbsp; 
                                <span>&#9993; jowimarmandirimakmur@gmail.com</span>
                            </div>
                            <div class="company-web">
                                <span>&#127760; www.jomotocenter.com</span>
                            </div>
                            <table class="kop-line-wrapper">
                                <tr>
                                    <td class="kop-red-bar"></td>
                                    <td class="kop-slogan-text">Sahabat Bisnis Sejati</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td colspan="2" class="meta-cell">
                <table class="meta-table">
                    <tr>
                        <td class="lbl">TANGGAL</td>
                        <td>{{ !empty($invoice['invoice_date']) ? \Illuminate\Support\Carbon::parse($invoice['invoice_date'])->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">NOMOR FAKTUR</td>
                        <td>{{ $invoice['invoice_no'] ?? '-' }}</td>
                    </tr>
                    <tr class="last">
                        <td class="lbl">PEMBELI</td>
                        <td>
                            <strong>{{ strtoupper($invoice['buyer_name'] ?? $order->user?->name ?? '-') }}</strong><br>
                            {!! nl2br(e(strtoupper($invoice['buyer_address'] ?? ''))) !!}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 3. Header Tabel Barang -->
        <tr>
            <td class="th-bar b-right" style="width: 53%;">DESKRIPSI BARANG</td>
            <td class="th-bar b-right" style="width: 18%;">HARGA SATUAN</td>
            <td class="th-bar b-right" style="width: 10%;">KUANTITAS</td>
            <td class="th-bar" style="width: 19%;">TOTAL</td>
        </tr>

        <!-- 4. Baris Barang -->
        @php
            $item = $order->items->first();
            $qty = $item ? max(1, (int) $item->quantity) : 1;
            $price = $item ? ((float) $item->line_total / $qty) : (float) $order->total;
            $total = $item ? (float) $item->line_total : (float) $order->total;
            $rawItemName = $item ? ($item->name . ($item->variant_name ? ' ' . $item->variant_name : '')) : '-';
            $itemName = strtoupper($rawItemName);
        @endphp
        <tr class="item-row">
            <td class="b-right">
                <div style="font-weight: bold; margin-bottom: 6px;">{{ $rawItemName }}</div>
                <table style="width: 100%; font-size: 10px;">
                    <tr>
                        <td style="width: 110px; border: none; padding: 2px 0;">Nomor Rangka</td>
                        <td style="border: none; padding: 2px 0;">: {{ $invoice['chassis_no'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0;">Nomor Mesin</td>
                        <td style="border: none; padding: 2px 0;">: {{ $invoice['engine_no'] ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td class="b-right" style="text-align: right; padding-right: 12px;">
                <span style="float: left;">Rp</span> {{ number_format($price, 0, ',', '.') }}
            </td>
            <td class="b-right" style="text-align: center;">
                {{ $qty }}
            </td>
            <td style="text-align: right; padding-right: 12px;">
                <span style="float: left;">Rp</span> {{ number_format($total, 0, ',', '.') }}
            </td>
        </tr>

        <!-- 5. Baris Total Keseluruhan -->
        <tr class="total-row">
            <td colspan="2" class="b-right">TOTAL KESELURUHAN</td>
            <td class="b-right" style="text-align: center;">{{ $qty }}</td>
            <td style="text-align: right; padding-right: 12px;">
                <span style="float: left;">Rp</span> {{ number_format((float) $order->total, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- 6. Bagian Bawah: Kotak Catatan Hitam & Tanda Tangan -->
    <table class="bottom-table">
        <tr>
            <td style="vertical-align: top; width: 50%;">
                <table class="catatan-box">
                    <tr>
                        <td style="font-weight: bold; font-size: 8.5px; padding-bottom: 4px;">
                            CATATAN:
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 7.5px; font-style: italic; line-height: 11px;">
                            <div style="margin-bottom: 3px;">
                                &#10004; &nbsp; Harga pada Dokumen ini mengacu pada harga Jual (On The Road),
                            </div>
                            <div style="margin-bottom: 3px;">
                                &#10004; &nbsp; Harga sudah termasuk PPN 11%
                            </div>
                            <div style="margin-bottom: 3px;">
                                &#10004; &nbsp; Dokumen ini bukan merupakan tanda terima Uang,
                            </div>
                            <div>
                                &#10004; &nbsp; Pembayaran dianggap SAH apabila Keseluruhan Uang telah diterima di Rekening Resmi atas nama CV. Jowimar Mandiri Makmur.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top; width: 50%; text-align: right;">
                <table class="sign-box">
                    <tr>
                        <td style="height: 100px;">
                            {{ $invoice['signatory_name'] ?: 'JONI' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>

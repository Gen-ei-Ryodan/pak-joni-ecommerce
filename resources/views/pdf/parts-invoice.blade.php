<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FAKTUR SUKUCADANG - {{ $order->order_no }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
        }

        /* Container Border Luar Keseluruhan */
        .faktur-container {
            border: 2px solid #000;
            width: 100%;
        }

        /* Header Judul Utama */
        .faktur-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            padding: 6px 0;
            border-bottom: 2px solid #000;
            letter-spacing: 0.5px;
        }

        /* Tabel Kop & Pembeli */
        table.header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }
        table.header-table td {
            vertical-align: top;
            padding: 0;
        }

        /* Sisi Kiri: Banner Brand & Alamat & NPWP */
        td.kop-left {
            width: 48%;
            border-right: 2px solid #000;
            padding: 6px 8px 6px 8px;
        }
        td.kop-left img {
            width: 100%;
            height: auto;
            max-height: 75px;
            display: block;
            margin-bottom: 6px;
        }
        .npwp-text {
            font-size: 9.5px;
            font-weight: bold;
            color: #000;
        }

        /* Sisi Kanan: Kotak Pembeli */
        td.kop-right {
            width: 52%;
            padding: 6px 10px;
        }
        .pembeli-label {
            font-style: italic;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .pembeli-content {
            font-size: 9.5px;
            line-height: 14px;
        }

        /* Baris Tanggal & Nomor */
        table.meta-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 6px;
            font-size: 10px;
        }
        table.meta-row td {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta-lbl {
            width: 85px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .perincian-title {
            font-size: 10.5px;
            margin-bottom: 6px;
            margin-top: 4px;
        }

        /* Tabel Perincian Barang */
        table.parts-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        table.parts-table th {
            border: 1.5px solid #000;
            padding: 5px 4px;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            background-color: #ffffff;
        }
        table.parts-table td {
            border: 1.5px solid #000;
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: middle;
        }

        /* Lebar Kolom */
        .th-no     { width: 5%; text-align: center; }
        .th-kode   { width: 23%; text-align: center; }
        .th-nama   { width: 34%; text-align: center; }
        .th-satuan { width: 14%; text-align: center; }
        .th-qty    { width: 8%; text-align: center; }
        .th-total  { width: 16%; text-align: center; }

        /* Baris Kalkulasi Total Bawah */
        .row-calc td {
            border: 1.5px solid #000;
            padding: 3.5px 6px;
            font-size: 9px;
            font-style: italic;
            font-weight: bold;
        }
        .calc-label {
            text-align: left;
        }
        .calc-value {
            text-align: right;
            font-style: normal;
        }

        /* Kotak Tanda Tangan Penjual */
        .signature-section {
            margin-top: 14px;
            width: 100%;
        }
        .seller-sign-box {
            width: 160px;
            height: 95px;
            border: 2px solid #000;
            padding: 5px 8px;
            position: relative;
        }
        .seller-title {
            font-style: italic;
            font-weight: bold;
            font-size: 10px;
        }
        .seller-name {
            position: absolute;
            bottom: 5px;
            left: 8px;
            right: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 9.5px;
        }
    </style>
</head>
<body>

    <!-- Header Box Utama -->
    <div class="faktur-container">
        <!-- Judul Faktur -->
        <div class="faktur-title">
            FAKTUR SUKUCADANG
        </div>

        <!-- Tabel Kop Banner (Kiri) dan Pembeli (Kanan) -->
        <table class="header-table">
            <tr>
                <!-- Sisi Kiri: Banner Brand JOMOTO & NPWP -->
                <td class="kop-left">
                    @php
                        $bannerPath = public_path('assets/images/jomoto_banner_brands.jpg');
                        $bannerBase64 = file_exists($bannerPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($bannerPath)) : '';
                    @endphp
                    @if($bannerBase64)
                        <img src="{{ $bannerBase64 }}" alt="JOMOTO CENTER BRANDS">
                    @endif
                    <div class="npwp-text">
                        npwp : 0207.2320.7561.9000
                    </div>
                </td>

                <!-- Sisi Kanan: Pembeli -->
                <td class="kop-right">
                    <div class="pembeli-label">Pembeli:</div>
                    @php
                        $snapshot = $order->address_snapshot ?? [];
                        $buyerName = $order->buyer_name ?: ($snapshot['recipient_name'] ?? $order->user?->name ?? '-');
                        $buyerPhone = $snapshot['phone'] ?? $order->user?->phone ?? '';
                        $buyerAddress = $order->buyer_address ?: collect([
                            $snapshot['address_line1'] ?? '',
                            $snapshot['address_line2'] ?? '',
                            $snapshot['city'] ?? '',
                            $snapshot['province'] ?? '',
                            $snapshot['postal_code'] ?? '',
                        ])->filter()->implode(', ');
                    @endphp
                    <div class="pembeli-content">
                        <strong>{{ strtoupper($buyerName) }}</strong>
                        @if($buyerPhone)
                            <div>Telp: {{ $buyerPhone }}</div>
                        @endif
                        @if($buyerAddress)
                            <div>{{ strtoupper($buyerAddress) }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tanggal & Nomor -->
    <table class="meta-row">
        <tr>
            <td class="meta-lbl">TANGGAL</td>
            <td>: &nbsp;{{ \Illuminate\Support\Carbon::parse($order->paid_at ?? $order->created_at)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-lbl">NOMOR</td>
            <td>: &nbsp;{{ $order->order_no }}</td>
        </tr>
    </table>

    <!-- Label Perincian barang -->
    <div class="perincian-title">
        Perincian barang
    </div>

    <!-- Tabel Daftar Suku Cadang -->
    <table class="parts-table">
        <thead>
            <tr>
                <th class="th-no">No.</th>
                <th class="th-kode">Kode Part</th>
                <th class="th-nama">Nama Part</th>
                <th class="th-satuan">Harga satuan</th>
                <th class="th-qty">Q'tity</th>
                <th class="th-total">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = $order->partItems();
                $rowCount = $items->count();
                $minRows = 14; // Menyesuaikan layout tinggi pada gambar referensi
                $subtotal = 0;
            @endphp

            @foreach($items as $idx => $it)
                @php
                    $qty = max(1, (int) $it->quantity);
                    $unitPrice = $qty > 0 ? ((float) $it->line_total / $qty) : (float) $it->price;
                    $lineTotal = (float) $it->line_total;
                    $subtotal += $lineTotal;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="text-align: center;">{{ $it->sku ?: '-' }}</td>
                    <td>{{ $it->name }}{{ $it->variant_name ? ' - '.$it->variant_name : '' }}</td>
                    <td style="text-align: right;">{{ number_format($unitPrice, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $qty }}</td>
                    <td style="text-align: right;">{{ number_format($lineTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <!-- Filler Rows untuk menjaga layout baris penuh seperti di cetakan fisik -->
            @for($i = $rowCount; $i < $minRows; $i++)
                <tr>
                    <td style="height: 16px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor

            @php
                $discount = (float) ($order->discount_amount ?? 0);
                $afterDiscount = max(0, $subtotal - $discount);
                $ppn = round($afterDiscount * 0.11);
                $grandTotal = $afterDiscount + $ppn;
            @endphp

            <!-- Baris Kalkulasi -->
            <tr class="row-calc">
                <td colspan="5" class="calc-label">TOTAL HARGA BARANG SEBELUM DISKON DAN PPN 11%</td>
                <td class="calc-value">{{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr class="row-calc">
                <td colspan="5" class="calc-label">DISKON</td>
                <td class="calc-value">{{ $discount > 0 ? number_format($discount, 0, ',', '.') : '-' }}</td>
            </tr>
            <tr class="row-calc">
                <td colspan="5" class="calc-label">HARGA SETELAH DISKON</td>
                <td class="calc-value">{{ number_format($afterDiscount, 0, ',', '.') }}</td>
            </tr>
            <tr class="row-calc">
                <td colspan="5" class="calc-label">PPN 11%</td>
                <td class="calc-value">{{ number_format($ppn, 0, ',', '.') }}</td>
            </tr>
            <tr class="row-calc">
                <td colspan="5" class="calc-label">HARGA SETELAH PPN 11%</td>
                <td class="calc-value">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Kotak Tanda Tangan Penjual di Kiri Bawah -->
    <div class="signature-section">
        <div class="seller-sign-box">
            <div class="seller-title">Penjual,</div>
            <div class="seller-name">{{ $order->signatory_name ?: 'JONI' }}</div>
        </div>
    </div>

</body>
</html>

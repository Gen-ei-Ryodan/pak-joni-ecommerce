<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>KWITANSI - {{ $receipt['receipt_no'] ?? $order->order_no }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 15mm 15mm 15mm 15mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        }

        /* Container kwitansi dengan border titik-titik (dotted) */
        .kwitansi-border {
            border: 1.5px dotted #000;
            width: 100%;
            position: relative;
        }

        /* Tabel 2 Kolom Utama: Kiri (Sidebar Banner) & Kanan (Konten Kwitansi) */
        table.layout-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.layout-table td {
            vertical-align: top;
            padding: 0;
        }

        /* Kolom Kiri: Sidebar Banner Rotated */
        td.sidebar-cell {
            width: 72px;
            border-right: 1.5px dotted #000;
            padding: 0;
            text-align: center;
            vertical-align: top !important;
        }
        td.sidebar-cell img {
            width: 68px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Kolom Kanan: Isi Kwitansi */
        td.content-cell {
            padding: 12px 25px 12px 20px;
            position: relative;
        }

        /* Nomor Kwitansi di Pojok Kanan Atas */
        .receipt-number {
            text-align: right;
            font-size: 19px;
            font-weight: 800;
            font-style: italic;
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        /* Tabel Data Kwitansi */
        table.receipt-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.receipt-data td {
            padding: 6px 0;
            vertical-align: top;
        }
        .col-label {
            width: 155px;
            font-style: italic;
            font-weight: 500;
            white-space: nowrap;
        }
        .col-colon {
            width: 25px;
            text-align: center;
            font-style: italic;
        }
        .col-value {
            vertical-align: top;
        }

        /* Garis bawah pada bagian Untuk Pembayaran */
        .payment-for-line {
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .chassis-engine-line {
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            font-weight: 500;
        }

        /* Kotak Terbilang Nominal Rp */
        .terbilang-box {
            display: inline-block;
            border: 1.5px solid #000;
            padding: 5px 14px;
            font-size: 16px;
            font-weight: bold;
            font-family: Arial, Helvetica, sans-serif;
            min-width: 200px;
        }

        /* Bagian Tanggal & Lokasi Kanan Bawah */
        .date-location {
            margin-top: 15px;
            text-align: right;
            font-size: 13px;
            padding-right: 25px;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Footer Catatan Kiri Bawah */
        .receipt-footer-note {
            margin-top: 15px;
            font-size: 10px;
            font-style: italic;
            line-height: 14px;
            font-weight: 600;
            max-width: 320px;
        }
    </style>
</head>
<body>

    <div class="kwitansi-border">
        <table class="layout-table">
            <tr>
                <!-- Sidebar Banner Brand Kiri -->
                <td class="sidebar-cell">
                    @php
                        $sidebarPath = public_path('assets/images/jomoto_kwitansi_sidebar.png');
                        $sidebarBase64 = file_exists($sidebarPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($sidebarPath)) : '';
                    @endphp
                    @if($sidebarBase64)
                        <img src="{{ $sidebarBase64 }}" alt="JOMOTO CENTER BRANDS">
                    @endif
                </td>

                <!-- Konten Utama Kwitansi -->
                <td class="content-cell">
                    <!-- Nomor Kwitansi di Pojok Kanan Atas -->
                    <div class="receipt-number">
                        {{ $receipt['receipt_no'] }}
                    </div>

                    <!-- Baris-Baris Data Kwitansi -->
                    <table class="receipt-data">
                        <!-- Telah terima dari -->
                        <tr>
                            <td class="col-label">Telah terima dari</td>
                            <td class="col-colon">:</td>
                            <td class="col-value">
                                <span style="font-weight: bold; font-size: 13.5px;">{{ strtoupper($receipt['received_from'] ?? '-') }}</span>
                            </td>
                        </tr>

                        <!-- Uang Sejumlah (Terbilang Kata) -->
                        <tr>
                            <td class="col-label">Uang SejumLah</td>
                            <td class="col-colon">:</td>
                            <td class="col-value">
                                <span style="font-weight: bold; font-style: italic; font-size: 12px; text-transform: uppercase;">
                                    {{ $receipt['amount_words'] }}
                                </span>
                            </td>
                        </tr>

                        <!-- Untuk Pembayaran -->
                        <tr>
                            <td class="col-label">Untuk Pembayaran</td>
                            <td class="col-colon">:</td>
                            <td class="col-value">
                                <div class="payment-for-line">
                                    {{ $receipt['payment_for'] }}
                                </div>
                                <div class="chassis-engine-line">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                                        <tr>
                                            <td style="width: 120px; padding: 0;">Nomor Rangka</td>
                                            <td style="padding: 0;">: &nbsp;{{ $receipt['chassis_no'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 120px; padding: 2px 0 0 0;">Nomor Mesin</td>
                                            <td style="padding: 2px 0 0 0;">: &nbsp;{{ $receipt['engine_no'] ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>

                        <!-- Terbilang (Nominal Rp) -->
                        <tr>
                            <td class="col-label" style="padding-top: 14px;">Terbilang</td>
                            <td class="col-colon" style="padding-top: 14px;">:</td>
                            <td class="col-value" style="padding-top: 10px;">
                                <div class="terbilang-box">
                                    <span style="font-weight: normal; margin-right: 15px;">Rp</span>
                                    <span>{{ number_format((float) $receipt['amount'], 0, ',', '.') }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Tanggal Kota Surabaya di Kanan Bawah -->
                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                        <tr>
                            <td style="vertical-align: bottom; width: 60%; padding: 0;">
                                <!-- Catatan Sah di Kiri Bawah -->
                                <div class="receipt-footer-note">
                                    Pembayaran baru SAH bila Uang telah<br>
                                    masuk ke rekening Resmi Perusahaan.
                                </div>
                            </td>
                            <td style="vertical-align: bottom; width: 40%; text-align: right; padding: 0;">
                                <div class="date-location">
                                    Surabaya, &nbsp;&nbsp; {{ \Illuminate\Support\Carbon::parse($receipt['receipt_date'])->translatedFormat('d F Y') }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $order->order_no }} - {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background: #f8fafc;
            padding: 30px 16px;
            font-size: 13px;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .logo { font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
        .logo span { color: #0055DA; }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h1 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .invoice-title .order-no {
            font-family: monospace;
            font-size: 14px;
            color: #64748b;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
        }
        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .info-block p { color: #334155; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-table {
            width: 320px;
            margin-left: auto;
            margin-bottom: 30px;
        }
        .totals-table td {
            padding: 8px 12px;
            border: none;
        }
        .totals-table tr.total-row td {
            font-weight: 800;
            font-size: 16px;
            color: #0f172a;
            border-top: 2px solid #e2e8f0;
            padding-top: 12px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .actions-bar {
            text-align: center;
            margin-top: 24px;
        }
        .btn-print {
            background: #0055DA;
            color: #fff;
            border: none;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 13px;
            border-radius: 8px;
            cursor: pointer;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { border: none; box-shadow: none; padding: 0; }
            .actions-bar { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <div class="logo">JOMOTO <span>CENTER</span></div>
                <p style="color:#64748b;font-size:12px;margin-top:4px;">Official Dealership & Part Mechanic Support</p>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <div class="order-no">{{ $order->order_no }}</div>
                <div style="margin-top:6px;">
                    <span class="badge {{ $order->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                        {{ strtoupper($order->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <h3>Informasi Pesanan</h3>
                <p><strong>Tanggal:</strong> {{ $order->created_at->format('d M Y, H:i') }}</p>
                @if($order->paid_at)
                    <p><strong>Dibayar:</strong> {{ $order->paid_at->format('d M Y, H:i') }}</p>
                @endif
                <p><strong>Metode Pembayaran:</strong> QRIS / Bank Transfer</p>
                <p><strong>Status Pesanan:</strong> {{ $order->statusLabel() }}</p>
            </div>
            <div class="info-block">
                <h3>Penerima & Pengiriman</h3>
                @if($order->isDealerPickup())
                    <p><strong>Pengambilan:</strong> Ambil di Dealer / Workshop</p>
                    <p><strong>Pelanggan:</strong> {{ $order->user->name ?? '-' }} ({{ $order->user->email ?? '-' }})</p>
                @else
                    @php($addr = $order->address_snapshot)
                    <p><strong>Penerima:</strong> {{ $addr['recipient_name'] ?? '-' }}</p>
                    <p><strong>Telepon:</strong> {{ $addr['phone'] ?? '-' }}</p>
                    <p><strong>Alamat:</strong> {{ $addr['address_line1'] ?? '-' }}{{ !empty($addr['address_line2']) ? ', '.$addr['address_line2'] : '' }}, {{ $addr['city'] ?? '-' }}, {{ $addr['province'] ?? '-' }} {{ $addr['postal_code'] ?? '' }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item / Produk</th>
                    <th class="text-center">Varian</th>
                    <th class="text-right">Harga</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $it)
                    <tr>
                        <td>
                            <strong>{{ $it->name }}</strong>
                            @if($it->isIndent())
                                <span style="display:inline-block;margin-left:6px;font-size:10px;padding:2px 6px;border-radius:10px;background:#fef3c7;color:#92400e;">Indent</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $it->variant_name ?: '-' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $it->price, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $it->quantity }}</td>
                        <td class="text-right">Rp {{ number_format((float) $it->price * $it->quantity, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if((float) $order->discount_amount > 0)
                <tr>
                    <td style="color:#16a34a;">Potongan Diskon / Voucher</td>
                    <td class="text-right" style="color:#16a34a;">- Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td>Ongkos Kirim</td>
                <td class="text-right">
                    @if($order->isDealerPickup() || (float)$order->shipping_cost == 0)
                        Gratis
                    @else
                        Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}
                    @endif
                </td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
            </tr>
            @if($order->is_indent)
                <tr>
                    <td style="color:#92400e;">DP (Telah Dibayar)</td>
                    <td class="text-right" style="color:#92400e;">Rp {{ number_format((float) $order->dp_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Sisa Pelunasan</td>
                    <td class="text-right" style="color:#64748b;">Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
        </table>

        <div style="border-top:1px dashed #cbd5e1;padding-top:20px;font-size:11px;color:#94a3b8;text-align:center;">
            Terima kasih telah berbelanja di JOMOTO CENTER.<br>
            Invoice ini diterbitkan otomatis oleh sistem dan sah tanpa tanda tangan basah.
        </div>
    </div>

    <div class="actions-bar">
        <button class="btn-print" onclick="window.print()">Cetak Dokumen (Print)</button>
    </div>
</body>
</html>

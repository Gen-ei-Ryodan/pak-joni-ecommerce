<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Order {{ $order->order_no }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f7;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7;padding:24px 0;">
        <tr>
            <td align="center">
                <table width="640" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#1a1a2e;padding:28px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:0.5px;">{{ config('app.name') }}</div>
                                        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Dealer Resmi Motor & Suku Cadang</div>
                                    </td>
                                    <td align="right">
                                        <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Sales Order</div>
                                        <div style="font-size:16px;font-weight:700;color:#ffffff;margin-top:4px;font-family:monospace;">{{ $order->order_no }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Status Badge --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background-color:#fef3c7;border:1px solid #f59e0b;border-radius:20px;padding:6px 16px;">
                                        <span style="font-size:12px;font-weight:600;color:#92400e;text-transform:uppercase;">{{ $order->statusLabel() }}</span>
                                    </td>
                                    @if($order->is_indent)
                                    <td style="padding-left:8px;">
                                        <td style="background-color:#dbeafe;border:1px solid #3b82f6;border-radius:20px;padding:6px 16px;">
                                            <span style="font-size:12px;font-weight:600;color:#1e40af;">INDENT</span>
                                        </td>
                                    </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Order Info + Customer Info --}}
                    <tr>
                        <td style="padding:20px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%" valign="top" style="padding-right:16px;">
                                        <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Informasi Pesanan</div>
                                        <table cellpadding="0" cellspacing="0" style="font-size:13px;color:#1e293b;line-height:1.8;">
                                            <tr><td style="color:#64748b;padding-right:12px;">Tanggal</td><td style="font-weight:600;">{{ $order->created_at->isoFormat('D MMMM Y') }}</td></tr>
                                            <tr><td style="color:#64748b;padding-right:12px;">No. Order</td><td style="font-weight:600;font-family:monospace;">{{ $order->order_no }}</td></tr>
                                            <tr><td style="color:#64748b;padding-right:12px;">Pembayaran</td><td style="font-weight:600;">{{ ucfirst($order->payment_method ?? '-') }}</td></tr>
                                            <tr><td style="color:#64748b;padding-right:12px;">Pengiriman</td><td style="font-weight:600;">{{ $order->shippingTypeLabel() }}</td></tr>
                                        </table>
                                    </td>
                                    <td width="50%" valign="top" style="padding-left:16px;border-left:1px solid #e2e8f0;">
                                        <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Data Pembeli</div>
                                        <table cellpadding="0" cellspacing="0" style="font-size:13px;color:#1e293b;line-height:1.8;">
                                            <tr><td style="color:#64748b;padding-right:12px;">Nama</td><td style="font-weight:600;">{{ $order->user->name }}</td></tr>
                                            <tr><td style="color:#64748b;padding-right:12px;">Email</td><td style="font-weight:600;">{{ $order->user->email }}</td></tr>
                                            @if($order->address_snapshot && is_array($order->address_snapshot))
                                            <tr><td style="color:#64748b;padding-right:12px;vertical-align:top;">Alamat</td><td style="font-weight:600;">{{ $order->address_snapshot['address'] ?? $order->address_snapshot['recipient_name'] ?? '-' }}<br>{{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['province'] ?? '' }}</td></tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr><td style="padding:0 32px;"><hr style="border:0;border-top:1px solid #e2e8f0;margin:0;"></td></tr>

                    {{-- Items Table --}}
                    <tr>
                        <td style="padding:20px 32px;">
                            <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">Detail Pesanan</div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border-collapse:collapse;">
                                <thead>
                                    <tr style="background-color:#f8fafc;">
                                        <th align="left" style="padding:10px 12px;border-bottom:2px solid #e2e8f0;color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase;">Item</th>
                                        <th align="center" style="padding:10px 12px;border-bottom:2px solid #e2e8f0;color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase;">Qty</th>
                                        <th align="right" style="padding:10px 12px;border-bottom:2px solid #e2e8f0;color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase;">Harga</th>
                                        <th align="right" style="padding:10px 12px;border-bottom:2px solid #e2e8f0;color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td style="padding:12px;border-bottom:1px solid #f1f5f9;">
                                            <div style="font-weight:600;color:#1e293b;">{{ $item->name }}</div>
                                            @if($item->variant_name)
                                            <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $item->variant_name }}</div>
                                            @endif
                                            @if($item->sku)
                                            <div style="font-size:11px;color:#94a3b8;font-family:monospace;margin-top:2px;">SKU: {{ $item->sku }}</div>
                                            @endif
                                        </td>
                                        <td align="center" style="padding:12px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                            {{ $item->quantity }}
                                            @if($item->indent_quantity > 0)
                                            <div style="font-size:11px;color:#f59e0b;">(indent: {{ $item->indent_quantity }})</div>
                                            @endif
                                        </td>
                                        <td align="right" style="padding:12px;border-bottom:1px solid #f1f5f9;color:#475569;font-family:monospace;">
                                            Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                                        </td>
                                        <td align="right" style="padding:12px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:600;font-family:monospace;">
                                            Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    {{-- Summary --}}
                    <tr>
                        <td style="padding:0 32px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
                                            <tr>
                                                <td style="color:#64748b;padding:4px 0;">Subtotal</td>
                                                <td align="right" style="color:#1e293b;font-family:monospace;padding:4px 0;">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#64748b;padding:4px 0;">Biaya Pengiriman</td>
                                                <td align="right" style="color:#1e293b;font-family:monospace;padding:4px 0;">Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</td>
                                            </tr>
                                            @if($order->is_indent && $order->dp_amount > 0)
                                            <tr>
                                                <td style="color:#64748b;padding:4px 0;">Uang Muka (DP)</td>
                                                <td align="right" style="color:#1e293b;font-family:monospace;padding:4px 0;">Rp {{ number_format((float) $order->dp_amount, 0, ',', '.') }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td colspan="2" style="padding:8px 0 0;"><hr style="border:0;border-top:1px solid #e2e8f0;margin:0;"></td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:15px;font-weight:700;color:#1e293b;padding:8px 0 0;">Total</td>
                                                <td align="right" style="font-size:15px;font-weight:700;color:#1a1a2e;font-family:monospace;padding:8px 0 0;">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                            </tr>
                                            @if($order->is_indent && $order->remaining_amount > 0)
                                            <tr>
                                                <td style="color:#f59e0b;padding:4px 0;">Sisa Pembayaran</td>
                                                <td align="right" style="color:#f59e0b;font-weight:600;font-family:monospace;padding:4px 0;">Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Shipping Info --}}
                    @if($order->shipping_snapshot && is_array($order->shipping_snapshot))
                    <tr>
                        <td style="padding:0 32px 24px;">
                            <div style="background-color:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:16px 20px;">
                                <div style="font-size:11px;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Informasi Pengiriman</div>
                                <table cellpadding="0" cellspacing="0" style="font-size:13px;color:#1e293b;line-height:1.8;">
                                    <tr><td style="color:#166534;padding-right:12px;">Kurir</td><td style="font-weight:600;">{{ $order->shipping_snapshot['courier'] ?? $order->shipping_courier ?? '-' }}</td></tr>
                                    <tr><td style="color:#166534;padding-right:12px;">Layanan</td><td style="font-weight:600;">{{ $order->shipping_snapshot['service'] ?? '-' }}</td></tr>
                                    @if($order->shipping_receipt)
                                    <tr><td style="color:#166534;padding-right:12px;">No. Resi</td><td style="font-weight:600;font-family:monospace;">{{ $order->shipping_receipt }}</td></tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="font-size:12px;color:#64748b;line-height:1.6;">
                                            <strong>{{ config('app.name') }}</strong><br>
                                            Jl. Kapasari No.73, Surabaya, Jawa Timur<br>
                                            WhatsApp: {{ config('app.social.whatsapp', '-') }}
                                        </div>
                                    </td>
                                    <td align="right">
                                        <div style="font-size:11px;color:#94a3b8;">
                                            Email ini dikirim otomatis oleh sistem.<br>
                                            Dokumen ini bukan bukti pembayaran.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

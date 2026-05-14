@extends('layouts.buyer')

@section('title', 'Order Detail')

@section('content')
    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div>
                    <div style="font-size:18px;font-weight:600;">Order {{ $order->order_no }}</div>
                    <div class="muted" style="margin-top:6px;">Status: {{ $order->status }}</div>
                </div>
                <a class="btn" href="{{ route('buyer.orders.index') }}">Kembali</a>
            </div>

            <div style="height:14px;"></div>

            <div class="panel" style="padding:14px;">
                <div class="muted" style="display:grid;gap:8px;">
                    <div>Subtotal: <span style="font-family:var(--mono);">{{ number_format((float) $order->subtotal, 2, '.', ',') }}</span></div>
                    <div>Shipping: <span style="font-family:var(--mono);">{{ number_format((float) $order->shipping_cost, 2, '.', ',') }}</span></div>
                    <div>Total: <span style="font-family:var(--mono);">{{ number_format((float) $order->total, 2, '.', ',') }}</span></div>
                </div>

                <div style="height:14px;"></div>

                <div style="font-weight:600;">Items</div>
                <div style="height:10px;"></div>

                <div style="overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:760px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:12px;">
                                <th style="padding:10px;">SKU</th>
                                <th style="padding:10px;">Name</th>
                                <th style="padding:10px;">Variant</th>
                                <th style="padding:10px;">Qty</th>
                                <th style="padding:10px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $it)
                                <tr style="border-top:1px solid var(--line);">
                                    <td style="padding:10px;">{{ $it->sku }}</td>
                                    <td style="padding:10px;">{{ $it->name }}</td>
                                    <td style="padding:10px;color:var(--muted);">{{ $it->variant_name ?? '-' }}</td>
                                    <td style="padding:10px;">{{ $it->quantity }}</td>
                                    <td style="padding:10px;">{{ number_format((float) $it->line_total, 2, '.', ',') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

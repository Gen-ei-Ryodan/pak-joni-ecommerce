@extends('layouts.buyer')

@section('title', 'Order Detail')

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
                <div>
                    <div style="font-size:18px;font-weight:600;">Order {{ $order->order_no }}</div>
                    <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                        <span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
                        <span class="badge {{ $order->paymentStatusBadge() }}">Payment: {{ $order->payment_status }}</span>
                    </div>
                </div>
                <a class="btn" href="{{ route('buyer.orders.index') }}">Back</a>
            </div>

            <div style="height:14px;"></div>

            @if($order->status === 'unpaid' && $order->payment_status === 'pending')
                <div class="panel" style="padding:14px;margin-bottom:14px;border-color:rgba(234,179,8,0.35);background:rgba(234,179,8,0.06);">
                    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                        <div>
                            <div style="font-weight:600;">Awaiting Payment</div>
                            <div class="muted" style="margin-top:4px;font-size:13px;">Simulate payment for testing.</div>
                        </div>
                        <form method="post" action="{{ route('buyer.orders.simulatePayment', $order) }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">Simulate Payment</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="panel" style="padding:16px;">
                <div style="font-weight:600;margin-bottom:10px;">Order Information</div>
                <div style="display:grid;gap:8px;color:var(--muted);">
                    <div>Invoice: <span style="color:var(--text);font-family:var(--mono);">{{ $order->order_no }}</span></div>
                    <div>Date: <span style="color:var(--text);">{{ $order->created_at->format('d M Y H:i') }}</span></div>
                    <div>Status: <span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></div>
                    <div>Payment: <span class="badge {{ $order->paymentStatusBadge() }}">{{ $order->payment_status }}</span></div>
                    @if($order->payment_method)
                        <div>Method: <span style="color:var(--text);">{{ $order->payment_method }}</span></div>
                    @endif
                    @if($order->paid_at)
                        <div>Paid at: <span style="color:var(--text);">{{ $order->paid_at->format('d M Y H:i') }}</span></div>
                    @endif
                </div>

                <div style="height:12px;"></div>

                <div style="font-weight:600;margin-bottom:10px;">Shipping Address</div>
                <div class="muted" style="line-height:1.8;">
                    @php($addr = $order->address_snapshot)
                    <div><span style="color:var(--text);">{{ $addr['recipient_name'] ?? '-' }}</span></div>
                    <div>{{ $addr['phone'] ?? '-' }}</div>
                    <div>{{ $addr['address_line1'] ?? '-' }}{{ !empty($addr['address_line2']) ? ', '.$addr['address_line2'] : '' }}</div>
                    <div>{{ $addr['city'] ?? '-' }}, {{ $addr['province'] ?? '-' }} {{ $addr['postal_code'] ?? '' }}</div>
                </div>

                @if($order->shipping_courier)
                    <div style="height:8px;"></div>
                    <div>Courier: <span style="color:var(--text);">{{ $order->shipping_courier }}</span></div>
                    @if($order->shipping_receipt)
                        <div>Receipt: <span style="color:var(--text);font-family:var(--mono);">{{ $order->shipping_receipt }}</span></div>
                    @endif
                @endif

                <div style="height:12px;"></div>

                <div style="font-weight:600;margin-bottom:10px;">Cost Breakdown</div>
                <div class="muted" style="display:grid;gap:6px;">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Subtotal</span>
                        <span style="color:var(--text);">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Shipping</span>
                        <span style="color:var(--text);">Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:600;border-top:1px solid var(--line);padding-top:6px;">
                        <span>Total</span>
                        <span style="color:var(--text);">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div style="height:14px;"></div>

            <div class="panel" style="padding:16px;">
                <div style="font-weight:600;margin-bottom:10px;">Item</div>
                <div style="overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:700px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:12px;">
                                <th style="padding:10px;">Product</th>
                                <th style="padding:10px;">Variant</th>
                                <th style="padding:10px;">Price</th>
                                <th style="padding:10px;">Qty</th>
                                <th style="padding:10px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $it)
                                <tr style="border-top:1px solid var(--line);">
                                    <td style="padding:10px;">
                                        <div>{{ $it->name }}</div>
                                        <div class="muted" style="margin-top:4px;font-size:11px;">{{ $it->sku }}</div>
                                    </td>
                                    <td style="padding:10px;color:var(--muted);">{{ $it->variant_name ?? '-' }}</td>
                                    <td style="padding:10px;">Rp {{ number_format((float) $it->price, 0, ',', '.') }}</td>
                                    <td style="padding:10px;">{{ $it->quantity }}</td>
                                    <td style="padding:10px;">Rp {{ number_format((float) $it->line_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="height:14px;"></div>

            <div class="panel" style="padding:16px;">
                <div style="font-weight:600;margin-bottom:12px;">Order Timeline</div>
                @php
                    $timeline = [
                        ['label' => 'Order Created', 'time' => $order->created_at, 'done' => true],
                        ['label' => 'Awaiting Payment', 'time' => $order->created_at, 'done' => $order->status !== 'unpaid' || $order->payment_status === 'paid'],
                        ['label' => 'Payment Successful', 'time' => $order->paid_at, 'done' => in_array($order->status, ['paid','processing','shipped','completed'])],
                        ['label' => 'Processing', 'time' => $order->status === 'processing' ? $order->updated_at : null, 'done' => in_array($order->status, ['processing','shipped','completed'])],
                        ['label' => 'Shipped', 'time' => $order->shipped_at, 'done' => in_array($order->status, ['shipped','completed'])],
                        ['label' => 'Completed', 'time' => $order->completed_at, 'done' => $order->status === 'completed'],
                    ];
                @endphp
                <div style="display:grid;gap:0;">
                    @foreach($timeline as $t)
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div style="display:flex;flex-direction:column;align-items:center;">
                                <div style="width:12px;height:12px;border-radius:50%;{{ $t['done'] ? 'background:#4ade80;' : 'background:var(--line);' }}"></div>
                                @if(!$loop->last)
                                    <div style="width:2px;flex:1;min-height:20px;{{ $t['done'] ? 'background:#4ade80;' : 'background:var(--line);' }}"></div>
                                @endif
                            </div>
                            <div style="padding-bottom:16px;">
                                <div style="{{ $t['done'] ? 'color:var(--text);' : 'color:var(--muted);' }}">{{ $t['label'] }}</div>
                                @if($t['time'])
                                    <div class="muted" style="font-size:11px;">{{ $t['time']->format('d M Y H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection

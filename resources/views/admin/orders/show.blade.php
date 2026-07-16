@extends('layouts.admin')

@section('title', 'Order Detail')

@section('content')
    @include('admin.partials.flash')

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
            <div style="font-size:16px;font-weight:600;">Order {{ $order->order_no }}</div>
            <div class="muted" style="margin-top:6px;">
                Buyer: {{ $order->user?->email }}
                <span class="badge {{ $order->statusBadge() }}" style="margin-left:8px;">{{ $order->statusLabel() }}</span>
                <span class="badge {{ $order->paymentStatusBadge() }}" style="margin-left:4px;">Payment: {{ $order->payment_status }}</span>
            </div>
        </div>
        <a class="btn" href="{{ route('admin.orders.index') }}">Back</a>
    </div>

    <div style="height:14px;"></div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;" class="order-detail-grid">
        <div>
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

                @if($order->isDealerPickup())
                    <div style="font-weight:600;margin-bottom:10px;">Pengambilan</div>
                    <div class="muted" style="line-height:1.8;">
                        <span style="color:var(--text);">Ambil di Dealer / Workshop</span><br>
                        <span style="color:#4ade80;">Gratis Ongkir</span>
                    </div>
                @else
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
                @endif

                <div style="height:12px;"></div>

                <div style="font-weight:600;margin-bottom:10px;">Cost Breakdown</div>
                <div style="display:grid;gap:6px;" class="muted">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Subtotal</span>
                        <span style="color:var(--text);">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Shipping</span>
                        @if($order->isDealerPickup())
                            <span style="color:#4ade80;">Gratis</span>
                        @else
                            <span style="color:var(--text);">Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:600;border-top:1px solid var(--line);padding-top:6px;">
                        <span>Total</span>
                        <span style="color:var(--text);">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div style="height:14px;"></div>

            <div class="panel" style="padding:16px;">
                <div style="font-weight:600;margin-bottom:10px;">Items</div>
                <div style="overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:600px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:12px;">
                                <th style="padding:10px;">SKU</th>
                                <th style="padding:10px;">Name</th>
                                <th style="padding:10px;">Variant</th>
                                <th style="padding:10px;">Qty</th>
                                <th style="padding:10px;">Price</th>
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
                                    <td style="padding:10px;">Rp {{ number_format((float) $it->price, 0, ',', '.') }}</td>
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

        <div>
            <div class="panel" style="padding:16px;">
                <div style="font-weight:600;margin-bottom:12px;">Update Status</div>

                <div style="display:grid;gap:10px;">
                    @if($order->canTransitionTo('paid') && $order->payment_status !== 'paid')
                        <form method="post" action="{{ route('admin.orders.update', $order) }}">
                            @csrf
                            @method('put')
                            <input type="hidden" name="action" value="mark_paid">
                            <button class="btn btn-primary" type="submit" style="width:100%;">Mark as Paid</button>
                        </form>
                    @endif

                    @if($order->canTransitionTo('processing'))
                        <form method="post" action="{{ route('admin.orders.update', $order) }}">
                            @csrf
                            @method('put')
                            <input type="hidden" name="action" value="process">
                            <button class="btn" type="submit" style="width:100%;">Process Order</button>
                        </form>
                    @endif

                    @if($order->canTransitionTo('shipped'))
                        @if($order->isDealerPickup())
                            <form method="post" action="{{ route('admin.orders.update', $order) }}">
                                @csrf
                                @method('put')
                                <input type="hidden" name="action" value="ship">
                                <input type="hidden" name="dealer_pickup" value="1">
                                <button class="btn btn-primary" type="submit" style="width:100%;">Siap Diambil</button>
                            </form>
                        @else
                            <form method="post" action="{{ route('admin.orders.update', $order) }}" style="display:grid;gap:8px;">
                                @csrf
                                @method('put')
                                <input type="hidden" name="action" value="ship">
                                <div>
                                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px;">Courier</label>
                                    <input name="courier" required placeholder="JNE / J&T / SiCepat..."
                                        style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                                </div>
                                <div>
                                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px;">Receipt No.</label>
                                    <input name="receipt" required placeholder="Tracking number..."
                                        style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                                </div>
                                <button class="btn btn-primary" type="submit" style="width:100%;">Mark as Shipped</button>
                            </form>
                        @endif
                    @endif

                    @if($order->canTransitionTo('completed'))
                        <form method="post" action="{{ route('admin.orders.update', $order) }}">
                            @csrf
                            @method('put')
                            <input type="hidden" name="action" value="complete">
                            <button class="btn" type="submit" style="width:100%;">Complete Order</button>
                        </form>
                    @endif

                    @if($order->canTransitionTo('cancelled'))
                        <form method="post" action="{{ route('admin.orders.update', $order) }}" onsubmit="return confirm('Cancel this order?')">
                            @csrf
                            @method('put')
                            <input type="hidden" name="action" value="cancel">
                            <button class="btn btn-danger" type="submit" style="width:100%;">Cancel Order</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

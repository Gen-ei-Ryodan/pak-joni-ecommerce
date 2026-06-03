@extends('layouts.buyer-dashboard')

@section('title', 'Order Detail')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/order-detail.css') }}">
@endpush

@section('dashboard-content')
    @if($order->status === 'unpaid' && $order->payment_status === 'pending')
        <div class="payment-banner">
            <div>
                <div style="font-weight:600;font-size:14px;">Awaiting Payment</div>
                <div class="muted" style="margin-top:4px;font-size:13px;">Complete your payment to process this order.</div>
            </div>
            <form method="post" action="{{ route('buyer.orders.simulatePayment', $order) }}">
                @csrf
                <button class="btn btn-primary" type="submit" style="flex-shrink:0;">Pay Now</button>
            </form>
        </div>
    @endif

    <div class="order-detail-grid">
        {{-- LEFT COLUMN --}}
        <div style="display:grid;gap:20px;">
            {{-- Header --}}
            <div class="order-header">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                    <div>
                        <div class="order-id">{{ $order->order_no }}</div>
                        <div class="order-meta">
                            <div class="order-meta-item">
                                <span class="order-meta-label">Date</span>
                                <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            @if($order->paid_at)
                                <div class="order-meta-item">
                                    <span class="order-meta-label">Paid</span>
                                    <span>{{ $order->paid_at->format('d M Y, H:i') }}</span>
                                </div>
                            @endif
                            <div class="order-meta-item">
                                <span class="order-meta-label">Payment</span>
                                <span class="badge {{ $order->paymentStatusBadge() }}">{{ $order->payment_status }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <span class="badge {{ $order->statusBadge() }}" style="font-size:13px;padding:6px 16px;">{{ $order->statusLabel() }}</span>
                        <a class="btn" href="{{ route('buyer.orders.index') }}" style="flex-shrink:0;">Back</a>
                    </div>
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="shipping-card">
                <div class="card-title-sm">Shipping Address</div>
                @php($addr = $order->address_snapshot)
                <div class="shipping-name">{{ $addr['recipient_name'] ?? '-' }}</div>
                <div class="shipping-detail">
                    {{ $addr['phone'] ?? '-' }}<br>
                    {{ $addr['address_line1'] ?? '-' }}{{ !empty($addr['address_line2']) ? ', '.$addr['address_line2'] : '' }}<br>
                    {{ $addr['city'] ?? '-' }}, {{ $addr['province'] ?? '-' }} {{ $addr['postal_code'] ?? '' }}
                </div>
                @if($order->shipping_courier)
                    <div class="shipping-courier-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        {{ $order->shipping_courier }}
                        @if($order->shipping_receipt)
                            — {{ $order->shipping_receipt }}
                        @endif
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="sidebar-card">
                <div class="card-title-sm">Items ({{ $order->items->count() }})</div>
                <div>
                    @foreach ($order->items as $it)
                        <div class="item-card-modern">
                            <div class="item-thumb">
                                @if($it->part && $it->part->thumbnail_path)
                                    <img src="{{ image_url($it->part->thumbnail_path) }}" alt="">
                                @elseif($it->variant && $it->variant->part && $it->variant->part->thumbnail_path)
                                    <img src="{{ image_url($it->variant->part->thumbnail_path) }}" alt="">
                                @endif
                            </div>
                            <div class="item-info">
                                <div class="item-name">{{ $it->name }}</div>
                                <div class="item-variant">{{ $it->variant_name ?? '-' }}</div>
                                <div class="item-sku">{{ $it->sku }}</div>
                            </div>
                            <div style="text-align:center;flex-shrink:0;">
                                <div class="item-qty-badge">{{ $it->quantity }}</div>
                            </div>
                            <div class="item-pricing">
                                <div class="item-price">Rp {{ number_format((float) $it->price, 0, ',', '.') }}</div>
                                <div class="item-subtotal">Rp {{ number_format((float) $it->line_total, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="sidebar-sticky">
            {{-- Timeline --}}
            <div class="sidebar-card">
                <div class="card-title-sm">Order Timeline</div>
                <div class="timeline-modern">
                    @php($hasCurrent = false)
                    @foreach($timeline as $i => $t)
                        @php($isCurrent = !$hasCurrent && !$t['done'] && $i > 0)
                        @php($hasCurrent = $hasCurrent || $isCurrent)
                        <div class="timeline-step">
                            <div class="timeline-indicator">
                                <div class="timeline-dot {{ $t['done'] ? 'done' : ($isCurrent ? 'current' : '') }}"></div>
                                @if(!$loop->last)
                                    <div class="timeline-line {{ $t['done'] ? 'done' : 'pending' }}"></div>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-label {{ !$t['done'] ? 'pending' : '' }}">{{ $t['label'] }}</div>
                                @if($t['time'])
                                    <div class="timeline-time">{{ $t['time']->format('d M Y, H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment Summary --}}
            <div class="sidebar-card">
                <div class="card-title-sm">Payment Summary</div>
                <div class="summary-row">
                    <span style="color:var(--muted);">Subtotal</span>
                    <span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span style="color:var(--muted);">Shipping</span>
                    <span>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span style="color:var(--accent);">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="sidebar-card" style="display:grid;gap:8px;">
                @if($order->status === 'unpaid' && $order->payment_status === 'pending')
                    <form method="post" action="{{ route('buyer.orders.simulatePayment', $order) }}">
                        @csrf
                        <button class="action-btn-primary" type="submit">Pay Now</button>
                    </form>
                    <a class="action-btn-secondary" href="{{ route('buyer.parts.index') }}">Continue Shopping</a>
                @elseif($order->status === 'shipped')
                    <form method="post" action="{{ route('buyer.orders.confirmReceived', $order) }}" onsubmit="return confirm('Konfirmasi barang sudah diterima?')">
                        @csrf
                        <button class="action-btn-primary" type="submit">Barang Sudah Diterima</button>
                    </form>
                    <a class="action-btn-secondary" href="{{ route('buyer.parts.index') }}">Shop Again</a>
                @elseif(in_array($order->status, ['paid', 'processing']))
                    <a class="action-btn-secondary" href="{{ route('buyer.orders.index') }}">Track Order</a>
                    <a class="action-btn-secondary" href="{{ route('buyer.parts.index') }}">Shop Again</a>
                @elseif($order->status === 'completed')
                    <a class="action-btn-primary" href="{{ route('buyer.parts.index') }}">Buy Again</a>
                    <a class="action-btn-secondary" href="{{ route('buyer.parts.index') }}">Browse More</a>
                @elseif($order->status === 'cancelled')
                    <a class="action-btn-secondary" href="{{ route('buyer.parts.index') }}">Browse Parts</a>
                @else
                    <a class="action-btn-secondary" href="{{ route('buyer.orders.index') }}">Back to Orders</a>
                @endif
            </div>
        </div>
    </div>
@endsection

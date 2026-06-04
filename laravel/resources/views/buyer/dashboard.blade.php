@extends('layouts.buyer-dashboard')

@section('title', 'Dashboard')

@section('dashboard-content')
    <div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:center;">
        <div>
            <div style="font-size:16px;font-weight:600;">Hi, {{ auth()->user()->name }}</div>
            <div class="muted" style="margin-top:6px;">Account: {{ auth()->user()->email }}</div>
        </div>
    </div>

    <div style="height:16px;"></div>

    <div class="kpi">
        <div class="kpi-item">
            <div class="kpi-title">Total Pesanan</div>
            <div class="kpi-value">{{ $ordersCount }}</div>
        </div>
        <div class="kpi-item">
            <div class="kpi-title">Wishlist</div>
            <div class="kpi-value">{{ $wishlistCount }}</div>
        </div>
        <div class="kpi-item">
            <div class="kpi-title">Alamat</div>
            <div class="kpi-value">{{ $addressCount }}</div>
        </div>
    </div>

    @if($indentOrders->count())
        <div style="height:24px;"></div>
        <div style="font-weight:600;margin-bottom:12px;">Pesanan Indent Aktif</div>
        <div class="panel" style="padding:12px;">
            <div style="display:grid;gap:8px;">
                @foreach($indentOrders as $order)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--bg);border-radius:8px;border:1px solid var(--line);">
                        <div>
                            <a href="{{ route('buyer.orders.show', $order->order_no) }}" style="font-weight:500;">{{ $order->order_no }}</a>
                            <div class="muted" style="font-size:12px;">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                                @if($order->dp_amount > 0)
                                    | DP: Rp {{ number_format($order->dp_amount, 0, ',', '.') }}
                                @endif
                            </div>
                        </div>
                        <div class="badge {{ $order->indentStatusBadge() }}" style="padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500;">
                            {{ $order->indentStatusLabel() }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($recentOrders->count())
        <div style="height:24px;"></div>
        <div style="font-weight:600;margin-bottom:12px;">Pesanan Terbaru</div>
        <div class="panel" style="padding:12px;">
            <div style="display:grid;gap:8px;">
                @foreach($recentOrders as $order)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--bg);border-radius:8px;border:1px solid var(--line);">
                        <div>
                            <a href="{{ route('buyer.orders.show', $order->order_no) }}" style="font-weight:500;">{{ $order->order_no }}</a>
                            <div class="muted" style="font-size:12px;">{{ $order->items->count() }} item | {{ $order->created_at->format('d M Y') }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-family:var(--mono);font-size:13px;">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                            <div class="badge {{ $order->statusBadge() }}" style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;">{{ $order->statusLabel() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

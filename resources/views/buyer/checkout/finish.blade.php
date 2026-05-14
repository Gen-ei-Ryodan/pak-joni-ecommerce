@extends('layouts.buyer')

@section('title', 'Order Created')

@section('content')
    <section class="section">
        <div class="container">
            <div class="panel" style="padding:16px;">
                <div style="font-size:18px;font-weight:600;">Order Berhasil Dibuat</div>
                <div style="height:10px;"></div>
                <div class="muted" style="line-height:1.8;">
                    No. Invoice: <span style="font-family:var(--mono);">{{ $order->order_no }}</span><br>
                    Status: <span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span><br>
                    Payment: <span class="badge {{ $order->paymentStatusBadge() }}">{{ $order->payment_status }}</span>
                </div>

                <div style="height:4px;"></div>
                <div class="muted" style="line-height:1.8;">
                    Total: <span style="font-family:var(--mono);">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                </div>

                <div style="height:14px;"></div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a class="btn btn-primary" href="{{ route('buyer.orders.show', $order) }}">Lihat Order</a>
                    <a class="btn" href="{{ route('buyer.orders.index') }}">Order Saya</a>
                    <a class="btn" href="{{ route('buyer.parts.index') }}">Belanja Lagi</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@extends('layouts.buyer')

@section('title', 'Checkout Finish')

@section('content')
    <section class="section">
        <div class="container">
            <div class="panel" style="padding:16px;">
                <div style="font-size:18px;font-weight:600;">Order Berhasil Dibuat</div>
                <div style="height:10px;"></div>
                <div class="muted" style="line-height:1.8;">
                    Order No: <span style="font-family:var(--mono);">{{ $order->order_no }}</span><br>
                    Status: {{ $order->status }}
                </div>

                <div style="height:14px;"></div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a class="btn btn-primary" href="{{ route('buyer.parts.index') }}">Belanja Lagi</a>
                    <a class="btn" href="{{ url('/dashboard') }}">Dashboard</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@extends('layouts.buyer')

@section('title', 'Dashboard')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
    <main class="dash">
        <div class="container dash-grid">
            <aside class="sidebar">
                <a href="{{ url('/dashboard') }}">Dashboard</a>
                <a href="{{ route('buyer.addresses.index') }}">Addresses</a>
                <a href="{{ route('buyer.orders.index') }}">My Orders</a>
                <a href="{{ route('buyer.wishlist.index') }}">Wishlist</a>
                <a href="{{ route('buyer.cart.index') }}">Cart</a>
            </aside>

            <section class="content">
                <div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:center;">
                    <div>
                        <div style="font-size:16px;font-weight:600;">Halo, {{ auth()->user()->name }}</div>
                        <div class="muted" style="margin-top:6px;">Akun: {{ auth()->user()->email }}</div>
                    </div>
                </div>

                <div style="height:16px;"></div>

                <div class="kpi">
                    <div class="kpi-item">
                        <div class="kpi-title">Orders</div>
                        <div class="kpi-value">{{ $ordersCount }}</div>
                    </div>
                    <div class="kpi-item">
                        <div class="kpi-title">Wishlist</div>
                        <div class="kpi-value">{{ $wishlistCount }}</div>
                    </div>
                    <div class="kpi-item">
                        <div class="kpi-title">Addresses</div>
                        <div class="kpi-value">{{ $addressCount }}</div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection

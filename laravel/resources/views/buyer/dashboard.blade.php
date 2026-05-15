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
@endsection

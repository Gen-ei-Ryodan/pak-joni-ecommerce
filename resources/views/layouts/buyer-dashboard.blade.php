@extends('layouts.buyer')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
    <main class="dash">
        <div class="container dash-grid">
            <aside class="sidebar">
                <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('buyer.addresses.index') }}" class="{{ request()->is('addresses*') ? 'active' : '' }}">Addresses</a>
                <a href="{{ route('buyer.orders.index') }}" class="{{ request()->is('orders*') ? 'active' : '' }}">My Orders</a>
                <a href="{{ route('buyer.wishlist.index') }}" class="{{ request()->is('wishlist*') ? 'active' : '' }}">Wishlist</a>
                <a href="{{ route('buyer.cart.index') }}" class="{{ request()->is('cart*') ? 'active' : '' }}">Cart</a>
            </aside>

            <section class="content">
                @if (session('status'))
                    <div class="panel" style="padding:10px 12px;margin-bottom:14px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="panel" style="padding:10px 12px;margin-bottom:14px;border-color:rgba(255,77,77,0.35);background:rgba(255,77,77,0.08);">
                        <div style="display:grid;gap:6px;">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @yield('dashboard-content')
            </section>
        </div>
    </main>
@endsection

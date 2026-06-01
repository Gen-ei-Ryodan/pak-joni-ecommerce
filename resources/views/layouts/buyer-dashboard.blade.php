@extends('layouts.buyer')

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

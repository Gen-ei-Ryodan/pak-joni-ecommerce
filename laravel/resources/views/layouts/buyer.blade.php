<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name'))</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <link rel="stylesheet" href="{{ asset('assets/css/reset.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/layout.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/button.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/card.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/homepage.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/product.css') }}">

        @stack('head')
    </head>
    <body>
        <div class="page">
            <header class="navbar">
                <div class="container navbar-inner">
                    <a class="brand" href="{{ url('/') }}">
                        <span>{{ config('app.name') }}</span>
                    </a>

                    <nav class="nav-links">
                        <a href="{{ route('buyer.home') }}">Home</a>
                        <a href="{{ route('buyer.about') }}">About</a>
                        <a href="{{ route('buyer.products') }}">Products</a>
                    </nav>

                    <div class="nav-cta">
                        @auth
                            @php($cartCount = auth()->user()->cart?->items()->count() ?? 0)
                            <a class="nav-cart-icon" href="{{ route('buyer.cart.index') }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                </svg>
                                @if($cartCount > 0)
                                    <span class="cart-badge">{{ $cartCount }}</span>
                                @endif
                            </a>
                            <div class="user-dropdown-wrap">
                                <a class="user-dropdown-trigger" href="{{ url('/dashboard') }}">{{ auth()->user()->name }}</a>
                                <div class="user-dropdown-menu">
                                    <a href="{{ route('buyer.wishlist.index') }}">Wishlist</a>
                                    <a href="{{ route('buyer.orders.index') }}">My Orders</a>
                                    <form method="post" action="{{ url('/logout') }}">
                                        @csrf
                                        <button type="submit">Logout</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a class="btn-login" href="{{ route('auth.login') }}">Login</a>
                        @endauth
                    </div>
                </div>
            </header>

            <main>
                @yield('content')
            </main>

            <footer class="section">
                <div class="container muted" style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                    <div>© {{ now()->year }} {{ config('app.name') }}</div>
                    <div style="display:flex;gap:14px;">
                        <a href="{{ url('/privacy') }}">Privacy</a>
                        <a href="{{ url('/terms') }}">Terms</a>
                    </div>
                </div>
            </footer>
        </div>

        <script src="{{ asset('assets/js/app.js') }}" defer></script>
        @stack('scripts')
    </body>
</html>

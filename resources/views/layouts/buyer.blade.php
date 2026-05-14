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
                            <a class="btn-logout cart-link" href="{{ route('buyer.cart.index') }}">Cart
                                @if($cartCount > 0)
                                    <span class="cart-badge">{{ $cartCount }}</span>
                                @endif
                            </a>
                            <a class="btn-logout" href="{{ route('buyer.wishlist.index') }}">Wishlist</a>
                            <a class="btn-logout" href="{{ url('/dashboard') }}">Dashboard</a>
                            <form method="post" action="{{ url('/logout') }}" style="display:inline;">
                                @csrf
                                <button class="btn-logout" type="submit">Logout</button>
                            </form>
                        @else
                            <a class="btn-login" href="{{ route('auth.login') }}">Login</a>
                        @endauth
                    </div>
                </div>
            </header>

            <main>
                @if (session('status'))
                    <div class="section" style="padding-bottom:0;">
                        <div class="container">
                            <div class="panel" style="padding:10px 12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                                {{ session('status') }}
                            </div>
                        </div>
                    </div>
                @endif

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

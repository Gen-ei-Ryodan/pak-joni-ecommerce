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
                        <a href="{{ route('buyer.products') }}">Produk</a>
                    </nav>

                    <div class="nav-cta">
                        @auth
                            <a class="btn" href="{{ route('buyer.cart.index') }}">Cart</a>
                            <a class="btn" href="{{ route('buyer.wishlist.index') }}">Wishlist</a>
                            <a class="btn" href="{{ url('/dashboard') }}">Dashboard</a>
                            <form method="post" action="{{ url('/logout') }}">
                                @csrf
                                <button class="btn btn-danger" type="submit">Logout</button>
                            </form>
                        @else
                            @if (Route::has('auth.login'))
                                <a class="btn" href="{{ route('auth.login') }}">Login</a>
                            @endif
                            @if (Route::has('auth.register'))
                                <a class="btn btn-primary" href="{{ route('auth.register') }}">Register</a>
                            @endif
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

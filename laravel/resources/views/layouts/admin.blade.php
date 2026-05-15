<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin')</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <link rel="stylesheet" href="{{ asset('assets/css/reset.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/layout.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/button.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    </head>
    <body>
        <div class="page">
            <header class="navbar">
                <div class="container navbar-inner">
                    <a class="brand" href="{{ url('/admin') }}">
                        <span>Admin</span>
                    </a>

                    <div class="nav-cta">
                        <a class="btn" href="{{ url('/') }}">Buyer</a>
                        <form method="post" action="{{ url('/logout') }}">
                            @csrf
                            <button class="btn btn-danger" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="dash">
                <div class="container dash-grid">
                    <aside class="sidebar">
                        <a href="{{ url('/admin') }}">Dashboard</a>
                        <div style="height:8px;"></div>
                        <a href="{{ route('admin.motors.index') }}">Motors</a>
                        <a href="{{ route('admin.part-categories.index') }}">Part Categories</a>
                        <a href="{{ route('admin.parts.index') }}">Parts</a>
                        <a href="{{ route('admin.banners.index') }}">Banners</a>
                        <a href="{{ route('admin.orders.index') }}">Orders</a>
                    </aside>
                    <section class="content">
                        @yield('content')
                    </section>
                </div>
            </main>
        </div>

        @stack('scripts')
    </body>
</html>

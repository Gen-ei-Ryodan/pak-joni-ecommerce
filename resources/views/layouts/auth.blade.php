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
        <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">

        @stack('head')
    </head>
    <body>
        <div class="page">
            <header class="navbar">
                <div class="container navbar-inner">
                    <a class="brand" href="{{ url('/') }}">
                        <span>{{ config('app.name') }}</span>
                    </a>

                    <div class="nav-cta">
                        @if (Route::has('auth.login'))
                            <a class="btn" href="{{ route('auth.login') }}">Login</a>
                        @endif
                        @if (Route::has('auth.register'))
                            <a class="btn btn-primary" href="{{ route('auth.register') }}">Register</a>
                        @endif
                    </div>
                </div>
            </header>

            @yield('content')
        </div>
    </body>
</html>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
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

                    <nav class="nav-links">
                        <a href="{{ route('buyer.home') }}">Beranda</a>
                        <a href="{{ route('buyer.products') }}">Produk</a>
                    </nav>

                    <div class="nav-cta">
                        <a class="btn-login" href="{{ route('auth.login') }}">Login</a>
                    </div>
                </div>
            </header>

            @yield('content')
        </div>

        <script>
        document.querySelectorAll('.password-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-toggle');
                var input = document.getElementById(targetId);
                var eyeOpen = this.querySelector('.eye-open');
                var eyeClosed = this.querySelector('.eye-closed');
                if (input.type === 'password') {
                    input.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'block';
                } else {
                    input.type = 'password';
                    eyeOpen.style.display = 'block';
                    eyeClosed.style.display = 'none';
                }
            });
        });
        </script>
    </body>
</html>

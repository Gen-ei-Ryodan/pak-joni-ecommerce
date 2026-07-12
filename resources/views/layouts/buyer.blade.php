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
        <link rel="stylesheet" href="{{ asset('assets/css/card.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/homepage.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/product.css') }}">
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


                    <button class="mobile-menu-toggle" aria-label="Toggle menu" data-mobile-toggle>
                        <span></span><span></span><span></span>
                    </button>

                    <nav class="nav-links" data-nav-menu>
                        <a href="{{ route('buyer.home') }}">Beranda</a>

                        @php
                            use App\Models\CategoryType;
                            $categoryTypes = CategoryType::where('is_active', true)->orderBy('sort_order')->get();
                        @endphp
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" data-dropdown-toggle="produk">Produk <span class="dd-arrow">&#9662;</span></button>
                            <div class="nav-dropdown-menu" data-dropdown-menu="produk">
                                <a href="{{ route('buyer.products') }}">Semua Produk</a>
                                @foreach($categoryTypes as $ct)
                                    <a href="{{ route('buyer.products', ['type' => $ct->slug]) }}">{{ $ct->name }}</a>
                                @endforeach
                                <hr style="border-color:var(--line);margin:4px 0;">
                                <a href="{{ route('buyer.price-list') }}">Daftar Harga</a>
                                <a href="{{ route('buyer.part-catalog') }}">Part Katalog</a>
                            </div>
                        </div>

                        {{-- Diler hidden sementara --}}
                        {{-- <a href="{{ route('buyer.dealer') }}">Diler</a> --}}

                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" data-dropdown-toggle="beritaacara">Berita dan Acara <span class="dd-arrow">&#9662;</span></button>
                            <div class="nav-dropdown-menu" data-dropdown-menu="beritaacara">
                                <a href="{{ route('buyer.news.index') }}">Berita</a>
                                <a href="{{ route('buyer.events.index') }}">Acara</a>
                                <a href="{{ route('buyer.csr.index') }}">Tanggung Jawab Sosial Perusahaan</a>
                            </div>
                        </div>

                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" data-dropdown-toggle="lainnya">Lainnya <span class="dd-arrow">&#9662;</span></button>
                            <div class="nav-dropdown-menu" data-dropdown-menu="lainnya">
                                <a href="{{ route('buyer.about') }}">Tentang Kami</a>
                                <a href="{{ route('buyer.showroom') }}">Showroom</a>
                                <a href="{{ route('buyer.careers.index') }}">Karir</a>
                                <a href="{{ route('buyer.internal-activities.index') }}">Kegiatan Internal</a>
                            </div>
                        </div>
                    </nav>

                    <div class="nav-cta">
                        <button class="nav-icon-btn" aria-label="Search" data-search-toggle>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </button>

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
                        @endauth
                    </div>
                </div>

                <div class="search-overlay" data-search-overlay style="display:none;">
                    <div class="container">
                        <form class="search-form" action="{{ route('buyer.search') }}" method="get">
                            <input type="text" name="q" placeholder="Cari motor, sparepart, SKU..." class="search-input" autocomplete="off">
                            <button type="submit" class="search-submit">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </button>
                            <button type="button" class="search-close" data-search-close>&#10005;</button>
                        </form>
                    </div>
                </div>
            </header>

            <main>
                @yield('content')
            </main>

            <footer class="site-footer">
                <div class="container">
                    <div class="footer-grid">
                        <div class="footer-col footer-brand">
                            <div class="footer-logo">{{ config('app.name') }}</div>
                            <p class="footer-desc">Dealer resmi motor WMOTO, SM SPORT, CFMOTO, ZONTES, dan ZEEHO. Menyediakan motor berkualitas, suku cadang asli, dan layanan purna jual terbaik di Indonesia.</p>
                            <div class="footer-social">
                                <a href="{{ config('app.social.facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                                <a href="{{ config('app.social.instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5"/></svg></a>
                                <a href="mailto:{{ config('app.social.email') }}" aria-label="Email"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></a>
                                <a href="https://wa.me/{{ config('app.social.whatsapp_link') }}" target="_blank" rel="noopener" aria-label="WhatsApp"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>
                            </div>
                        </div>

                        <div class="footer-col">
                            <h4 class="footer-heading">Link Cepat</h4>
                            <ul class="footer-links">
                                <li><a href="{{ route('buyer.home') }}">Beranda</a></li>
                                <li><a href="{{ route('buyer.products') }}">Produk</a></li>
                                <li><a href="{{ route('buyer.price-list') }}">Daftar Harga</a></li>
                                <li><a href="{{ route('buyer.part-catalog') }}">Part Katalog</a></li>
                                <li><a href="{{ route('buyer.showroom') }}">Showroom</a></li>
                            </ul>
                        </div>

                        <div class="footer-col">
                            <h4 class="footer-heading">Perusahaan</h4>
                            <ul class="footer-links">
                                <li><a href="{{ route('buyer.about') }}">Tentang Kami</a></li>
                                <li><a href="{{ route('buyer.news.index') }}">Berita</a></li>
                                <li><a href="{{ route('buyer.events.index') }}">Acara</a></li>
                                <li><a href="{{ route('buyer.careers.index') }}">Karir</a></li>
                                <li><a href="{{ route('buyer.csr.index') }}">CSR</a></li>
                            </ul>
                        </div>

                        <div class="footer-col">
                            <h4 class="footer-heading">Kontak</h4>
                            <ul class="footer-links footer-contact">
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span>Jl Kapasari No 73 Surabaya</span>
                                </li>
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span>{{ config('app.social.whatsapp') }}</span>
                                </li>
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <span>{{ config('app.social.email') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="footer-bottom">
                        <div>&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</div>
                        <div class="footer-bottom-links">
                            <a href="#">Privacy Policy</a>
                            <a href="#">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <script src="{{ asset('assets/js/app.js') }}" defer></script>
        <script>
        function showAuthConfirm(e) {
            if (e && e.preventDefault) e.preventDefault();
            var overlay = document.createElement('div');
            overlay.className = 'auth-confirm-overlay';
            overlay.innerHTML = '<div class="auth-confirm-modal">' +
                '<h3>Selamat Datang di {{ config('app.name') }}</h3>' +
                '<p>Apakah Anda sudah memiliki akun?</p>' +
                '<div class="auth-confirm-buttons">' +
                    '<a href="{{ route('auth.login') }}" class="btn btn-primary">Ya, saya sudah memiliki akun</a>' +
                    '<button class="btn btn-outline" onclick="dismissGuestPopup(event)">Saya pengunjung baru</button>' +
                '</div>' +
            '</div>';
            // Popup hanya bisa ditutup lewat tombol, tidak dengan klik di luar
            document.body.appendChild(overlay);
        }

        function dismissGuestPopup(e) {
            if (e && e.preventDefault) e.preventDefault();
            var overlay = document.querySelector('.auth-confirm-overlay');
            if (overlay) overlay.remove();
        }

        // Password show/hide toggle
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
        @stack('scripts')
    </body>
</html>

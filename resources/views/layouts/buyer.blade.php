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

                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" data-dropdown-toggle="produk">Produk <span class="dd-arrow">&#9662;</span></button>
                            <div class="nav-dropdown-menu" data-dropdown-menu="produk">
                                <a href="{{ route('buyer.products') }}">Semua Produk</a>
                                <a href="{{ route('buyer.products', ['type' => 'motor']) }}">Motor</a>
                                <a href="{{ route('buyer.products', ['type' => 'sparepart']) }}">Sparepart</a>
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
                        @else
                            <a class="btn-login" href="{{ route('auth.login') }}">Login</a>
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
                                <a href="#" aria-label="Facebook"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                                <a href="#" aria-label="Instagram"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5"/></svg></a>
                                <a href="#" aria-label="YouTube"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29.94 29.94 0 0 0 1 11.75a29.94 29.94 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29.94 29.94 0 0 0 .46-5.25 29.94 29.94 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg></a>
                                <a href="#" aria-label="TikTok"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg></a>
                            </div>
                        </div>

                        <div class="footer-col">
                            <h4 class="footer-heading">Link Cepat</h4>
                            <ul class="footer-links">
                                <li><a href="{{ route('buyer.home') }}">Beranda</a></li>
                                <li><a href="{{ route('buyer.products') }}">Produk</a></li>
                                <li><a href="{{ route('buyer.price-list') }}">Daftar Harga</a></li>
                                <li><a href="{{ route('buyer.part-catalog') }}">Part Katalog</a></li>
                                {{-- Diler hidden sementara --}}
                                {{-- <li><a href="{{ route('buyer.dealer') }}">Diler</a></li> --}}
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
                                    <span>Jl. Raya Motor No. 123, Jakarta Pusat, Indonesia</span>
                                </li>
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span>+62 21 1234 5678</span>
                                </li>
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <span>info@jomoto.co.id</span>
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
        @stack('scripts')
    </body>
</html>

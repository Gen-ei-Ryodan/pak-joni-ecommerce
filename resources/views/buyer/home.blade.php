@extends('layouts.buyer')

@section('title', 'Home')

@section('content')
    {{-- HERO VIDEO - Full screen 100vh --}}
    @if (!empty($heroVideo))
        <section class="hero-video-section">
            <video class="hero-video" autoplay muted loop playsinline>
                <source src="{{ asset('storage/' . $heroVideo->video_path) }}" type="video/mp4">
            </video>
            @if($heroVideo->title)
                <div class="hero-video-overlay">
                    <h1 class="hero-video-title">{{ $heroVideo->title }}</h1>
                </div>
            @endif
        </section>
    @endif

    {{-- HERO BANNER - Full screen 100vh, photo full layar --}}
    @if (!empty($heroBanners) && $heroBanners->count())
        <section class="banner-slider-hero" data-hero-carousel>
            <div class="carousel-container-hero" data-hero-track>
                @foreach ($heroBanners as $index => $banner)
                    <div class="carousel-slide-hero {{ $index === 0 ? 'active' : '' }}" data-hero-slide>
                        <div class="hero-bg-img" style="background-image: url('{{ image_url($banner->image_path) }}');"></div>
                    </div>
                @endforeach
            </div>
            @if($heroBanners->count() > 1)
                <button class="carousel-arrow carousel-arrow-prev" type="button" data-hero-prev aria-label="Previous">&#10094;</button>
                <button class="carousel-arrow carousel-arrow-next" type="button" data-hero-next aria-label="Next">&#10095;</button>
                <div class="carousel-dots" data-hero-dots>
                    @foreach ($heroBanners as $index => $b)
                        <button type="button" data-hero-dot="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- PROMO SECTION - 100vh, auto proporsional --}}
    @if (!empty($promoBanners) && $promoBanners->count())
        <section class="promo-section overlap-section z3">
            <div class="container">
                <div class="section-header center dark-text">
                    <div class="reveal">
                        <div class="section-title">Penawaran Terbaik</div>
                        <h2 class="section-title-text">Promo Spesial</h2>
                        <div class="section-line center-line"></div>
                    </div>
                </div>
                <div class="promo-grid {{ $promoBanners->count() === 1 ? 'single' : 'multiple' }}">
                    @foreach ($promoBanners as $banner)
                        <a class="promo-card reveal reveal-delay-{{ $loop->index + 1 }}" href="{{ $banner->link_url ?: '#' }}" style="background-image:url('{{ image_url($banner->image_path) }}');">
                            <div class="promo-card-body">
                                <h3 class="promo-card-title">{{ $banner->title }}</h3>
                                @if($banner->subtitle)
                                    <p class="promo-card-subtitle">{{ $banner->subtitle }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- LAUNCHING PRODUK + BERITA INFORMASI - Combined 100vh --}}
    @if ((!empty($launchingBanners) && $launchingBanners->count()) || (!empty($latestNews) && $latestNews->count()))
        <section class="launch-news-section overlap-section z4">
            <div class="container">
                <div class="section-header center dark-text">
                    <div class="reveal">
                        <div class="section-title">Update Terkini</div>
                        <h2 class="section-title-text">Launching Produk & Berita</h2>
                        <div class="section-line center-line"></div>
                    </div>
                </div>
                <div class="launch-news-grid">
                    {{-- Kolom Launching --}}
                    <div class="launch-col reveal reveal-delay-1">
                        @if (!empty($launchingBanners) && $launchingBanners->count())
                            @php $lb = $launchingBanners->first(); @endphp
                            <div class="launch-card-full" style="background-image:url('{{ image_url($lb->image_path) }}');">
                                <div class="launch-card-body">
                                    @if($lb->subtitle)
                                        <div style="color:#FFD400;font-size:13px;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;font-weight:600;">{{ $lb->subtitle }}</div>
                                    @endif
                                    <h3 style="font-size:clamp(22px,3vw,32px);font-weight:700;color:#fff;margin-bottom:16px;">{{ $lb->title }}</h3>
                                    @if($lb->button_text && $lb->link_url)
                                        <a href="{{ $lb->link_url }}" class="btn-accent" style="font-size:12px;padding:10px 24px;">{{ $lb->button_text }}</a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Kolom Berita --}}
                    <div class="news-col reveal reveal-delay-2">
                        @if (!empty($latestNews) && $latestNews->count())
                            @foreach ($latestNews->take(4) as $item)
                                <a class="news-card" href="{{ $item->external_url ?: route('buyer.news.show', $item->slug) }}" {{ $item->external_url ? 'target="_blank" rel="noopener"' : '' }}>
                                    <div class="news-card-thumb" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');"></div>
                                    <div class="news-card-info">
                                        <div class="news-card-date">{{ $item->publish_date?->format('d M Y') }}</div>
                                        <div class="news-card-title">{{ $item->title }}</div>
                                        <div class="news-card-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($item->content), 80) }}</div>
                                    </div>
                                </a>
                            @endforeach
                            <a href="{{ route('buyer.news.index') }}" class="btn-outline-white" style="align-self:flex-start;margin-top:8px;">Lihat Semua Berita</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- DEALER RESMI + KEUNGGULAN - Top to bottom, 200vh --}}
    @if ((!empty($brands) && $brands->count()) || (!empty($whyChooseUs) && $whyChooseUs->count()))
        <section class="brand-why-section overlap-section z5">
            <div class="container">
                {{-- Big heading: Dealer Resmi untuk Merk --}}
                <div class="reveal">
                    <h2 class="dealer-heading">Dealer Resmi untuk Merk</h2>
                </div>

                {{-- Brand logos --}}
                @if (!empty($brands) && $brands->count())
                    <div class="brand-section reveal reveal-delay-2">
                        <div class="brand-items">
                            @foreach ($brands as $brand)
                                <a href="{{ route('buyer.category-brand', ['categoryType' => 'motor', 'brand' => $brand->slug]) }}" class="brand-item-new">
                                    @if($brand->logo_path)
                                        <img src="{{ image_url($brand->logo_path) }}" alt="{{ $brand->name }}" class="brand-logo-img-new">
                                    @else
                                        <span style="font-size:14px;font-weight:700;color:#0055DA;">{{ $brand->name }}</span>
                                    @endif
                                    <span class="brand-name-new">{{ $brand->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Keunggulan heading --}}
                <div class="reveal reveal-delay-3">
                    <h3 class="keunggulan-heading">Keunggulan Kami</h3>
                </div>

                {{-- Why Choose Us cards --}}
                @if (!empty($whyChooseUs) && $whyChooseUs->count())
                    <div class="why-section reveal reveal-delay-4">
                        <div class="why-items">
                            @foreach ($whyChooseUs as $item)
                                <div class="why-card-new">
                                    <div class="why-icon-new">
                                        @if($item->icon_image)
                                            <img src="{{ asset('storage/' . $item->icon_image) }}" alt="{{ $item->title }}">
                                        @endif
                                    </div>
                                    <h4 class="why-title-new">{{ $item->title }}</h4>
                                    <p class="why-desc-new">{{ $item->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- HUBUNGI KAMI + SIMULASI KREDIT - 2 Kolom --}}
    <section class="contact-credit-section overlap-section" style="z-index:6;">
        <div class="container">
            <div class="section-header center dark-text">
                <div class="reveal">
                    <div class="section-title">Layanan Kami</div>
                    <h2 class="section-title-text">Hubungi Kami & Simulasi Kredit</h2>
                    <div class="section-line center-line"></div>
                </div>
            </div>
            <div class="contact-credit-grid">
                {{-- Kolom Kiri: Hubungi Kami --}}
                <div class="contact-col reveal reveal-delay-1">
                    <h3 style="font-size:16px;font-weight:600;color:#fff;">Hubungi Kami</h3>
                    <p style="font-size:13px;color:rgba(255,255,255,0.7);margin-top:6px;line-height:1.6;">Konsultasi dan penawaran terbaik untuk produk pilihan Anda.</p>

                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon-circle">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                            </div>
                            <div class="contact-item-info">
                                <h4>WhatsApp</h4>
                                <a href="https://wa.me/{{ config('app.social.whatsapp_link') }}?text=Halo%20{{ urlencode(config('app.name')) }}%2C%20saya%20ingin%20konsultasi%20dan%20penawaran." target="_blank" rel="noopener">{{ config('app.social.whatsapp') }}</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon-circle">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div class="contact-item-info">
                                <h4>Email</h4>
                                <a href="mailto:{{ config('app.social.email') }}">{{ config('app.social.email') }}</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon-circle">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div class="contact-item-info">
                                <h4>Alamat Dealer</h4>
                                <span>Jl Kapasari No 73 Surabaya</span>
                            </div>
                        </div>
                    </div>

                    <div class="contact-qr">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(url('/')) }}" alt="QR Code" loading="lazy">
                        <p style="font-size:11px;color:var(--muted);margin-top:8px;">Scan QR untuk mengunjungi website kami</p>
                    </div>
                </div>

                {{-- Kolom Kanan: Simulasi Kredit --}}
                <div class="credit-col reveal reveal-delay-2">
                    <h3 style="font-size:16px;font-weight:600;color:#fff;">Simulasi Kredit Motor</h3>
                    <p style="font-size:13px;color:rgba(255,255,255,0.7);margin-top:6px;line-height:1.6;">Hitung perkiraan cicilan kredit motor impian Anda.</p>

                    <div class="credit-form" id="creditSimulation">
                        <div class="credit-form-group">
                            <label class="credit-form-label">Harga Motor (Rp)</label>
                            <input type="number" class="credit-form-input" id="creditPrice" placeholder="Contoh: 30000000" value="30000000" min="0">
                        </div>
                        <div class="credit-form-group">
                            <label class="credit-form-label">Uang Muka / DP (Rp)</label>
                            <input type="number" class="credit-form-input" id="creditDp" placeholder="Contoh: 5000000" value="5000000" min="0">
                        </div>
                        <div class="credit-form-group">
                            <label class="credit-form-label">Tenor / Jangka Waktu</label>
                            <select class="credit-form-select" id="creditTenor">
                                <option value="12">12 Bulan (1 Tahun)</option>
                                <option value="24" selected>24 Bulan (2 Tahun)</option>
                                <option value="36">36 Bulan (3 Tahun)</option>
                                <option value="48">48 Bulan (4 Tahun)</option>
                            </select>
                        </div>
                        <div class="credit-form-group">
                            <label class="credit-form-label">Bunga per Tahun (%)</label>
                            <input type="number" class="credit-form-input" id="creditBunga" placeholder="Contoh: 8" value="8" min="0" max="50" step="0.1">
                        </div>
                        <button type="button" class="btn-accent btn-full" onclick="hitungKredit()">Hitung Simulasi</button>

                        <div class="credit-result" id="creditResult" style="display:none;">
                            <div class="credit-result-row">
                                <span class="credit-result-label">Total Pinjaman</span>
                                <span id="resultPinjaman">-</span>
                            </div>
                            <div class="credit-result-row">
                                <span class="credit-result-label">Bunga Total</span>
                                <span id="resultBunga">-</span>
                            </div>
                            <div class="credit-result-row">
                                <span class="credit-result-label">Total Pembayaran</span>
                                <span id="resultTotal">-</span>
                            </div>
                            <div class="credit-result-row">
                                <span class="credit-result-label">Cicilan per Bulan</span>
                                <span id="resultCicilan">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('head')
    <style>
        .hero-video-section {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
            background: #000;
        }
        .hero-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .hero-video-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.3);
        }
        .hero-video-title {
            color: #fff;
            font-size: clamp(24px, 5vw, 56px);
            font-weight: 700;
            text-align: center;
            text-shadow: 0 4px 20px rgba(0,0,0,0.5);
            padding: 0 20px;
        }
        .auth-confirm-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-confirm-modal {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .auth-confirm-modal h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
        }
        .auth-confirm-modal p {
            color: #666;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .auth-confirm-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .auth-confirm-buttons .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background: #FF0052;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .auth-confirm-buttons .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 0, 82, 0.3);
        }
        .auth-confirm-buttons .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            background: transparent;
            border: 2px solid #ddd;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .auth-confirm-buttons .btn-outline:hover {
            border-color: #FF0052;
            color: #FF0052;
        }
    </style>
@endpush

@push('scripts')
    <script>
        @guest
        setTimeout(function() { showAuthConfirm(null); }, 500);
        @endguest

        // Hero Carousel
        (function () {
            const carousel = document.querySelector('[data-hero-carousel]');
            if (!carousel) return;

            const slides = Array.from(carousel.querySelectorAll('[data-hero-slide]'));
            const dots = Array.from(carousel.querySelectorAll('[data-hero-dot]'));
            const prevBtn = carousel.querySelector('[data-hero-prev]');
            const nextBtn = carousel.querySelector('[data-hero-next]');
            if (slides.length < 2) return;

            let current = 0;
            let timer = null;

            function show(idx) {
                idx = ((idx % slides.length) + slides.length) % slides.length;
                slides.forEach((s, i) => { s.classList.toggle('active', i === idx); });
                dots.forEach((d, i) => { d.classList.toggle('active', i === idx); });
                current = idx;
            }

            function startAuto() {
                stopAuto();
                timer = setInterval(() => show(current + 1), 5000);
            }

            function stopAuto() {
                if (timer) { clearInterval(timer); timer = null; }
            }

            if (prevBtn) prevBtn.addEventListener('click', () => { show(current - 1); startAuto(); });
            if (nextBtn) nextBtn.addEventListener('click', () => { show(current + 1); startAuto(); });
            dots.forEach(d => {
                d.addEventListener('click', () => { show(parseInt(d.dataset.heroDot)); startAuto(); });
            });

            carousel.addEventListener('mouseenter', stopAuto);
            carousel.addEventListener('mouseleave', startAuto);

            startAuto();
        })();

        // Credit Simulation
        function hitungKredit() {
            const price = parseFloat(document.getElementById('creditPrice').value) || 0;
            const dp = parseFloat(document.getElementById('creditDp').value) || 0;
            const tenor = parseInt(document.getElementById('creditTenor').value) || 12;
            const bunga = parseFloat(document.getElementById('creditBunga').value) || 0;

            const pinjaman = Math.max(0, price - dp);
            const totalBunga = pinjaman * (bunga / 100) * (tenor / 12);
            const totalBayar = pinjaman + totalBunga;
            const cicilan = tenor > 0 ? totalBayar / tenor : 0;

            const fmt = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');

            document.getElementById('resultPinjaman').textContent = fmt(pinjaman);
            document.getElementById('resultBunga').textContent = fmt(totalBunga);
            document.getElementById('resultTotal').textContent = fmt(totalBayar);
            document.getElementById('resultCicilan').textContent = fmt(cicilan);
            document.getElementById('creditResult').style.display = 'block';
        }

        // Delivery option selector
        function selectDelivery(el, type) {
            document.querySelectorAll('.delivery-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
        }

        // Init credit calc on load
        document.addEventListener('DOMContentLoaded', function() {
            hitungKredit();
        });

        // Scroll Reveal via IntersectionObserver
        (function() {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.05 });
            document.querySelectorAll('.reveal').forEach(function(el) {
                var rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    el.classList.add('visible');
                } else {
                    observer.observe(el);
                }
            });
        })();
    </script>
@endpush

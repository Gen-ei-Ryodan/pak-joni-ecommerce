@extends('layouts.buyer')

@section('title', 'Home')

@section('content')
    @if (!empty($heroBanners) && $heroBanners->count())
        <section class="banner-slider" data-carousel>
            <div class="carousel-container" data-carousel-track>
                @foreach ($heroBanners as $index => $banner)
                    <div class="carousel-slide" data-carousel-slide style="{{ $index === 0 ? '' : 'display:none;' }}">
                        <div class="banner-slide" style="background-image: url('{{ image_url($banner->image_path) }}'); background-size: cover; background-position: center; min-height: 75vh;">
                            <div class="banner-overlay">
                                <div class="container banner-content-inner">
                                    @if($banner->subtitle)
                                        <div class="banner-label">{{ $banner->subtitle }}</div>
                                    @endif
                                    <h2 class="banner-heading">{{ $banner->title }}</h2>
                                    @if($banner->button_text && $banner->link_url)
                                        <a href="{{ $banner->link_url }}" class="btn btn-accent">{{ $banner->button_text }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($heroBanners->count() > 1)
                <button class="carousel-arrow carousel-arrow-prev" type="button" data-carousel-prev aria-label="Previous">&#10094;</button>
                <button class="carousel-arrow carousel-arrow-next" type="button" data-carousel-next aria-label="Next">&#10095;</button>
                <div class="carousel-dots" data-carousel-dots>
                    @foreach ($heroBanners as $index => $b)
                        <button type="button" data-carousel-dot="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if (!empty($promoBanners) && $promoBanners->count())
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Promo Spesial</h2>
                    <div class="section-line"></div>
                </div>
                <div class="grid grid-3">
                    @foreach ($promoBanners as $banner)
                        <a class="card card-banner" href="{{ $banner->link_url ?: '#' }}">
                            <div class="card-media" style="background-image:url('{{ image_url($banner->image_path) }}');background-size:cover;background-position:center;"></div>
                            <div class="card-body">
                                <div class="card-title">{{ $banner->title }}</div>
                                @if($banner->subtitle)
                                    <div class="card-meta">{{ $banner->subtitle }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (!empty($launchingBanners) && $launchingBanners->count())
        <section class="section section-dark">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Launching Produk</h2>
                    <div class="section-line"></div>
                </div>
                <div class="carousel" data-interval="4000">
                    @foreach ($launchingBanners as $index => $banner)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="launch-card" style="background-image:linear-gradient(135deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3)),url('{{ image_url($banner->image_path) }}'); background-size:cover; background-position:center; min-height:400px; border-radius:var(--radius); display:flex; align-items:center; justify-content:center; text-align:center; padding:40px;">
                                <div>
                                    @if($banner->subtitle)
                                        <div style="color:#f0d68a;font-size:14px;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;font-weight:600;">{{ $banner->subtitle }}</div>
                                    @endif
                                    <h3 style="font-size:clamp(24px,4vw,40px);font-weight:700;color:#fff;">{{ $banner->title }}</h3>
                                    @if($banner->button_text && $banner->link_url)
                                        <a href="{{ $banner->link_url }}" class="btn btn-accent" style="margin-top:20px;">{{ $banner->button_text }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($launchingBanners->count() > 1)
                        <button class="carousel-control-prev" type="button">&#10094;</button>
                        <button class="carousel-control-next" type="button">&#10095;</button>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if (!empty($kegiatanBanners) && $kegiatanBanners->count())
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Kegiatan Perusahaan</h2>
                    <div class="section-line"></div>
                </div>
                <div class="carousel" data-interval="4000">
                    @foreach ($kegiatanBanners as $index => $banner)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="kegiatan-card" style="background-image:linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.3)),url('{{ image_url($banner->image_path) }}');background-size:cover;background-position:center;min-height:350px;border-radius:var(--radius);display:flex;align-items:flex-end;padding:40px;">
                                <div>
                                    <h3 style="font-size:28px;font-weight:700;color:#fff;">{{ $banner->title }}</h3>
                                    @if($banner->subtitle)
                                        <p style="color:rgba(255,255,255,0.7);margin-top:8px;">{{ $banner->subtitle }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($kegiatanBanners->count() > 1)
                        <button class="carousel-control-prev" type="button">&#10094;</button>
                        <button class="carousel-control-next" type="button">&#10095;</button>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if (!empty($latestNews) && $latestNews->count())
        <section class="section section-dark">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Berita dan Informasi</h2>
                    <a href="{{ route('buyer.news.index') }}" class="btn btn-outline">Lihat Semua</a>
                </div>
                <div class="grid grid-4">
                    @foreach ($latestNews as $item)
                        <a class="card" href="{{ route('buyer.news.show', $item->slug) }}">
                            <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:180px;"></div>
                            <div class="card-body">
                                <div class="card-meta">{{ $item->publish_date?->format('d M Y') }}</div>
                                <div class="card-title" style="font-size:14px;">{{ $item->title }}</div>
                                <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit(strip_tags($item->content), 80) }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($highlight && $highlight->motor)
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Produk Unggulan</h2>
                    <div class="section-line"></div>
                </div>
                <div class="highlight-card">
                    <div class="highlight-image" style="background-image:url('{{ $highlight->motor->thumbnail_path ? image_url($highlight->motor->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                    <div class="highlight-body">
                        @if($highlight->motor->brand)
                            <div class="highlight-brand">{{ $highlight->motor->brand->name }}</div>
                        @endif
                        <h3 class="highlight-title">{{ $highlight->motor->name }}</h3>
                        @if($highlight->motor->price)
                            <div class="highlight-price">Rp {{ number_format($highlight->motor->price, 0, ',', '.') }}</div>
                        @endif
                        <p class="highlight-desc">{{ $highlight->motor->short_description }}</p>
                        <a href="{{ route('buyer.motors.show', $highlight->motor->slug) }}" class="btn btn-accent">Lihat Detail</a>
                        <a href="{{ route('buyer.motors.show', ['motor' => $highlight->motor->slug, 'tab' => 'parts']) }}" class="btn btn-outline" style="margin-left:8px;">Sparepart</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (!empty($brands) && $brands->count())
        <section class="section section-dark">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Brand Kami</h2>
                    <div class="section-line"></div>
                </div>
                <div class="brand-carousel">
                    @foreach ($brands as $brand)
                        <a href="{{ route('buyer.products', ['brand' => $brand->slug]) }}" class="brand-item">
                            @if($brand->logo_path)
                                <img src="{{ image_url($brand->logo_path) }}" alt="{{ $brand->name }}" class="brand-logo-img">
                            @else
                                <div class="brand-placeholder">{{ $brand->name }}</div>
                            @endif
                            <span class="brand-name">{{ $brand->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (!empty($whyChooseUs) && $whyChooseUs->count())
        <section class="section">
            <div class="container">
                <div class="section-header center">
                    <h2 class="section-title-text">Mengapa Memilih MOTOMART</h2>
                    <div class="section-line center-line"></div>
                </div>
                <div class="grid grid-3">
                    @foreach ($whyChooseUs as $item)
                        <div class="why-card">
                            <div class="why-icon">
                                @if($item->icon_image)
                                    <img src="{{ asset('storage/' . $item->icon_image) }}" alt="{{ $item->title }}" style="display:block;margin:0 auto;width:48px;height:48px;object-fit:contain;">
                                @endif
                            </div>
                            <h4 class="why-title">{{ $item->title }}</h4>
                            <p class="why-desc">{{ $item->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (!empty($latestEvents) && $latestEvents->count())
        <section class="section section-dark">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Event Terbaru</h2>
                    <a href="{{ route('buyer.events.index') }}" class="btn btn-outline">Lihat Semua</a>
                </div>
                <div class="grid grid-3">
                    @foreach ($latestEvents as $event)
                        <a class="card" href="{{ route('buyer.events.show', $event->slug) }}">
                            <div class="card-media" style="background-image:url('{{ $event->thumbnail_path ? image_url($event->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                            <div class="card-body">
                                <div class="card-meta">{{ $event->event_date?->format('d M Y') }} @if($event->location) &middot; {{ $event->location }} @endif</div>
                                <div class="card-title">{{ $event->title }}</div>
                                <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Hubungi Kami Untuk Penawaran</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:500px;margin:0 auto;">Tertarik dengan produk kami? Hubungi langsung via WhatsApp untuk konsultasi dan penawaran.</p>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;gap:16px;padding:20px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(url('/')) }}"
                     alt="WhatsApp QR Code" style="width:200px;height:200px;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,0.1);" loading="lazy">
                <a href="https://wa.me/{{ config('app.whatsapp_number', '6281234567890') }}?text=Halo%20MOTOMART%2C%20saya%20ingin%20konsultasi%20dan%20penawaran%20untuk%20produk%20di%20website%20Anda."
                   target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:6px;color:#25D366;font-size:13px;font-weight:500;text-decoration:none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Hubungi via WhatsApp
                </a>
            </div>
        </div>
    </section>

    @if (!empty($motors) && $motors->count())
        <section class="section section-dark">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title-text">Motor Terbaru</h2>
                    <a href="{{ route('buyer.products') }}" class="btn btn-outline">Lihat Semua</a>
                </div>
                <div class="grid grid-4">
                    @foreach ($motors as $m)
                        <div class="card motor-card">
                            <a class="card-media-link" href="{{ route('buyer.motors.show', $m->slug) }}" style="display:block;text-decoration:none;">
                                <div class="card-media" style="background-image:url('{{ $m->thumbnail_path ? image_url($m->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                            </a>
                            <div class="card-body">
                                @if($m->brand)
                                    <div class="card-meta">{{ $m->brand->name }}</div>
                                @endif
                                <a href="{{ route('buyer.motors.show', $m->slug) }}" style="text-decoration:none;color:inherit;">
                                    <div class="card-title">{{ $m->name }}</div>
                                </a>
                                @if($m->price)
                                    <div class="price">Rp {{ number_format($m->price, 0, ',', '.') }}</div>
                                @endif
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('buyer.motors.show', $m->slug) }}" class="card-action-btn primary">Lihat Motor</a>
                                <a href="{{ route('buyer.motors.show', ['motor' => $m->slug, 'tab' => 'parts']) }}" class="card-action-btn">Sparepart</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@push('head')
    <style>
        .motor-card { display: flex; flex-direction: column; overflow: hidden; }
        .card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-top: 1px solid var(--line);
        }
        .card-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            color: var(--muted);
            transition: all .2s;
            letter-spacing: 0.3px;
        }
        .card-action-btn.primary {
            color: var(--accent);
            border-right: 1px solid var(--line);
        }
        .card-action-btn:hover {
            background: rgba(217,180,111,0.08);
            color: var(--accent);
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const carousel = document.querySelector('[data-carousel]');
            if (!carousel) return;

            const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
            const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
            const prevBtn = carousel.querySelector('[data-carousel-prev]');
            const nextBtn = carousel.querySelector('[data-carousel-next]');
            if (slides.length < 2) return;

            let current = 0;
            let timer = null;

            function show(idx) {
                idx = ((idx % slides.length) + slides.length) % slides.length;
                slides.forEach((s, i) => { s.style.display = i === idx ? '' : 'none'; });
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
                d.addEventListener('click', () => { show(parseInt(d.dataset.carouselDot)); startAuto(); });
            });

            carousel.addEventListener('mouseenter', stopAuto);
            carousel.addEventListener('mouseleave', startAuto);

            show(0);
            startAuto();
        })();
    </script>
@endpush

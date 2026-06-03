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
                            <div class="why-icon">{!! $item->icon ?: '&#9733;' !!}</div>
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
                <h2 class="section-title-text">Request Penawaran</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:500px;margin:0 auto;">Tertarik dengan produk kami? Isi form di bawah dan tim kami akan segera menghubungi Anda.</p>
            </div>
            <form action="{{ route('buyer.quotation.store') }}" method="POST" class="quotation-form">
                @csrf
                @if(session('success'))
                    <div class="flash-success">{{ session('success') }}</div>
                @endif
                <div class="form-grid-2">
                    <div class="form-group">
                        <input type="text" name="name" class="form-input" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-input" placeholder="Email" required>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" name="phone" class="form-input" placeholder="Nomor HP" required>
                </div>
                <div class="form-group">
                    <textarea name="message" class="form-input" rows="4" placeholder="Pesan Anda" required></textarea>
                </div>
                <button type="submit" class="btn btn-accent btn-full">Kirim Permintaan</button>
            </form>
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
                        <a class="card" href="{{ route('buyer.motors.show', $m->slug) }}">
                            <div class="card-media" style="background-image:url('{{ $m->thumbnail_path ? image_url($m->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                            <div class="card-body">
                                @if($m->brand)
                                    <div class="card-meta">{{ $m->brand->name }}</div>
                                @endif
                                <div class="card-title">{{ $m->name }}</div>
                                @if($m->price)
                                    <div class="price">Rp {{ number_format($m->price, 0, ',', '.') }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

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

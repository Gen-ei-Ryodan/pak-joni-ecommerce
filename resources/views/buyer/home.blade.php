@extends('layouts.buyer')

@section('title', 'Home')

@section('content')
    @if (!empty($banners) && $banners->count())
        <section class="banner-slider" data-carousel>
            <div class="carousel-container" data-carousel-track>
                @foreach ($banners as $index => $banner)
                    <div class="carousel-slide" data-carousel-slide style="{{ $index === 0 ? '' : 'display:none;' }}">
                        <div class="banner-slide" style="background-image: url('{{ asset($banner->image_path) }}'); background-size: cover; background-position: center; min-height: 70vh;"></div>
                    </div>
                @endforeach
            </div>
            @if($banners->count() > 1)
                <button class="carousel-prev" type="button" data-carousel-prev aria-label="Previous">&#10094;</button>
                <button class="carousel-next" type="button" data-carousel-next aria-label="Next">&#10095;</button>
                <div class="carousel-dots" data-carousel-dots>
                    @foreach ($banners as $index => $b)
                        <button type="button" data-carousel-dot="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if (!empty($banners) && $banners->count())
        <section class="section py-5">
            <div class="container">
                <div class="section-title h3 mb-4">Highlights</div>
                <div class="grid grid-3">
                    @foreach ($banners->take(3) as $banner)
                        <a class="card" href="{{ $banner->link_url ?: route('buyer.products') }}">
                            <div class="card-media" style="background-image:url('{{ asset($banner->image_path) }}');background-size:cover;background-position:center;"></div>
                            <div class="card-body">
                                <div class="card-title">{{ $banner->title }}</div>
                                <div class="card-meta">Click for details.</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div class="section-title" style="font-size:18px;font-weight:600;">Products</div>
                <a class="btn" href="{{ route('buyer.products') }}">View All</a>
            </div>

            <div style="height:16px;"></div>

            <div class="section-title">Motor</div>
            <div class="grid grid-3">
                @forelse ($motors as $m)
                    <a class="card" href="{{ route('buyer.motors.show', $m->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $m->thumbnail_path ? asset($m->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                        <div class="card-body">
                            <div class="card-title">{{ $m->name }}</div>
                            <div class="card-meta">{{ $m->year ?? '' }}</div>
                        </div>
                    </a>
                @empty
                    <div class="muted">No motorcycles yet.</div>
                @endforelse
            </div>

            <div style="height:26px;"></div>

            <div class="section-title">Parts</div>
            <div class="grid grid-3">
                @forelse ($parts as $p)
                    <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $p->thumbnail_path ? asset($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                        <div class="card-body">
                            <div class="card-title">{{ $p->name }}</div>
                            <div class="card-meta">{{ $p->category?->group }} — {{ $p->category?->name }}</div>
                            <div class="price">
                                {{ $p->defaultVariant ? number_format((float) $p->defaultVariant->price, 2, '.', ',') : number_format((float) $p->base_price, 2, '.', ',') }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="muted">No parts yet.</div>
                @endforelse
            </div>
        </div>
    </section>
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

            let current = 0;
            let timer = null;

            function show(idx) {
                slides.forEach((s, i) => { s.style.display = i === idx ? '' : 'none'; });
                dots.forEach((d, i) => { d.classList.toggle('active', i === idx); });
                current = idx;
            }

            function next() { show((current + 1) % slides.length); }
            function prev() { show((current - 1 + slides.length) % slides.length); }

            function startAuto() {
                stopAuto();
                timer = setInterval(next, 5000);
            }

            function stopAuto() {
                if (timer) { clearInterval(timer); timer = null; }
            }

            if (prevBtn) prevBtn.addEventListener('click', () => { prev(); startAuto(); });
            if (nextBtn) nextBtn.addEventListener('click', () => { next(); startAuto(); });
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

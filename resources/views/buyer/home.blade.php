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

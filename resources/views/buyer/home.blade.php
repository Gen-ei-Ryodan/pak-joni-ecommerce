@extends('layouts.buyer')

@section('title', 'Home')

@section('content')
    @if (!empty($banners) && $banners->count())
        <section class="banner-slider">
            <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-inner">
                    @foreach ($banners as $index => $banner)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="banner-slide" style="background-image: url('{{ asset($banner->image_path) }}'); background-size: cover; background-position: center; height: 100vh;"></div>
                        </div>
                    @endforeach
                </div>
                @if($banners->count() > 1)
                    <button class="carousel-control-prev" type="button" data-target="#bannerCarousel" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true">&#10094;</span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-target="#bannerCarousel" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true">&#10095;</span>
                        <span class="visually-hidden">Next</span>
                    </button>
                @endif
            </div>
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

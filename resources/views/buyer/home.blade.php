@extends('layouts.buyer')

@section('title', 'Home')

@section('content')
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-content">
            <h1 class="hero-title">Premium Sparepart Untuk Motor Anda</h1>
            <p class="hero-subtitle">
                Motor sebagai visual catalog. Part sebagai produk utama penjualan. Full custom UI, cepat, dan responsif.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('buyer.products') }}">Lihat Produk</a>
                <a class="btn" href="{{ route('buyer.motors.index') }}">Catalog Motor</a>
            </div>
        </div>
    </section>

    @if (!empty($banners) && $banners->count())
        <section class="section">
            <div class="container">
                <div class="section-title">Highlights</div>
                <div class="grid grid-3">
                    @foreach ($banners->take(3) as $banner)
                        <a class="card" href="{{ $banner->link_url ?: route('buyer.products') }}">
                            <div class="card-media" style="background-image:url('{{ asset($banner->image_path) }}');background-size:cover;background-position:center;"></div>
                            <div class="card-body">
                                <div class="card-title">{{ $banner->title }}</div>
                                <div class="card-meta">Klik untuk lihat detail.</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

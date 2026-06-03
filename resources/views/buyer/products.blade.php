@extends('layouts.buyer')

@section('title', 'Produk')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Produk Kami</h2>
                <div class="section-line center-line"></div>
            </div>

            @if(!empty($brands) && $brands->count())
                <div class="brand-filter" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:24px;">
                    <a href="{{ route('buyer.products') }}" class="filter-tag {{ !$selectedBrand ? 'active' : '' }}">Semua</a>
                    @foreach($brands as $brand)
                        <a href="{{ route('buyer.products', ['brand' => $brand->slug]) }}" class="filter-tag {{ $selectedBrand === $brand->slug ? 'active' : '' }}">{{ $brand->name }}</a>
                    @endforeach
                </div>
            @endif

            @if(!empty($categories) && $categories->count())
                <div class="brand-filter" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:30px;">
                    <a href="{{ route('buyer.products', $selectedBrand ? ['brand' => $selectedBrand] : []) }}" class="filter-tag filter-tag-sm {{ !$selectedCategory ? 'active' : '' }}">Semua Kategori</a>
                    @foreach($categories as $cat)
                        @php
                            $params = $selectedBrand ? ['brand' => $selectedBrand, 'category' => $cat->slug] : ['category' => $cat->slug];
                        @endphp
                        <a href="{{ route('buyer.products', $params) }}" class="filter-tag filter-tag-sm {{ $selectedCategory === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-3">
                @forelse ($motors as $m)
                    <a class="card" href="{{ route('buyer.motors.show', $m->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $m->thumbnail_path ? image_url($m->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:220px;"></div>
                        <div class="card-body">
                            @if($m->brand)
                                <div class="card-meta">{{ $m->brand->name }}</div>
                            @endif
                            <div class="card-title">{{ $m->name }}</div>
                            @if($m->price)
                                <div class="price">Rp {{ number_format($m->price, 0, ',', '.') }}</div>
                            @endif
                            @if($m->short_description)
                                <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit($m->short_description, 80) }}</div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada produk tersedia.</div>
                @endforelse
            </div>

            <div style="margin-top:30px;">
                {{ $motors->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
@endsection

@push('head')
    <style>
        .filter-tag {
            display: inline-flex;
            padding: 8px 18px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.6px;
            text-decoration: none;
            color: var(--muted);
            border: 1px solid var(--line);
            border-radius: 20px;
            transition: all 0.2s ease;
        }
        .filter-tag:hover, .filter-tag.active {
            border-color: var(--accent);
            color: var(--accent);
        }
        .filter-tag.active {
            background: rgba(217, 180, 111, 0.1);
        }
        .filter-tag-sm {
            padding: 6px 14px;
            font-size: 11px;
        }
    </style>
@endpush

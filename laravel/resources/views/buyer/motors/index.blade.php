@extends('layouts.buyer')

@section('title', 'Motor')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title-text">Motor Catalog</h2>
                    <div class="section-line"></div>
                </div>
                <a class="btn btn-outline" href="{{ route('buyer.products') }}">Lihat Produk</a>
            </div>

            <form method="get" style="display:flex;gap:10px;margin-bottom:24px;">
                <input class="form-input" name="q" value="{{ $q }}" placeholder="Cari motor..." style="flex:1;">
                <button class="btn btn-accent" type="submit">Cari</button>
            </form>

            <div class="grid grid-3">
                @forelse ($motors as $m)
                    <a class="card" href="{{ route('buyer.motors.show', $m->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $m->thumbnail_path ? image_url($m->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
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
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Tidak ada motor ditemukan.</div>
                @endforelse
            </div>

            <div style="margin-top:24px;">
                {{ $motors->appends(['q' => $q])->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
@endsection

@extends('layouts.buyer')

@section('title', 'Wishlist')

@section('content')
    <section class="section">
        <div class="container">
            @if (session('status'))
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                    {{ session('status') }}
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Wishlist</div>
                <a class="btn" href="{{ route('buyer.parts.index') }}">Browse Parts</a>
            </div>

            <div style="height:14px;"></div>

            <div class="grid grid-3">
                @forelse ($items as $it)
                    <a class="card" href="{{ route('buyer.parts.show', $it->part->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $it->part->thumbnail_path ? asset($it->part->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                        <div class="card-body">
                            <div class="card-title">{{ $it->part->name }}</div>
                            <div class="card-meta">{{ $it->part->category?->group }} — {{ $it->part->category?->name }}</div>
                            <div class="price">{{ $it->part->defaultVariant ? number_format((float) $it->part->defaultVariant->price, 2, '.', ',') : number_format((float) $it->part->base_price, 2, '.', ',') }}</div>
                        </div>
                    </a>
                @empty
                    <div class="muted">Wishlist kosong.</div>
                @endforelse
            </div>

            <div style="height:12px;"></div>
            {{ $items->links() }}
        </div>
    </section>
@endsection

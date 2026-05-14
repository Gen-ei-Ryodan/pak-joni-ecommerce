@extends('layouts.buyer')

@section('title', 'Products')

@section('content')
    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Products</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a class="btn" href="{{ route('buyer.motors.index') }}">Motor</a>
                    <a class="btn btn-primary" href="{{ route('buyer.parts.index') }}">Parts</a>
                </div>
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

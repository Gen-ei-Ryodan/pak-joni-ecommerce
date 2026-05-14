@extends('layouts.buyer')

@section('title', 'Motor')

@section('content')
    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Motor Catalog</div>
                <a class="btn" href="{{ route('buyer.products') }}">Produk</a>
            </div>

            <div style="height:12px;"></div>

            <form method="get" class="filters">
                <input class="input" name="q" value="{{ $q }}" placeholder="Search motor...">
                <button class="btn" type="submit">Filter</button>
            </form>

            <div style="height:14px;"></div>

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
                    <div class="muted">Belum ada motor.</div>
                @endforelse
            </div>

            <div style="height:12px;"></div>
            {{ $motors->links() }}
        </div>
    </section>
@endsection

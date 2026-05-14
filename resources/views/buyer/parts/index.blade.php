@extends('layouts.buyer')

@section('title', 'Parts')

@section('content')
    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Parts</div>
                <a class="btn" href="{{ route('buyer.products') }}">Products</a>
            </div>

            <div style="height:12px;"></div>

            <form method="get" class="filters">
                <input class="input" name="q" value="{{ $q }}" placeholder="Search part / SKU...">
                <select class="select" name="category">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected((string) $category === (string) $cat->slug)>{{ $cat->group }} — {{ $cat->name }}</option>
                    @endforeach
                </select>
                <button class="btn" type="submit">Filter</button>
            </form>

            <div style="height:14px;"></div>

            <div class="grid grid-3">
                @forelse ($parts as $p)
                    <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $p->thumbnail_path ? asset($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                        <div class="card-body">
                            <div class="card-title">{{ $p->name }}</div>
                            <div class="card-meta">{{ $p->category?->group }} — {{ $p->category?->name }}</div>
                            <div class="price">{{ $p->defaultVariant ? number_format((float) $p->defaultVariant->price, 2, '.', ',') : number_format((float) $p->base_price, 2, '.', ',') }}</div>
                        </div>
                    </a>
                @empty
                    <div class="muted">No parts yet.</div>
                @endforelse
            </div>

            <div style="height:12px;"></div>
            {{ $parts->links() }}
        </div>
    </section>
@endsection

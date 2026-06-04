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
                    <div class="card motor-card">
                        <a class="card-media-link" href="{{ route('buyer.motors.show', $m->slug) }}" style="display:block;text-decoration:none;">
                            <div class="card-media" style="background-image:url('{{ $m->thumbnail_path ? image_url($m->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        </a>
                        <div class="card-body">
                            @if($m->brand)
                                <div class="card-meta">{{ $m->brand->name }}</div>
                            @endif
                            <a href="{{ route('buyer.motors.show', $m->slug) }}" style="text-decoration:none;color:inherit;">
                                <div class="card-title">{{ $m->name }}</div>
                            </a>
                            @if($m->price)
                                <div class="price">Rp {{ number_format($m->price, 0, ',', '.') }}</div>
                            @endif
                        </div>
                        <div class="card-actions">
                            <a href="{{ route('buyer.motors.show', $m->slug) }}" class="card-action-btn primary">Lihat Motor</a>
                            <a href="{{ route('buyer.motors.show', ['motor' => $m->slug, 'tab' => 'parts']) }}" class="card-action-btn">Sparepart</a>
                        </div>
                    </div>
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

@push('head')
    <style>
        .motor-card { display: flex; flex-direction: column; overflow: hidden; }
        .card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-top: 1px solid var(--line);
        }
        .card-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            color: var(--muted);
            transition: all .2s;
            letter-spacing: 0.3px;
        }
        .card-action-btn.primary {
            color: var(--accent);
            border-right: 1px solid var(--line);
        }
        .card-action-btn:hover {
            background: rgba(217,180,111,0.08);
            color: var(--accent);
        }
    </style>
@endpush

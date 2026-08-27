@extends('layouts.buyer')

@section('title', 'Motor')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title-text">Katalog Motor</h2>
                    <div class="section-line"></div>
                </div>
                <a class="btn btn-outline" href="{{ route('buyer.product.choose', ['categoryType' => 'motor']) }}">Lihat Semua Motor</a>
            </div>

            <form method="get" style="display:flex;gap:10px;margin-bottom:24px;">
                <input class="form-input" name="q" value="{{ $q }}" placeholder="Cari motor..." style="flex:1;">
                <button class="btn btn-accent" type="submit">Cari</button>
            </form>

            <div class="grid grid-3">
                @forelse ($items as $item)
                    @php
                        $totalStock = $item->colors->sum('stock');
                    @endphp
                    <div class="card motor-card">
                        <a class="card-media-link" href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" style="display:block;text-decoration:none;">
                            <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        </a>
                        <div class="card-body">
                            @if($item->brand)
                                <div class="card-meta">{{ $item->brand->name }}</div>
                            @endif
                            <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" style="text-decoration:none;color:inherit;">
                                <div class="card-title">{{ $item->name }}</div>
                            </a>
                            @if($item->price)
                                <div class="price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                            @endif
                            <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                                @if($item->stock_status === 'ready')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: rgba(34,197,94,0.1); color: #22c55e;">
                                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                                        Ready Stock
                                    </span>
                                    @if($totalStock > 0)
                                        <span style="font-size: 11px; color: var(--muted);">({{ $totalStock }} unit)</span>
                                    @else
                                        <span style="font-size: 11px; color: #ef4444;">(Habis)</span>
                                    @endif
                                @elseif($item->stock_status === 'indent')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #fef3c7; color: #92400e;">
                                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                                        Indent
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" class="card-action-btn primary">Lihat {{ $item->type->name }}</a>
                            <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug, 'tab' => 'parts']) }}" class="card-action-btn">Sparepart</a>
                        </div>
                    </div>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Tidak ada motor ditemukan.</div>
                @endforelse
            </div>

            <div style="margin-top:24px;">
                {{ $items->appends(['q' => $q])->links('pagination.simple-dark') }}
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

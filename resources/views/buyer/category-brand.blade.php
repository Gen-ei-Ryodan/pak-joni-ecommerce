@extends('layouts.buyer')

@section('title', $brandModel ? $brandModel->name . ' - ' . $type->name : $type->name)

@section('content')
    <section class="section section-category">
        <div class="container">
            {{-- Header --}}
            <div class="section-header center">
                <h2 class="section-title-text">{{ $brandModel ? $brandModel->name : 'Semua ' . $type->name }}</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">
                    @if($brandModel)
                        Jelajahi koleksi {{ $type->name }} {{ $brandModel->name }} dan sparepart pendukungnya.
                    @else
                        Jelajahi seluruh koleksi {{ $type->name }} dari berbagai brand.
                    @endif
                </p>
            </div>

            {{-- Brand Filter --}}
            @if($allBrands->isNotEmpty())
                <div class="brand-filter">
                    <a href="{{ route('buyer.category-brand', ['categoryType' => $type->slug, 'brand' => 'all']) }}"
                       class="filter-tag {{ !$brandModel ? 'active' : '' }}">Semua Brand</a>
                    @foreach($allBrands as $ab)
                        <a href="{{ route('buyer.category-brand', ['categoryType' => $type->slug, 'brand' => $ab->slug]) }}"
                           class="filter-tag {{ $brandModel && $brandModel->id === $ab->id ? 'active' : '' }}">{{ $ab->name }}</a>
                    @endforeach
                </div>
            @endif

            {{-- Category Filter (sub-kategori dalam brand ini) --}}
            @if($categories->isNotEmpty())
                <div class="brand-filter" style="margin-bottom:24px;">
                    <a href="{{ route('buyer.category-brand', ['categoryType' => $type->slug, 'brand' => $brandModel?->slug ?? 'all']) }}"
                       class="filter-tag filter-tag-sm {{ !$selectedCategory ? 'active' : '' }}">Semua Kategori</a>
                    @foreach($categories as $cat)
                        @php
                            $catUrl = route('buyer.category-brand', [
                                'categoryType' => $type->slug,
                                'brand' => $brandModel?->slug ?? 'all',
                                'category' => $cat->slug,
                            ]);
                        @endphp
                        <a href="{{ $catUrl }}"
                           class="filter-tag filter-tag-sm {{ $selectedCategory === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            {{-- ========== MOTOR SECTION ========== --}}
            <div style="margin-bottom:32px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <h3 style="font-size:20px;font-weight:700;">{{ $type->name }}</h3>
                </div>

                <div class="grid grid-3">
                    @forelse ($items as $item)
                        <div class="card motor-card">
                            <a class="card-media-link" href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" style="display:block;text-decoration:none;">
                                <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:220px;"></div>
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
                                @if($item->stock_status === 'indent')
                                    <span class="stock-badge indent">Indent</span>
                                @elseif($item->stock_status === 'ready')
                                    <span class="stock-badge ready">Ready Stock</span>
                                    <span class="stock-badge otr">OTR SURABAYA</span>
                                @endif
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" class="card-action-btn primary">Lihat {{ $type->name }}</a>
                                <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug, 'tab' => 'parts']) }}" class="card-action-btn">Sparepart</a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada {{ $type->name }} tersedia {{ $brandModel ? 'untuk brand ini' : '' }}.</div>
                    @endforelse
                    </div>

                @if(method_exists($items, 'links'))
                    <div style="margin-top:30px;">{{ $items->links('pagination.simple-dark') }}</div>
                @endif
            </div>

            {{-- ========== SPAREPART SECTION ========== --}}
            @if($parts->isNotEmpty())
                <div style="margin-top:40px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                        <h3 style="font-size:20px;font-weight:700;">Sparepart {{ $brandModel ? $brandModel->name : '' }}</h3>
                    </div>

                    <p style="color:var(--muted);font-size:13px;margin-bottom:20px;">
                        Sparepart yang kompatibel {{ $brandModel ? 'dengan ' . $brandModel->name : '' }}.
                    </p>

                    <div class="grid grid-3">
                        @forelse ($parts as $p)
                            <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}">
                                <div class="card-media" style="background-image:url('{{ $p->thumbnail_path ? image_url($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:220px;"></div>
                                <div class="card-body">
                                    @if($p->category)
                                        <div class="card-meta" style="font-size:10px;text-transform:none;letter-spacing:0;">{{ $p->category->group }} &rsaquo; {{ $p->category->name }}</div>
                                    @endif
                                    <div class="card-title">{{ $p->name }}</div>
                                    @if($p->defaultVariant)
                                        <div class="price">Rp {{ number_format($p->defaultVariant->price, 0, ',', '.') }}</div>
                                    @elseif($p->base_price)
                                        <div class="price">Rp {{ number_format($p->base_price, 0, ',', '.') }}</div>
                                    @endif
                                    @if($p->stock_status === 'indent')
                                        <span class="stock-badge indent">Indent</span>
                                    @elseif($p->stock_status === 'ready')
                                        <span class="stock-badge ready">Ready Stock ({{ $p->totalStock() }})</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="empty-state">Belum ada sparepart tersedia {{ $brandModel ? 'untuk brand ini' : '' }}.</div>
                        @endforelse
                    </div>

                    @if(method_exists($parts, 'links'))
                        <div style="margin-top:30px;">{{ $parts->links('pagination.simple-dark') }}</div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        .section-category {
            background: linear-gradient(160deg, #fff 0%, #eef2ff 30%, #dbeafe 70%, #fff 100%);
        }
        .brand-filter {
            display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:16px;
        }
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
        .stock-badge {
            display: inline-block;
            margin-top: 4px;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .stock-badge.ready {
            background: rgba(34,197,94,0.1);
            color: #22c55e;
        }
        .stock-badge.indent {
            background: #fef3c7;
            color: #92400e;
        }
        .stock-badge.otr {
            background: #0055DA;
            color: #fff;
            margin-left: 4px;
        }
        .empty-state {
            text-align: center;
            grid-column: 1/-1;
            padding: 60px 0;
            color: var(--muted);
        }
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

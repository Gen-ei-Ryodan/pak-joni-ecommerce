@extends('layouts.buyer')

@section('title', 'Produk')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Produk Kami</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Jelajahi koleksi motor dan sparepart premium untuk kebutuhan berkendara Anda.</p>
            </div>

            {{-- Step 1: Type Toggle (All / Motor / Sparepart) — paling atas --}}
            <div style="display:flex;justify-content:center;gap:0;margin-bottom:28px;">
                @php
                    $allParams = $selectedBrand ? ['brand' => $selectedBrand] : [];
                    if ($selectedCategory) $allParams['category'] = $selectedCategory;
                    $mParams = array_merge($allParams, ['type' => 'motor']);
                    $sParams = array_merge($allParams, ['type' => 'sparepart']);
                @endphp
                <a href="{{ route('buyer.products', $allParams) }}" class="type-toggle-btn {{ $productType === null ? 'active' : '' }}" style="border-radius:8px 0 0 8px;">Semua</a>
                <a href="{{ route('buyer.products', $mParams) }}" class="type-toggle-btn {{ $productType === 'motor' ? 'active' : '' }}">Motor</a>
                <a href="{{ route('buyer.products', $sParams) }}" class="type-toggle-btn {{ $productType === 'sparepart' ? 'active' : '' }}" style="border-radius:0 8px 8px 0;">Sparepart</a>
            </div>

            {{-- Step 2: Brand Selection --}}
            @if(!empty($brands) && $brands->count())
                <div class="brand-filter">
                    @php
                        $baseParams = $productType ? ['type' => $productType] : [];
                    @endphp
                    <a href="{{ route('buyer.products', $baseParams) }}" class="filter-tag {{ !$selectedBrand ? 'active' : '' }}">Semua Brand</a>
                    @foreach($brands as $brand)
                        @php $bp = array_merge($baseParams, ['brand' => $brand->slug]); @endphp
                        <a href="{{ route('buyer.products', $bp) }}" class="filter-tag {{ $selectedBrand === $brand->slug ? 'active' : '' }}">{{ $brand->name }}</a>
                    @endforeach
                </div>
            @endif

            {{-- Step 3: Category Selection (if brand selected) --}}
            @if(!empty($categories) && $categories->count())
                <div class="brand-filter">
                    @php $catBase = $selectedBrand ? ['brand' => $selectedBrand] : [];
                        if ($productType) $catBase['type'] = $productType; @endphp
                    <a href="{{ route('buyer.products', $catBase) }}" class="filter-tag filter-tag-sm {{ !$selectedCategory ? 'active' : '' }}">Semua Kategori</a>
                    @foreach($categories as $cat)
                        @php $cp = array_merge($catBase, ['category' => $cat->slug]); @endphp
                        <a href="{{ route('buyer.products', $cp) }}" class="filter-tag filter-tag-sm {{ $selectedCategory === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            {{-- ========== MOTOR SECTION ========== --}}
            @if($productType === null || $productType === 'motor')
                @if($productType === null)
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;margin-top:10px;">
                        <h3 style="font-size:20px;font-weight:700;">Motor</h3>
                        <a href="{{ route('buyer.products', $mParams) }}" style="font-size:13px;color:var(--accent);text-decoration:none;">Lihat Semua Motor &rarr;</a>
                    </div>
                @endif

                <div class="grid grid-3">
                    @forelse ($items as $item)
                        <div class="card motor-card">
                            <a class="card-media-link" href="{{ route('buyer.motors.show', $item->slug) }}" style="display:block;text-decoration:none;">
                                <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:220px;"></div>
                            </a>
                            <div class="card-body">
                                @if($item->brand)
                                    <div class="card-meta">{{ $item->brand->name }}</div>
                                @endif
                                <a href="{{ route('buyer.motors.show', $item->slug) }}" style="text-decoration:none;color:inherit;">
                                    <div class="card-title">{{ $item->name }}</div>
                                </a>
                                @if($item->price)
                                    <div class="price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                @endif
                                @if($item->stock_status === 'indent')
                                    <span class="stock-badge indent">Indent</span>
                                @elseif($item->stock_status === 'ready')
                                    <span class="stock-badge ready">Ready Stock</span>
                                @endif
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('buyer.motors.show', $item->slug) }}" class="card-action-btn primary">Lihat Motor</a>
                                <a href="{{ route('buyer.motors.show', ['slug' => $item->slug, 'tab' => 'parts']) }}" class="card-action-btn">Sparepart</a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada produk motor tersedia untuk filter ini.</div>
                    @endforelse
                </div>

                @if(method_exists($items, 'links'))
                    <div style="margin-top:30px;">{{ $items->links('pagination.simple-dark') }}</div>
                @endif
            @endif

            {{-- ========== SPAREPART SECTION ========== --}}
            @if($productType === null || $productType === 'sparepart')
                @if($productType === null)
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;margin-top:40px;">
                        <h3 style="font-size:20px;font-weight:700;">Sparepart</h3>
                        <a href="{{ route('buyer.products', $sParams) }}" style="font-size:13px;color:var(--accent);text-decoration:none;">Lihat Semua Sparepart &rarr;</a>
                    </div>
                @endif

                @if($productType === 'sparepart')
                    {{-- Sparepart Group Filter --}}
                    @if(!empty($sparepartGroups) && $sparepartGroups->count())
                        <div class="sparepart-groups">
                            @php $sgBase = $selectedBrand ? ['brand' => $selectedBrand, 'type' => 'sparepart'] : ['type' => 'sparepart'];
                                if ($selectedCategory) $sgBase['category'] = $selectedCategory; @endphp
                            <a href="{{ route('buyer.products', $sgBase) }}"
                               class="sparepart-group-card {{ !$selectedSparepartGroup ? 'active' : '' }}">
                                <div class="sg-title">Semua Sparepart</div>
                            </a>
                            @foreach($sparepartGroups as $group => $cats)
                                @php $gp = array_merge($sgBase, ['part_group' => $group]); @endphp
                                <a href="{{ route('buyer.products', $gp) }}"
                                   class="sparepart-group-card {{ $selectedSparepartGroup === $group ? 'active' : '' }}">
                                    <div class="sg-title">{{ $group }}</div>
                                    <div class="sg-sub">{{ $cats->pluck('name')->join(', ') }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endif

                <div class="grid grid-3">
                    @forelse ($parts as $p)
                        <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}">
                            <div class="card-media" style="background-image:url('{{ $p->thumbnail_path ? image_url($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:220px;"></div>
                            <div class="card-body">
                                @php $pBrand = $p->items()->first()?->brand; @endphp
                                @if($pBrand)
                                    <div class="card-meta">{{ $pBrand->name }}</div>
                                @endif
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
                        <div class="empty-state">Belum ada produk sparepart tersedia untuk filter ini.</div>
                    @endforelse
                </div>

                @if(method_exists($parts, 'links'))
                    <div style="margin-top:30px;">{{ $parts->links('pagination.simple-dark') }}</div>
                @endif
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
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
        .type-toggle-btn {
            display:inline-flex;align-items:center;justify-content:center;
            padding:10px 36px;font-size:14px;font-weight:600;
            border:1px solid var(--line);background:var(--panel);color:var(--muted);
            cursor:pointer;text-decoration:none;transition:all .2s;min-width:120px;
        }
        .type-toggle-btn:hover { border-color:var(--accent); }
        .type-toggle-btn.active {
            background:var(--accent);color:#fff;border-color:var(--accent);
        }
        .sparepart-groups {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .sparepart-group-card {
            display: block;
            padding: 12px 16px;
            border: 1px solid var(--line);
            border-radius: 10px;
            text-decoration: none;
            color: var(--text);
            transition: all .2s;
        }
        .sparepart-group-card:hover {
            border-color: var(--accent);
        }
        .sparepart-group-card.active {
            border-color: var(--accent);
            background: rgba(217, 180, 111, 0.08);
        }
        .sg-title {
            font-weight: 600;
            font-size: 14px;
        }
        .sg-sub {
            color: var(--muted);
            font-size: 11px;
            margin-top: 4px;
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

@extends('layouts.buyer')

@section('title', $q ? 'Cari: ' . $q : 'Pencarian Produk')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Pencarian Produk</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Cari motor, sparepart, atau suku cadang berdasarkan kata kunci, brand, dan kategori.</p>
            </div>

            {{-- Search Form --}}
            <form method="get" action="{{ route('buyer.search') }}" class="search-page-form">
                <div class="search-input-wrap">
                    <svg class="search-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari motor, sparepart, SKU..." class="search-input-field" autocomplete="off" autofocus>
                    <button type="submit" class="search-submit-btn">Cari</button>
                </div>

                {{-- Filter Row 1: Type + Brand --}}
                <div class="search-filters">
                    {{-- Type Toggle --}}
                    <div class="filter-group">
                        <span class="filter-label">Tipe</span>
                        <div class="filter-toggle-group">
                            @php
                                $allParams = ['q' => $q];
                                if ($brand) $allParams['brand'] = $brand;
                                if ($partGroup) $allParams['part_group'] = $partGroup;
                                $mParams = array_merge($allParams, ['type' => 'motor']);
                                $sParams = array_merge($allParams, ['type' => 'sparepart']);
                            @endphp
                            <a href="{{ route('buyer.search', $allParams) }}" class="filter-toggle-btn {{ $type === null ? 'active' : '' }}">Semua</a>
                            <a href="{{ route('buyer.search', $mParams) }}" class="filter-toggle-btn {{ $type === 'motor' ? 'active' : '' }}">Motor</a>
                            <a href="{{ route('buyer.search', $sParams) }}" class="filter-toggle-btn {{ $type === 'sparepart' ? 'active' : '' }}">Sparepart</a>
                        </div>
                    </div>

                    {{-- Brand Dropdown --}}
                    @if($brands->count())
                        <div class="filter-group">
                            <span class="filter-label">Brand</span>
                            <select name="brand" class="filter-select" onchange="this.form.submit()">
                                <option value="">Semua Brand</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->slug }}" {{ $brand === $b->slug ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Golongan Dropdown (only for sparepart) --}}
                    @if(($type === null || $type === 'sparepart') && $partGroups->count())
                        <div class="filter-group">
                            <span class="filter-label">Golongan</span>
                            <select name="part_group" class="filter-select" onchange="this.form.submit()">
                                <option value="">Semua Golongan</option>
                                @foreach($partGroups as $pg)
                                    <option value="{{ $pg }}" {{ $partGroup === $pg ? 'selected' : '' }}>{{ $pg }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Hidden type (to persist when changing brand/golongan) --}}
                    @if($type)
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif
                </div>
            </form>

            {{-- Active Filters --}}
            @if($q || $type || $brand || $partGroup)
                <div class="active-filters">
                    <span class="active-filters-label">Filter aktif:</span>
                    @if($q)
                        <span class="active-filter-tag">
                            "{{ $q }}"
                            @php $clearQ = $type ? ['type' => $type] : [];
                                if ($brand) $clearQ['brand'] = $brand;
                                if ($partGroup) $clearQ['part_group'] = $partGroup; @endphp
                            <a href="{{ route('buyer.search', $clearQ) }}" class="active-filter-remove">&times;</a>
                        </span>
                    @endif
                    @if($type)
                        @php $clearT = ['q' => $q];
                            if ($brand) $clearT['brand'] = $brand;
                            if ($partGroup) $clearT['part_group'] = $partGroup; @endphp
                        <span class="active-filter-tag">
                            {{ $type === 'motor' ? 'Motor' : 'Sparepart' }}
                            <a href="{{ route('buyer.search', $clearT) }}" class="active-filter-remove">&times;</a>
                        </span>
                    @endif
                    @if($brand)
                        @php $clearB = ['q' => $q];
                            if ($type) $clearB['type'] = $type;
                            if ($partGroup) $clearB['part_group'] = $partGroup; @endphp
                        <span class="active-filter-tag">
                            Brand: {{ $brands->firstWhere('slug', $brand)?->name ?? $brand }}
                            <a href="{{ route('buyer.search', $clearB) }}" class="active-filter-remove">&times;</a>
                        </span>
                    @endif
                    @if($partGroup)
                        @php $clearG = ['q' => $q];
                            if ($type) $clearG['type'] = $type;
                            if ($brand) $clearG['brand'] = $brand; @endphp
                        <span class="active-filter-tag">
                            {{ $partGroup }}
                            <a href="{{ route('buyer.search', $clearG) }}" class="active-filter-remove">&times;</a>
                        </span>
                    @endif
                    <a href="{{ route('buyer.search') }}" class="active-filter-clear-all">Reset Semua</a>
                </div>
            @endif

            {{-- Results Count --}}
            <div class="search-results-header">
                @if($q || $type || $brand || $partGroup)
                    <div class="results-count">
                        {{ $totalResults }} hasil ditemukan
                        @if($q) untuk <strong>"{{ $q }}"</strong> @endif
                    </div>
                @elseif($totalResults > 0)
                    <div class="results-count">Menampilkan semua produk ({{ $totalResults }})</div>
                @endif
            </div>

            {{-- ========== MOTOR RESULTS ========== --}}
            @if($type === null || $type === 'motor')
                @if($type === null && $items->count())
                    <div class="search-section-header">
                        <h3 class="search-section-title">Motor</h3>
                        <span class="search-section-count">{{ $items->total() }} ditemukan</span>
                    </div>
                @endif

                <div class="grid grid-3">
                    @forelse($items as $item)
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
                            </div>
                            <div class="card-actions">
                                <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" class="card-action-btn primary">Lihat {{ $item->type->name }}</a>
                                <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug, 'tab' => 'parts']) }}" class="card-action-btn">Sparepart</a>
                            </div>
                        </div>
                    @empty
                        @if($q || $type === 'motor')
                            <div class="empty-state">Tidak ada motor ditemukan untuk pencarian ini.</div>
                        @endif
                    @endforelse
                </div>

                @if($items->hasPages())
                    <div class="pagination-wrap">{{ $items->appends(request()->except('motor_page'))->links('pagination.simple-dark') }}</div>
                @endif
            @endif

            {{-- ========== SPAREPART RESULTS ========== --}}
            @if($type === null || $type === 'sparepart')
                @if($type === null && $parts->count())
                    <div class="search-section-header">
                        <h3 class="search-section-title">Sparepart</h3>
                        <span class="search-section-count">{{ $parts->total() }} ditemukan</span>
                    </div>
                @endif

                <div class="grid grid-3">
                    @forelse($parts as $p)
                        <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}">
                            <div class="card-media" style="background-image:url('{{ $p->thumbnail_path ? image_url($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
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
                                <div class="card-sku">SKU: {{ $p->sku }}</div>
                                @if($p->stock_status === 'ready')
                                    <span class="stock-tag ready">Ready Stock</span>
                                @elseif($p->stock_status === 'indent')
                                    <span class="stock-tag indent">Indent</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        @if($q || $type === 'sparepart')
                            <div class="empty-state">Tidak ada sparepart ditemukan untuk pencarian ini.</div>
                        @endif
                    @endforelse
                </div>

                @if($parts->hasPages())
                    <div class="pagination-wrap">{{ $parts->appends(request()->except('part_page'))->links('pagination.simple-dark') }}</div>
                @endif
            @endif

            {{-- Initial empty state --}}
            @if(!$q && !$type && !$brand && !$partGroup && $items->isEmpty() && $parts->isEmpty())
                <div class="search-initial-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--line);"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <p>Masukkan kata kunci untuk mencari motor atau sparepart.</p>
                    <p class="muted">Contoh: "Papio", "Busi", "CFMOTO", "Kampas Rem"</p>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        /* Search Form */
        .search-page-form {
            max-width: 800px;
            margin: 0 auto 20px;
        }
        .search-input-wrap {
            display: flex;
            align-items: center;
            gap: 0;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            transition: border-color .2s;
        }
        .search-input-wrap:focus-within {
            border-color: var(--accent);
        }
        .search-input-icon {
            margin-left: 16px;
            color: var(--muted);
            flex-shrink: 0;
        }
        .search-input-field {
            flex: 1;
            background: transparent;
            border: none;
            padding: 14px 12px;
            font-size: 15px;
            color: var(--text);
            font-family: inherit;
            outline: none;
        }
        .search-input-field::placeholder {
            color: var(--muted);
        }
        .search-submit-btn {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 14px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: opacity .2s;
        }
        .search-submit-btn:hover {
            opacity: 0.9;
        }

        /* Filters */
        .search-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
            justify-content: center;
            margin-top: 16px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding-left: 2px;
        }
        .filter-toggle-group {
            display: flex;
            gap: 0;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }
        .filter-toggle-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            color: var(--muted);
            background: var(--panel);
            border-right: 1px solid var(--line);
            transition: all .2s;
        }
        .filter-toggle-btn:last-child {
            border-right: none;
        }
        .filter-toggle-btn:hover {
            color: var(--accent);
        }
        .filter-toggle-btn.active {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }
        .filter-select {
            padding: 8px 14px;
            font-size: 13px;
            font-family: inherit;
            background: var(--panel);
            color: var(--text);
            border: 1px solid var(--line);
            border-radius: 8px;
            cursor: pointer;
            min-width: 160px;
            outline: none;
        }
        .filter-select:focus {
            border-color: var(--accent);
        }

        /* Active Filters */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin: 16px 0 0;
            justify-content: center;
        }
        .active-filters-label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .active-filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            font-size: 12px;
            background: rgba(217,180,111,0.1);
            border: 1px solid rgba(217,180,111,0.3);
            border-radius: 20px;
            color: var(--accent);
        }
        .active-filter-remove {
            color: var(--accent);
            text-decoration: none;
            font-size: 14px;
            line-height: 1;
            font-weight: 700;
        }
        .active-filter-remove:hover {
            color: #f87171;
        }
        .active-filter-clear-all {
            font-size: 12px;
            color: var(--muted);
            text-decoration: underline;
            margin-left: 4px;
        }
        .active-filter-clear-all:hover {
            color: #f87171;
        }

        /* Results */
        .search-results-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 24px 0 8px;
        }
        .results-count {
            font-size: 15px;
            color: var(--muted);
        }
        .results-count strong {
            color: var(--text);
        }
        .search-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 32px 0 16px;
        }
        .search-section-title {
            font-size: 20px;
            font-weight: 700;
        }
        .search-section-count {
            font-size: 13px;
            color: var(--muted);
        }
        .pagination-wrap {
            margin-top: 24px;
        }

        /* Cards */
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
        .card-sku {
            font-size: 10px;
            color: var(--muted);
            margin-top: 4px;
        }
        .stock-tag {
            display: inline-block;
            margin-top: 4px;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .stock-tag.ready {
            background: rgba(34,197,94,0.1);
            color: #22c55e;
        }
        .stock-tag.indent {
            background: #fef3c7;
            color: #92400e;
        }
        .empty-state {
            text-align: center;
            grid-column: 1/-1;
            padding: 60px 0;
            color: var(--muted);
        }
        .search-initial-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px;
            text-align: center;
            color: var(--muted);
        }
        .search-initial-state svg {
            margin-bottom: 20px;
        }
        .search-initial-state p {
            font-size: 16px;
            margin-bottom: 6px;
        }
    </style>
@endpush

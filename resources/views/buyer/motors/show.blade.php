@extends('layouts.buyer')

@section('title', $item->name)

@section('content')
    <section class="section">
        <div class="container">
            {{-- Header --}}
            <div class="motor-header">
                @if($item->brand)
                    <div class="motor-brand">{{ $item->brand->name }}</div>
                @endif
                <h1 class="motor-name">{{ $item->name }}</h1>
                @if($item->price)
                    <div class="motor-price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                @endif
            </div>

            {{-- Tab Navigation --}}
            <div class="motor-tabs">
                <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}"
                   class="motor-tab {{ $tab === 'detail' ? 'active' : '' }}">
                    Detail {{ $item->type->name }}
                </a>
                @if($item->type->slug !== 'sparepart')
                    <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug, 'tab' => 'parts']) }}"
                       class="motor-tab {{ $tab === 'parts' ? 'active' : '' }}">
                        Sparepart {{ $item->type->name }}
                    </a>
                @endif
            </div>

            {{-- ============ TAB: DETAIL ============ --}}
            @if($tab === 'detail')
                {{-- Gallery Atas --}}
                <div class="motor-gallery-section">
                    <div class="gallery-main" id="galleryMain" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;">
                        @if(!$item->thumbnail_path)
                            <span style="color:var(--muted);">No Image</span>
                        @endif
                    </div>
                    @php
                        $galleryImages = $item->images->filter(fn($img) => !str_starts_with($img->path, 'http'));
                    @endphp
                    @if($galleryImages->count())
                        <div class="gallery-thumbs">
                            @foreach($galleryImages as $img)
                                <button class="gallery-thumb {{ $loop->first ? 'active' : '' }}" style="background-image:url('{{ image_url($img->path) }}');" onclick="document.getElementById('galleryMain').style.backgroundImage='url({{ image_url($img->path) }})';this.parentElement.querySelectorAll('.gallery-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active');"></button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Info & Variants Bawah --}}
                <div class="motor-info-section">
                    @if($item->short_description)
                        <p class="motor-short-desc">{{ $item->short_description }}</p>
                    @endif

                    {{-- Stock Info --}}
                    @php
                        $totalStock = $item->colors->sum('stock');
                    @endphp
                    <div style="text-align:center;margin-bottom:16px;">
                    @php
                        $totalStock = $item->colors->sum('stock');
                        $finalPrice = $item->final_price ?? $item->price;
                    @endphp
                    @if($item->stock_status === 'ready')
                        <span id="stockBadge" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;background:rgba(34,197,94,0.1);color:#22c55e;">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#22c55e;"></span>
                            Ready Stock
                            @if($totalStock > 0)
                                - {{ $totalStock }} unit tersedia
                            @else
                                - Habis
                            @endif
                        </span>
                        @if($item->discount_type)
                            - Diskon: <span style="color:#f59e0b;font-weight:500;">{{ strtoupper($item->discount_type) }} {{ $item->discount_type === 'percent' ? number_format($item->discount_value, 1, ',', '.') . '%' : 'Rp '.number_format($item->discount_value, 0, ',', '.') }}</span>
                        @endif
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;background:#0055DA;color:#fff;margin-left:8px;">OTR SURABAYA</span>
                    @elseif($item->stock_status === 'indent')
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;background:#fef3c7;color:#92400e;">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#f59e0b;"></span>
                            Indent - DP 50%
                        </span>
                        @if($item->discount_type)
                            - Diskon: <span style="color:#f59e0b;font-weight:500;">{{ strtoupper($item->discount_type) }} {{ $item->discount_type === 'percent' ? number_format($item->discount_value, 1, ',', '.') . '%' : 'Rp '.number_format($item->discount_value, 0, ',', '.') }}</span>
                        @endif
                    @endif
                </div>

                    @if($item->colors->count())
                        <div class="motor-colors">
                            <div class="motor-colors-label">Varian Warna:</div>
                            <div class="motor-colors-list">
                                @foreach($item->colors as $loopIdx => $color)
                                    <button type="button" class="color-item color-btn {{ $loopIdx === 0 ? 'active' : '' }}"
                                        data-stock="{{ $color->stock }}"
                                        @if($color->image_path)
                                            data-img="{{ image_url($color->image_path) }}"
                                        @else
                                            data-img="{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}"
                                        @endif
                                        onclick="var main=document.getElementById('galleryMain');if(this.dataset.img){main.style.backgroundImage='url('+this.dataset.img+')';}document.querySelectorAll('.color-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');document.querySelector('[data-color-id]').value='{{ $color->id }}';updateStockDisplay(parseInt(this.dataset.stock));">
                                        <span class="color-dot" style="background:{{ $color->color_code ?: '#666' }};"></span>
                                        <span class="color-name">{{ $color->name }}</span>
                                        <span class="color-stock" style="font-size:10px;color:{{ $color->stock > 0 ? '#22c55e' : '#ef4444' }};">({{ $color->stock }})</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <form method="post" action="{{ route('buyer.cart.store') }}" style="margin-top:20px;display:grid;gap:10px;">
                            @csrf
                            <input type="hidden" name="itemable_type" value="item_color">
                            <input type="hidden" name="itemable_id" value="{{ $item->colors->first()->id ?? '' }}" data-color-id>
                            <input type="hidden" name="quantity" value="1">
                            @php
                                $firstColorStock = $item->colors->first()->stock ?? 0;
                            @endphp
                            <button type="submit" class="btn btn-primary" style="width:100%;" id="addToCartBtn"
                                @if($item->stock_status === 'ready' && $firstColorStock <= 0) disabled @endif>
                                @if($item->stock_status === 'indent')
                                    Pre-Order (Indent) - DP 50%
                                @elseif($item->stock_status === 'ready' && $firstColorStock <= 0)
                                    Stok Habis
                                @else
                                    Add to Cart
                                @endif
                            </button>
                        </form>

                        @if($item->stock_status === 'indent')
                            <div class="indent-notice">
                                Produk ini tersedia secara indent. DP 50% akan dibayarkan saat checkout.
                            </div>
                        @elseif($item->stock_status === 'ready' && $totalStock <= 0)
                            <div class="indent-notice" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #dc2626;">
                                Maaf, stok motor ini sedang habis. Silakan hubungi kami untuk informasi lebih lanjut.
                            </div>
                        @endif
                    @endif
                </div>

                @if($item->images360->count() >= 4)
                    <div class="motor-360-section">
                        <h2 class="section-title-text" style="margin-bottom:20px;">Frame 360&deg;</h2>
                        <div class="viewer-360" data-360-viewer>
                            <div class="viewer-360-frame">
                                <img src="{{ image_url($item->images360->first()->path) }}" alt="360 view" id="viewer360Img" draggable="false">
                            </div>
                            <div class="viewer-360-controls" style="pointer-events:none;">
                                <span>&#8592; Drag untuk memutar 360&deg; &#8594;</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($item->specifications->count())
                    <div class="motor-specs-section">
                        <div style="max-width:700px;margin:0 auto;">
                            <h2 class="section-title-text" style="margin-bottom:20px;text-align:center;">Spesifikasi</h2>
                            <div class="spec-tabs">
                                @foreach($specsGrouped as $group => $specs)
                                    <div class="spec-group">
                                        <h3 class="spec-group-title">{{ $group }}</h3>
                                        <div class="spec-table">
                                            @foreach($specs as $spec)
                                                <div class="spec-row">
                                                    <div class="spec-key">{{ $spec->key }}</div>
                                                    <div class="spec-value">{{ $spec->value }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($item->document_path)
                    <div class="motor-desc-section">
                        <h2 class="section-title-text" style="margin-bottom:16px;">Cetak Brosur</h2>
                        <a href="{{ image_url($item->document_path) }}" target="_blank" rel="noopener"
                           class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Cetak Brosur
                        </a>
                    </div>
                @endif

                @if($item->type->slug === 'sparepart')
                    <div class="motor-desc-section">
                        @if($item->part_number)
                            <div style="font-size:13px;color:var(--muted);margin-bottom:12px;">
                                <strong>Part Number:</strong> {{ $item->part_number }}
                            </div>
                        @endif

                        @if($item->catalog_pdf_path)
                            <h2 class="section-title-text" style="margin-bottom:16px;">Katalog Part (PDF)</h2>
                            <a href="{{ image_url($item->catalog_pdf_path) }}" target="_blank" rel="noopener"
                               class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6M9 18h4"/></svg>
                                Buka Katalog Part (PDF)
                            </a>
                        @endif

                        @php $motors = $item->compatibleMotors ?? collect(); @endphp
                        @if($motors->count())
                            <div style="margin-top:20px;">
                                <h2 class="section-title-text" style="margin-bottom:12px;font-size:18px;">Kompatibel Dengan</h2>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                    @foreach($motors as $motor)
                                        <a href="{{ route('buyer.motors.show', ['categoryType' => $motor->type->slug ?? 'motor', 'slug' => $motor->slug]) }}"
                                           style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1px solid var(--line);border-radius:20px;font-size:12px;color:var(--accent);text-decoration:none;">
                                            @if($motor->brand)<strong>{{ $motor->brand->name }}</strong>@endif
                                            {{ $motor->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if(!empty($relatedItems) && $relatedItems->count())
                    <div class="related-section">
                        <div class="section-header">
                            <h2 class="section-title-text">Produk Terkait</h2>
                            <div class="section-line"></div>
                        </div>
                        <div class="grid grid-4">
                            @foreach($relatedItems as $ri)
                                <a class="card" href="{{ route('buyer.motors.show', ['categoryType' => $ri->type->slug, 'slug' => $ri->slug]) }}">
                                    <div class="card-media" style="background-image:url('{{ $ri->thumbnail_path ? image_url($ri->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                                    <div class="card-body">
                                        @if($ri->brand)
                                            <div class="card-meta">{{ $ri->brand->name }}</div>
                                        @endif
                                        <div class="card-title">{{ $ri->name }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            @else
                {{-- ============ TAB: SPAREPART ============ --}}
                <div class="parts-tab-section">
                    <p style="color:var(--muted);margin-bottom:20px;text-align:center;">Sparepart yang kompatibel dengan <strong>{{ $item->name }}</strong></p>

                    <table class="part-table">
                        <thead>
                            <tr>
                                <th class="part-table-no">No</th>
                                <th class="part-table-name">Part Number</th>
                                <th class="part-table-name">Nama Sparepart</th>
                                <th class="part-table-price">SRP (Harga Rp)</th>
                                <th class="part-table-stock">Status Stok</th>
                                <th class="part-table-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($compatibleSpareparts ?? [] as $index => $sp)
                                @php $spTotalStock = $sp->colors->sum('stock'); @endphp
                                <tr>
                                    <td class="part-table-no">{{ $index + 1 }}</td>
                                    <td class="part-table-name">{{ $sp->part_number ?? '-' }}</td>
                                    <td class="part-table-name">{{ $sp->name }}</td>
                                    <td class="part-table-price">Rp {{ number_format($sp->price ?? 0, 0, ',', '.') }}</td>
                                    <td class="part-table-stock">
                                        <span class="stock-tag {{ $spTotalStock > 0 ? 'ready' : 'indent' }}">{{ $spTotalStock > 0 ? 'Ready Stock' : 'Habis' }}</span>
                                    </td>
                                    <td class="part-table-action">
                                        @php
                                            $firstColor = $sp->colors->first();
                                        @endphp
                                        @if($firstColor)
                                            <form method="post" action="{{ route('buyer.cart.store') }}" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="itemable_type" value="item_color">
                                                <input type="hidden" name="itemable_id" value="{{ $firstColor->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary" style="padding:6px 14px;font-size:12px;">
                                                    + Keranjang
                                                </button>
                                            </form>
                                        @else
                                            <span style="color:var(--muted);font-size:12px;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="part-table-no" colspan="6">Belum ada sparepart tersedia untuk motor ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        /* Motor Header */
        .motor-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .motor-brand {
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 6px;
        }
        .motor-name {
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 700;
            margin-bottom: 8px;
        }
        .motor-price {
            font-size: 22px;
            font-weight: 700;
            color: var(--accent);
        }

        /* Tabs */
        .motor-tabs {
            display: flex;
            justify-content: center;
            gap: 0;
            margin-bottom: 32px;
            border-bottom: 2px solid var(--line);
        }
        .motor-tab {
            display: inline-flex;
            align-items: center;
            padding: 12px 32px;
            font-size: 14px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
            letter-spacing: 0.4px;
        }
        .motor-tab:hover {
            color: var(--text);
        }
        .motor-tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }

        /* Detail Tab - New Layout */
        .motor-gallery-section {
            margin-bottom: 32px;
        }
        .motor-info-section {
            max-width: 700px;
            margin: 0 auto;
        }
        .gallery-main {
            width: 100%;
            aspect-ratio: 3 / 2;
            border-radius: var(--radius);
            background-size: cover;
            background-position: center bottom;
        }
        .gallery-thumbs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 14px;
        }
        .gallery-thumb {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            border: 2px solid transparent;
            background-size: cover;
            background-position: center;
            cursor: pointer;
            transition: border-color 0.2s ease;
            padding: 0;
        }
        .gallery-thumb.active { border-color: var(--accent); }
        .motor-short-desc {
            font-size: 14px;
            color: var(--muted);
            line-height: 2;
            text-align: center;
        }
        .motor-colors { margin-top: 24px; }
        .motor-colors-label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .motor-colors-list {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }
        .color-item { display: flex; align-items: center; gap: 6px; }
        .color-btn {
            background: none;
            border: 1px solid transparent;
            border-radius: 20px;
            padding: 4px 12px 4px 4px;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
            font: inherit;
        }
        .color-btn:hover,
        .color-btn.active {
            border-color: var(--accent);
            background: rgba(217, 180, 111, 0.08);
        }
        .color-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--line);
            display: inline-block;
        }
        .color-name { font-size: 12px; color: var(--muted); }
        .indent-notice {
            margin-top: 8px;
            padding: 8px 12px;
            background: rgba(234,179,8,0.1);
            border: 1px solid rgba(234,179,8,0.3);
            border-radius: 8px;
            font-size: 13px;
            color: #ca8a04;
        }
        .motor-360-section { margin-top: 40px; }
        .viewer-360 {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            cursor: ew-resize;
            user-select: none;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--line);
            background: #f0f0f0;
        }
        .viewer-360-frame {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: #e8e8e8;
        }
        .viewer-360-frame img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            pointer-events: none;
            -webkit-user-drag: none;
            user-select: none;
        }
        .viewer-360-controls {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.65);
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 2;
        }
        .motor-specs-section { margin-top: 40px; }
        .spec-tabs { display: flex; flex-direction: column; gap: 24px; }
        .spec-group-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 3px solid var(--text);
        }
        .spec-table { display: flex; flex-direction: column; gap: 0; }
        .spec-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 13px;
        }
        .spec-key { color: var(--muted); }
        .spec-value { color: var(--text); font-weight: 500; text-align: right; }
        .motor-desc-section {
            margin-top: 40px;
            background: var(--panel);
            border: 2px solid var(--line);
            border-radius: var(--radius);
            padding: 30px;
        }
        .desc-content {
            color: var(--muted);
            line-height: 2;
            font-size: 14px;
        }
        .related-section { margin-top: 50px; }

        /* Parts Tab */
        .parts-tab-section { margin-top: 8px; }
        .parts-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 28px;
        }
        .parts-filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            color: var(--muted);
            border: 1px solid var(--line);
            border-radius: 20px;
            transition: all .2s;
        }
        .parts-filter-tag:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        .parts-filter-tag.active {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(217,180,111,0.1);
        }
        .parts-filter-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            font-size: 11px;
            font-weight: 700;
            background: var(--line);
            color: var(--muted);
            border-radius: 11px;
            padding: 0 6px;
        }
        .parts-filter-tag.active .parts-filter-count {
            background: var(--accent);
            color: #000;
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

        /* Parts Table */
        .part-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 13px;
        }
        .part-table thead {
            background: var(--panel);
            border-bottom: 2px solid var(--accent);
        }
        .part-table th,
        .part-table td {
            padding: 12px 8px;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }
        .part-table th {
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
        }
        .part-table-tr {
            transition: background 0.2s;
        }
        .part-table-tr:hover {
            background: rgba(217, 180, 111, 0.04);
        }
        .part-table-no {
            width: 40px;
            color: var(--muted);
            font-weight: 600;
        }
        .part-table-name {
            flex: 1;
        }
        .part-table-price {
            text-align: right;
            color: var(--accent);
            font-weight: 500;
        }
        .part-table-stock {
            text-align: center;
        }
        .part-table-action {
            text-align: center;
        }
        .part-table-action .btn {
            padding: 6px 10px;
            font-size: 12px;
        }

        @media (max-width: 720px) {
            .motor-tab { padding: 12px 20px; font-size: 13px; }
        }
    </style>
@endpush

@if($tab === 'detail' && $item->colors->count())
    @push('scripts')
        <script>
            function updateStockDisplay(stock) {
                var badge = document.getElementById('stockBadge');
                var btn = document.getElementById('addToCartBtn');
                if (badge) {
                    if (stock > 0) {
                        badge.innerHTML = '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#22c55e;"></span> Ready Stock - ' + stock + ' unit tersedia';
                        badge.style.background = 'rgba(34,197,94,0.1)';
                        badge.style.color = '#22c55e';
                    } else {
                        badge.innerHTML = '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#ef4444;"></span> Stok Habis';
                        badge.style.background = 'rgba(239,68,68,0.1)';
                        badge.style.color = '#ef4444';
                    }
                }
                if (btn) {
                    if (stock > 0) {
                        btn.disabled = false;
                        btn.textContent = 'Add to Cart';
                    } else {
                        btn.disabled = true;
                        btn.textContent = 'Stok Habis';
                    }
                }
            }
        </script>
    @endpush
@endif

@if($item->images360->count() >= 4 && $tab === 'detail')
    @push('scripts')
        <script>
            (function() {
                const viewer = document.querySelector('[data-360-viewer]');
                if (!viewer) return;
                const img = document.getElementById('viewer360Img');
                const images = [
                    @foreach($item->images360 as $img360)
                        "{{ image_url($img360->path) }}",
                    @endforeach
                ];
                let currentFrame = 0;
                let dragging = false;
                let startX = 0;

                function setFrame(idx) {
                    currentFrame = ((idx % images.length) + images.length) % images.length;
                    img.src = images[currentFrame];
                }

                viewer.addEventListener('mousedown', (e) => { dragging = true; startX = e.clientX; e.preventDefault(); });
                viewer.addEventListener('touchstart', (e) => { dragging = true; startX = e.touches[0].clientX; });

                document.addEventListener('mousemove', (e) => {
                    if (!dragging) return;
                    const diff = e.clientX - startX;
                    if (Math.abs(diff) > 5) {
                        setFrame(currentFrame + (diff > 0 ? 1 : -1));
                        startX = e.clientX;
                    }
                });

                document.addEventListener('touchmove', (e) => {
                    if (!dragging) return;
                    const diff = e.touches[0].clientX - startX;
                    if (Math.abs(diff) > 5) {
                        setFrame(currentFrame + (diff > 0 ? 1 : -1));
                        startX = e.touches[0].clientX;
                    }
                });

                document.addEventListener('mouseup', () => { dragging = false; });
                document.addEventListener('touchend', () => { dragging = false; });
            })();
        </script>
    @endpush
@endif

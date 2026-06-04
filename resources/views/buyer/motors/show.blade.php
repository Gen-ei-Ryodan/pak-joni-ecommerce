@extends('layouts.buyer')

@section('title', $motor->name)

@section('content')
    <section class="section">
        <div class="container">
            {{-- Header --}}
            <div class="motor-header">
                @if($motor->brand)
                    <div class="motor-brand">{{ $motor->brand->name }}</div>
                @endif
                <h1 class="motor-name">{{ $motor->name }}</h1>
                @if($motor->price)
                    <div class="motor-price">Rp {{ number_format($motor->price, 0, ',', '.') }}</div>
                @endif
            </div>

            {{-- Tab Navigation --}}
            <div class="motor-tabs">
                <a href="{{ route('buyer.motors.show', ['motor' => $motor->slug]) }}" 
                   class="motor-tab {{ $tab === 'detail' ? 'active' : '' }}">
                    Detail Motor
                </a>
                <a href="{{ route('buyer.motors.show', ['motor' => $motor->slug, 'tab' => 'parts']) }}" 
                   class="motor-tab {{ $tab === 'parts' ? 'active' : '' }}">
                    Sparepart Motor
                </a>
            </div>

            {{-- ============ TAB: DETAIL MOTOR ============ --}}
            @if($tab === 'detail')
                {{-- Gallery Atas --}}
                <div class="motor-gallery-section">
                    <div class="gallery-main" id="galleryMain" style="background-image:url('{{ $motor->thumbnail_path ? image_url($motor->thumbnail_path) : '' }}');background-size:cover;background-position:center;">
                        @if(!$motor->thumbnail_path)
                            <span style="color:var(--muted);">No Image</span>
                        @endif
                    </div>
                    @php
                        $galleryImages = $motor->images->filter(fn($img) => !str_starts_with($img->path, 'http'));
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
                    @if($motor->short_description)
                        <p class="motor-short-desc">{{ $motor->short_description }}</p>
                    @endif

                    @if($motor->colors->count())
                        <div class="motor-colors">
                            <div class="motor-colors-label">Varian Warna:</div>
                            <div class="motor-colors-list">
                                @foreach($motor->colors as $loopIdx => $color)
                                    <button type="button" class="color-item color-btn {{ $loopIdx === 0 ? 'active' : '' }}"
                                        @if($color->image_path)
                                            data-img="{{ image_url($color->image_path) }}"
                                        @else
                                            data-img="{{ $motor->thumbnail_path ? image_url($motor->thumbnail_path) : '' }}"
                                        @endif
                                        onclick="var main=document.getElementById('galleryMain');if(this.dataset.img){main.style.backgroundImage='url('+this.dataset.img+')';}document.querySelectorAll('.color-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');document.querySelector('[data-color-id]').value='{{ $color->id }}';">
                                        <span class="color-dot" style="background:{{ $color->color_code ?: '#666' }};"></span>
                                        <span class="color-name">{{ $color->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <form method="post" action="{{ route('buyer.cart.store') }}" style="margin-top:20px;display:grid;gap:10px;">
                            @csrf
                            <input type="hidden" name="itemable_type" value="motor_color">
                            <input type="hidden" name="itemable_id" value="{{ $motor->colors->first()->id ?? '' }}" data-color-id>
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary" style="width:100%;">
                                @if($motor->stock_status === 'indent')
                                    Pre-Order (Indent) - DP 50%
                                @else
                                    Add to Cart
                                @endif
                            </button>
                        </form>

                        @if($motor->stock_status === 'indent')
                            <div class="indent-notice">
                                Produk ini tersedia secara indent. DP 50% akan dibayarkan saat checkout.
                            </div>
                        @endif
                    @endif
                </div>

                @if($motor->images360->count() >= 4)
                    <div class="motor-360-section">
                        <h2 class="section-title-text" style="margin-bottom:20px;">Frame 360&deg;</h2>
                        <div class="viewer-360" data-360-viewer style="max-width:600px;margin:0 auto;position:relative;cursor:ew-resize;user-select:none;border-radius:12px;overflow:hidden;border:1px solid var(--line);">
                            <img src="{{ image_url($motor->images360->first()->path) }}" alt="360 view" id="viewer360Img" style="width:100%;display:block;" draggable="false">
                            <div style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.6);color:#fff;padding:6px 14px;border-radius:20px;font-size:12px;">
                                &#8592; Drag untuk memutar 360&deg; &#8594;
                            </div>
                        </div>
                    </div>
                @endif

                @if($motor->specifications->count())
                    <div class="motor-specs-section">
                        <div style="max-width:700px;margin:0 auto;">
                            <h2 class="section-title-text" style="margin-bottom:20px;text-align:center;">Spesifikasi</h2>
                            <div class="spec-tabs">
                                @foreach($specGroups as $group => $specs)
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

                @if($motor->description)
                    <div class="motor-desc-section">
                        <h2 class="section-title-text" style="margin-bottom:16px;">Deskripsi</h2>
                        <div class="desc-content">{!! $motor->description !!}</div>
                    </div>
                @endif

                @if(!empty($relatedMotors) && $relatedMotors->count())
                    <div class="related-section">
                        <div class="section-header">
                            <h2 class="section-title-text">Produk Terkait</h2>
                            <div class="section-line"></div>
                        </div>
                        <div class="grid grid-4">
                            @foreach($relatedMotors as $rm)
                                <a class="card" href="{{ route('buyer.motors.show', $rm->slug) }}">
                                    <div class="card-media" style="background-image:url('{{ $rm->thumbnail_path ? image_url($rm->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                                    <div class="card-body">
                                        @if($rm->brand)
                                            <div class="card-meta">{{ $rm->brand->name }}</div>
                                        @endif
                                        <div class="card-title">{{ $rm->name }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            @else
                {{-- ============ TAB: SPAREPART MOTOR ============ --}}
                <div class="parts-tab-section">
                    <p style="color:var(--muted);margin-bottom:20px;text-align:center;">Sparepart yang kompatibel dengan <strong>{{ $motor->name }}</strong></p>

                    {{-- Golongan Filter --}}
                    <div class="parts-filter">
                        <a href="{{ route('buyer.motors.show', ['motor' => $motor->slug, 'tab' => 'parts']) }}"
                           class="parts-filter-tag {{ !$selectedPartGroup ? 'active' : '' }}">
                            Semua Sparepart
                            <span class="parts-filter-count">{{ $partsGrouped->flatten()->count() }}</span>
                        </a>
                        @foreach($partGroups as $group)
                            @php $count = $partsGrouped->get($group)?->count() ?? 0; @endphp
                            @if($count > 0)
                                <a href="{{ route('buyer.motors.show', ['motor' => $motor->slug, 'tab' => 'parts', 'part_group' => $group]) }}"
                                   class="parts-filter-tag {{ $selectedPartGroup === $group ? 'active' : '' }}">
                                    {{ $group }}
                                    <span class="parts-filter-count">{{ $count }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Parts Grid --}}
                    <div class="grid grid-3">
                        @forelse ($parts as $part)
                            <a class="card" href="{{ route('buyer.parts.show', $part->slug) }}">
                                <div class="card-media" style="background-image:url('{{ $part->thumbnail_path ? image_url($part->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                                <div class="card-body">
                                    @if($part->category)
                                        <div class="card-meta">{{ $part->category->group }} &rsaquo; {{ $part->category->name }}</div>
                                    @endif
                                    <div class="card-title">{{ $part->name }}</div>
                                    @if($part->defaultVariant)
                                        <div class="price">Rp {{ number_format($part->defaultVariant->price, 0, ',', '.') }}</div>
                                    @elseif($part->base_price)
                                        <div class="price">Rp {{ number_format($part->base_price, 0, ',', '.') }}</div>
                                    @endif
                                    @if($part->stock_status === 'ready')
                                        <span class="stock-tag ready">Ready Stock</span>
                                    @elseif($part->stock_status === 'indent')
                                        <span class="stock-tag indent">Indent</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="empty-state">
                                @if($selectedPartGroup)
                                    Tidak ada sparepart kategori <strong>{{ $selectedPartGroup }}</strong> untuk motor ini.
                                @else
                                    Belum ada sparepart tersedia untuk motor ini.
                                @endif
                            </div>
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
            height: 450px;
            border-radius: var(--radius);
            border: 1px solid var(--line);
            margin: 0 auto;
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
            line-height: 1.7;
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
        .motor-specs-section { margin-top: 40px; }
        .spec-tabs { display: flex; flex-direction: column; gap: 24px; }
        .spec-group-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--line);
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
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 30px;
        }
        .desc-content {
            color: var(--muted);
            line-height: 1.8;
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

        @media (max-width: 720px) {
            .gallery-main { height: 320px; }
            .motor-tab { padding: 12px 20px; font-size: 13px; }
        }
    </style>
@endpush

@if($motor->images360->count() >= 4 && $tab === 'detail')
    @push('scripts')
        <script>
            (function() {
                const viewer = document.querySelector('[data-360-viewer]');
                if (!viewer) return;
                const img = document.getElementById('viewer360Img');
                const images = [
                    @foreach($motor->images360 as $img)
                        "{{ image_url($img->path) }}",
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

@extends('layouts.buyer')

@section('title', $motor->name)

@section('content')
    <section class="section">
        <div class="container">
            <div class="motor-detail">
                <div class="motor-gallery">
                    @if($motor->images->count())
                        <div class="gallery-main" style="background-image:url('{{ asset($motor->images->first()->path) }}');background-size:cover;background-position:center;"></div>
                        @if($motor->images->count() > 1)
                            <div class="gallery-thumbs">
                                @foreach($motor->images as $img)
                                    <button class="gallery-thumb {{ $loop->first ? 'active' : '' }}" style="background-image:url('{{ asset($img->path) }}');" onclick="document.querySelector('.gallery-main').style.backgroundImage='url({{ asset($img->path) }})';this.parentElement.querySelectorAll('.gallery-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active');"></button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="gallery-main" style="background:var(--panel);display:flex;align-items:center;justify-content:center;color:var(--muted);">No Image</div>
                    @endif
                </div>

                <div class="motor-info">
                    @if($motor->brand)
                        <div class="motor-brand">{{ $motor->brand->name }}</div>
                    @endif
                    <h1 class="motor-name">{{ $motor->name }}</h1>
                    @if($motor->price)
                        <div class="motor-price">Rp {{ number_format($motor->price, 0, ',', '.') }}</div>
                    @endif
                    @if($motor->short_description)
                        <p class="motor-short-desc">{{ $motor->short_description }}</p>
                    @endif

                    @if($motor->colors->count())
                        <div class="motor-colors">
                            <div class="motor-colors-label">Varian Warna:</div>
                            <div class="motor-colors-list">
                                @foreach($motor->colors as $color)
                                    <div class="color-item">
                                        <span class="color-dot" style="background:{{ $color->color_code ?: '#666' }};" title="{{ $color->name }}"></span>
                                        <span class="color-name">{{ $color->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($motor->images360->count() >= 4)
                <div class="motor-360-section">
                    <h2 class="section-title-text" style="margin-bottom:20px;">Viewer 360&deg;</h2>
                    <div class="viewer-360" data-360-viewer>
                        <div class="viewer-360-canvas" data-360-canvas>
                            <img src="{{ asset($motor->images360->first()->path) }}" alt="360 view" id="viewer360Img">
                        </div>
                        <div class="viewer-360-controls">
                            <p class="viewer-hint">&#8592; Drag atau geser untuk memutar &rarr;</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($motor->specifications->count())
                <div class="motor-specs-section" style="margin-top:40px;">
                    <div style="max-width:700px;margin:0 auto;">
                        <h2 class="section-title-text" style="margin-bottom:20px;text-align:center;">Spesifikasi</h2>
                        <div class="spec-tabs">
                            @foreach($specGroups as $group => $specs)
                                <div class="spec-group">
                                    <h3 class="spec-group-title" style="font-size:16px;font-weight:600;color:var(--accent);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--line);">{{ $group }}</h3>
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
                <div style="margin-top:40px;background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:30px;">
                    <h2 class="section-title-text" style="margin-bottom:16px;">Deskripsi</h2>
                    <div style="color:var(--muted);line-height:1.8;font-size:14px;">{!! $motor->description !!}</div>
                </div>
            @endif

            @if(!empty($relatedMotors) && $relatedMotors->count())
                <div style="margin-top:50px;">
                    <div class="section-header">
                        <h2 class="section-title-text">Produk Terkait</h2>
                        <div class="section-line"></div>
                    </div>
                    <div class="grid grid-4">
                        @foreach($relatedMotors as $rm)
                            <a class="card" href="{{ route('buyer.motors.show', $rm->slug) }}">
                                <div class="card-media" style="background-image:url('{{ $rm->thumbnail_path ? asset($rm->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
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
        </div>
    </section>
@endsection

@push('head')
    <style>
        .motor-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }
        .gallery-main {
            width: 100%;
            height: 400px;
            border-radius: var(--radius);
            border: 1px solid var(--line);
        }
        .gallery-thumbs {
            display: flex;
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
        .gallery-thumb.active {
            border-color: var(--accent);
        }
        .motor-brand {
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 8px;
        }
        .motor-name {
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 700;
            margin-bottom: 12px;
        }
        .motor-price {
            font-size: 26px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 16px;
        }
        .motor-short-desc {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.7;
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
        .color-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .color-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--line);
            display: inline-block;
        }
        .color-name { font-size: 12px; color: var(--muted); }
        .viewer-360 {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
            user-select: none;
        }
        .viewer-360-canvas {
            max-width: 100%;
            overflow: hidden;
            border-radius: 8px;
            cursor: grab;
        }
        .viewer-360-canvas:active { cursor: grabbing; }
        .viewer-360-canvas img {
            width: 100%;
            max-width: 700px;
            pointer-events: none;
        }
        .viewer-360-controls {
            margin-top: 12px;
        }
        .viewer-hint {
            font-size: 12px;
            color: var(--muted);
        }
        .spec-tabs { display: flex; flex-direction: column; gap: 24px; }
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
        @media (max-width: 720px) {
            .motor-detail { grid-template-columns: 1fr; }
            .gallery-main { height: 280px; }
        }
    </style>
@endpush

@if($motor->images360->count() >= 4)
    @push('scripts')
        <script>
            (function() {
                const viewer = document.querySelector('[data-360-viewer]');
                if (!viewer) return;

                const canvas = viewer.querySelector('[data-360-canvas]');
                const img = document.getElementById('viewer360Img');
                const images = [
                    @foreach($motor->images360 as $img)
                        "{{ asset($img->path) }}",
                    @endforeach
                ];
                let currentFrame = 0;
                let dragging = false;
                let startX = 0;

                function setFrame(idx) {
                    currentFrame = ((idx % images.length) + images.length) % images.length;
                    img.src = images[currentFrame];
                }

                canvas.addEventListener('mousedown', (e) => { dragging = true; startX = e.clientX; e.preventDefault(); });
                canvas.addEventListener('touchstart', (e) => { dragging = true; startX = e.touches[0].clientX; });

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

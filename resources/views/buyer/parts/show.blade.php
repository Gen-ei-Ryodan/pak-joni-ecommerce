@extends('layouts.buyer')

@section('title', $part->name)

@section('content')
    <section class="section">
        <div class="container">
            @if (session('status'))
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                    {{ session('status') }}
                </div>
            @endif

            <div class="part-detail">
                {{-- Gallery --}}
                <div class="part-gallery">
                    <div class="gallery-main" id="galleryMain" style="background-image:url('{{ $part->thumbnail_path ? image_url($part->thumbnail_path) : '' }}');background-size:cover;background-position:center;">
                        @if(!$part->thumbnail_path)
                            <span style="color:var(--muted);display:flex;align-items:center;justify-content:center;height:100%;">No Image</span>
                        @endif
                    </div>

                    @php
                        $galleryImages = collect();
                        if ($part->thumbnail_path) $galleryImages->push(['url' => image_url($part->thumbnail_path)]);
                        foreach ($part->images as $img) $galleryImages->push(['url' => image_url($img->path)]);
                    @endphp

                    @if($galleryImages->count() > 1)
                        <div class="gallery-thumbs">
                            @foreach($galleryImages as $i => $item)
                                <button class="gallery-thumb {{ $i === 0 ? 'active' : '' }}" style="background-image:url('{{ $item['url'] }}');" onclick="document.getElementById('galleryMain').style.backgroundImage='url({{ $item['url'] }})';this.parentElement.querySelectorAll('.gallery-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active');"></button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Sidebar Info --}}
                <div class="part-info">
                    @if($part->items->count())
                        @php $firstItem = $part->items->first(); @endphp
                        @if($firstItem->brand)
                            <div class="part-brand">{{ $firstItem->brand->name }}</div>
                        @endif
                    @endif

                    @if($part->category)
                        <div class="part-cat">{{ $part->category->group }} / {{ $part->category->name }}</div>
                    @endif

                    <h1 class="part-name">{{ $part->name }}</h1>
                    <div class="part-sku">SKU: {{ $part->sku }}</div>

                    @php $defaultVariant = $part->defaultVariant ?? $part->variants->first(); @endphp
                    @if($defaultVariant)
                        <div class="part-price">Rp {{ number_format($defaultVariant->price, 0, ',', '.') }}</div>
                    @endif

                    @if($part->short_description)
                        <p class="part-short-desc">{{ $part->short_description }}</p>
                    @endif

                    {{-- Variant Selector --}}
                    @if($part->variants->count())
                        <div class="part-variants">
                            <div class="part-variants-label">Pilih Variant</div>
                            <div class="part-variants-list">
                                @foreach($part->variants as $v)
                                    <button type="button" class="variant-btn {{ $v->is_default ? 'active' : '' }}"
                                        data-variant-id="{{ $v->id }}"
                                        data-price="{{ $v->price }}"
                                        data-stock="{{ $v->stock }}"
                                        onclick="document.querySelectorAll('.variant-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');document.querySelector('[data-variant-id]').value=this.dataset.variantId;document.querySelector('[data-price-view]').textContent='Rp '+parseInt(this.dataset.price).toLocaleString('id-ID');var qty=document.querySelector('[data-qty]');var warn=document.querySelector('[data-stock-warn]');warn.style.display=parseInt(this.dataset.stock)<10?'block':'none';warn.textContent='Stok tersedia: '+this.dataset.stock;document.querySelector('[data-stock-info]').textContent='Stok: '+this.dataset.stock;">
                                        {{ $v->name }} <span class="variant-stock-hint">{{ $v->stock > 0 ? '('.$v->stock.')' : '(Habis)' }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <div class="price" data-price-view style="margin-top:8px;font-size:18px;font-weight:700;">Rp {{ number_format($defaultVariant->price, 0, ',', '.') }}</div>
                            <div style="font-size:13px;color:var(--muted);margin-top:4px;" data-stock-info>Stok: {{ $defaultVariant->stock }}</div>
                        </div>

                        <form method="post" action="{{ route('buyer.cart.store') }}" id="add-to-cart-form" style="margin-top:16px;display:grid;gap:10px;">
                            @csrf
                            <input type="hidden" name="itemable_type" value="part_variant">
                            <input type="hidden" name="itemable_id" value="{{ $defaultVariant->id }}" data-variant-id>
                            <input type="hidden" name="indent_mode" value="" data-indent-mode>
                            <div>
                                <label style="font-size:12px;color:var(--muted);margin-bottom:4px;display:block;">Qty</label>
                                <input type="number" name="quantity" value="1" min="1" data-qty required style="width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px;">
                                <div style="margin-top:4px;font-size:11px;color:#f87171;display:none;" data-stock-warn></div>
                            </div>
                            <button type="button" class="btn btn-primary" style="width:100%;" onclick="handleAddToCart()">
                                @if($part->stock_status === 'indent')
                                    Pre-Order (Indent) - DP 50%
                                @else
                                    Add to Cart
                                @endif
                            </button>
                        </form>

                        @if($part->stock_status === 'indent')
                            <div class="indent-warning">
                                Produk ini tersedia secara indent. DP 50% akan dibayarkan saat checkout.
                            </div>
                        @endif
                    @endif

                    {{-- Link ke item --}}
                    @if($part->items->count())
                        <a href="{{ route('buyer.motors.show', ['categoryType' => $part->items->first()->type->slug ?? 'motor', 'slug' => $part->items->first()->slug]) }}" 
                           class="motor-link-btn">
                            Lihat {{ $part->items->first()->name }} &rarr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- 360° Viewer --}}
            @php $images360 = $part->images360()->get(); @endphp
            @if($images360->count() >= 4)
                <div class="part-360-section">
                    <h2 class="section-title-text" style="margin-bottom:20px;">Foto 360&deg; Produk</h2>
                    <div class="viewer-360" data-360-viewer>
                        <div class="viewer-360-frame">
                            <img src="{{ image_url($images360->first()->image_path) }}" alt="360 view" id="viewer360Img" draggable="false">
                        </div>
                        <div class="viewer-360-controls">
                            <button type="button" id="rotateLeftBtn">&#9664;</button>
                            <span>&#8592; Drag / Scroll &#8594;</span>
                            <button type="button" id="rotateRightBtn">&#9654;</button>
                        </div>
                        <div class="viewer-360-auto">
                            <label>
                                <input type="checkbox" id="autoRotateCheck"> Auto
                            </label>
                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                    (function() {
                        const viewer = document.querySelector('[data-360-viewer]');
                        if (!viewer) return;
                        const img = document.getElementById('viewer360Img');
                        const images = [
                            @foreach($images360 as $i)
                                "{{ image_url($i->image_path) }}",
                            @endforeach
                        ];
                        let currentFrame = 0;
                        let dragging = false;
                        let startX = 0;
                        let autoRotateInterval = null;
                        const autoRotateCheck = document.getElementById('autoRotateCheck');

                        function setFrame(idx) {
                            currentFrame = ((idx % images.length) + images.length) % images.length;
                            img.src = images[currentFrame];
                        }

                        function startAutoRotate() {
                            stopAutoRotate();
                            autoRotateInterval = setInterval(() => setFrame(currentFrame + 1), 100);
                        }

                        function stopAutoRotate() {
                            if (autoRotateInterval) {
                                clearInterval(autoRotateInterval);
                                autoRotateInterval = null;
                            }
                        }

                        // Mouse drag
                        viewer.addEventListener('mousedown', (e) => { dragging = true; startX = e.clientX; stopAutoRotate(); if(autoRotateCheck) autoRotateCheck.checked = false; e.preventDefault(); });
                        viewer.addEventListener('touchstart', (e) => { dragging = true; startX = e.touches[0].clientX; stopAutoRotate(); if(autoRotateCheck) autoRotateCheck.checked = false; });

                        document.addEventListener('mousemove', (e) => {
                            if (!dragging) return;
                            const diff = e.clientX - startX;
                            if (Math.abs(diff) > 5) {
                                setFrame(currentFrame + (diff > 0 ? -1 : 1));
                                startX = e.clientX;
                            }
                        });

                        document.addEventListener('touchmove', (e) => {
                            if (!dragging) return;
                            const diff = e.touches[0].clientX - startX;
                            if (Math.abs(diff) > 5) {
                                setFrame(currentFrame + (diff > 0 ? -1 : 1));
                                startX = e.touches[0].clientX;
                            }
                        });

                        document.addEventListener('mouseup', () => { dragging = false; });
                        document.addEventListener('touchend', () => { dragging = false; });

                        // Scroll
                        viewer.addEventListener('wheel', (e) => {
                            e.preventDefault();
                            setFrame(currentFrame + (e.deltaY > 0 ? 1 : -1));
                        }, { passive: false });

                        // Arrow buttons
                        document.getElementById('rotateLeftBtn')?.addEventListener('click', () => setFrame(currentFrame - 1));
                        document.getElementById('rotateRightBtn')?.addEventListener('click', () => setFrame(currentFrame + 1));

                        // Auto rotate toggle
                        if (autoRotateCheck) {
                            autoRotateCheck.addEventListener('change', function() {
                                if (this.checked) startAutoRotate();
                                else stopAutoRotate();
                            });
                        }

                        // Keyboard
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'ArrowLeft') setFrame(currentFrame - 1);
                            if (e.key === 'ArrowRight') setFrame(currentFrame + 1);
                        });
                    })();
                </script>
                @endpush
            @endif

            {{-- Specifications --}}
            @if(!empty($specGroups) && $specGroups->count())
                <div class="part-specs-section">
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

            {{-- Description --}}
            @if($part->description)
                <div class="part-desc-section">
                    <h2 class="section-title-text" style="margin-bottom:16px;">Deskripsi</h2>
                    <div class="part-desc-content">{!! $part->description !!}</div>
                </div>
            @endif

            {{-- Compatible Products --}}
            @if(isset($allCompatibles) && $allCompatibles->count())
                <div class="compatible-section">
                    <h2 class="section-title-text" style="margin-bottom:16px;">Kompatibel Dengan</h2>

                    @php $compatItems = $allCompatibles->groupBy('type.name'); @endphp
                    @foreach($compatItems as $typeName => $items)
                        <div style="margin-bottom:12px;">
                            <h4 style="font-size:14px;font-weight:600;color:var(--muted);margin-bottom:8px;">{{ $typeName }}</h4>
                            <div class="compatible-list">
                                @foreach($items as $compatItem)
                                    <a href="{{ route('buyer.motors.show', ['categoryType' => $compatItem->type->slug, 'slug' => $compatItem->slug]) }}" class="compatible-tag">
                                        @if($compatItem->brand)
                                            <span class="compatible-brand">{{ $compatItem->brand->name }}</span>
                                        @endif
                                        {{ $compatItem->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Related Parts --}}
            @if(!empty($relatedParts) && $relatedParts->count())
                <div class="related-section">
                    <div class="section-header">
                        <h2 class="section-title-text">Sparepart Terkait</h2>
                        <div class="section-line"></div>
                    </div>
                    <div class="grid grid-4">
                        @foreach($relatedParts as $rp)
                            <a class="card" href="{{ route('buyer.parts.show', $rp->slug) }}">
                                <div class="card-media" style="background-image:url('{{ $rp->thumbnail_path ? image_url($rp->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                                <div class="card-body">
                                    @if($rp->category)
                                        <div class="card-meta">{{ $rp->category->group }} / {{ $rp->category->name }}</div>
                                    @endif
                                    <div class="card-title">{{ $rp->name }}</div>
                                    @if($rp->defaultVariant)
                                        <div class="price">Rp {{ number_format($rp->defaultVariant->price, 0, ',', '.') }}</div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Indent Choice Modal --}}
    <div id="indent-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="this.style.display='none'">
        <div class="panel" style="max-width:420px;width:90%;padding:20px;text-align:center;" onclick="event.stopPropagation()">
            <div style="font-weight:600;font-size:16px;margin-bottom:8px;">Stok Tidak Mencukupi</div>
            <div class="muted" style="margin-bottom:16px;" id="indent-modal-body">
                <!-- filled by JS -->
            </div>

            <div style="display:grid;gap:10px;">
                <button type="button" class="btn btn-primary" id="indent-split-btn" style="width:100%;text-align:left;padding:12px;">
                    <div style="font-weight:600;" id="indent-split-title"></div>
                    <div class="muted" style="font-size:11px;" id="indent-split-desc"></div>
                </button>

                <button type="button" class="btn" id="indent-full-btn" style="width:100%;text-align:left;padding:12px;border:1px solid #ca8a04;background:#fef3c7;color:#92400e;">
                    <div style="font-weight:600;" id="indent-full-title"></div>
                    <div style="font-size:11px;" id="indent-full-desc"></div>
                </button>

                <button type="button" class="btn" onclick="document.getElementById('indent-modal-overlay').style.display='none'" style="width:100%;padding:12px;color:#f87171;border-color:#f87171;">
                    Batal
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function handleAddToCart() {
    var form = document.getElementById('add-to-cart-form');
    var qtyInput = form.querySelector('[data-qty]');
    var qty = parseInt(qtyInput.value) || 1;
    var variantBtn = document.querySelector('.variant-btn.active');
    var stock = variantBtn ? parseInt(variantBtn.dataset.stock) : 9999;
    var indentModeInput = form.querySelector('[data-indent-mode]');

    if (qty <= stock || {{ $part->stock_status === 'indent' ? 'true' : 'false' }}) {
        // Stock cukup atau produk indent → langsung submit
        indentModeInput.value = '';
        form.submit();
        return;
    }

    // Qty > stock → show modal
    var overlay = document.getElementById('indent-modal-overlay');
    document.getElementById('indent-modal-body').innerHTML =
        'Anda ingin memesan <strong>' + qty + '</strong> item,<br>stok tersedia hanya <strong>' + stock + '</strong>.';

    document.getElementById('indent-split-title').textContent =
        'Split: Kirim ' + stock + ' + Indent ' + (qty - stock);
    document.getElementById('indent-split-desc').textContent =
        stock + ' ready dikirim sekarang, ' + (qty - stock) + ' indent (DP 50%). Sisa dibayar saat barang ready.';

    document.getElementById('indent-full-title').textContent =
        'Full Indent: ' + qty + ' tunggu semua';
    document.getElementById('indent-full-desc').textContent =
        'Semua ' + qty + ' item indent (DP 50%), dikirim bersama saat ready.';

    document.getElementById('indent-split-btn').onclick = function() {
        indentModeInput.value = 'split';
        form.submit();
    };

    document.getElementById('indent-full-btn').onclick = function() {
        indentModeInput.value = 'full';
        form.submit();
    };

    overlay.style.display = 'flex';
}
</script>
@endpush

@push('head')
    <style>
        .part-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: start;
        }
        .gallery-main {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: var(--radius);
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
        .part-brand {
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 8px;
        }
        .part-cat {
            font-size: 12px;
            background: var(--panel);
            border: 1px solid var(--line);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 14px;
            color: var(--muted);
            margin-bottom: 12px;
        }
        .part-name {
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 700;
            margin-bottom: 8px;
        }
        .part-sku {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 12px;
        }
        .part-price {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 16px;
        }
        .part-short-desc {
            font-size: 15px;
            color: var(--muted);
            line-height: 2;
        }
        .part-variants { margin-top: 28px; }
        .part-variants-label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .part-variants-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .variant-btn {
            padding: 10px 18px;
            border: 1.5px solid var(--line);
            border-radius: 12px;
            background: var(--panel);
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s;
        }
        .variant-btn.active {
            border-color: var(--accent);
            background: rgba(217,180,111,0.12);
            color: var(--accent);
        }
        .variant-btn:hover { border-color: var(--accent); }
        .variant-stock-hint {
            font-size: 10px;
            color: var(--muted);
            margin-left: 4px;
        }
        .indent-warning {
            margin-top: 8px;
            padding: 8px 12px;
            background: rgba(234,179,8,0.1);
            border: 1px solid rgba(234,179,8,0.3);
            border-radius: 8px;
            font-size: 13px;
            color: #ca8a04;
        }
        .motor-link-btn {
            display: block;
            margin-top: 16px;
            padding: 10px 16px;
            border: 1px dashed var(--line);
            border-radius: 10px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            text-decoration: none;
            transition: all .2s;
        }
        .motor-link-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .part-specs-section { margin-top: 40px; }
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

        .part-desc-section {
            margin-top: 40px;
            background: var(--panel);
            border: 2px solid var(--line);
            border-radius: var(--radius);
            padding: 30px;
        }
        .part-desc-content {
            color: var(--muted);
            line-height: 2;
            font-size: 14px;
        }

        .compatible-section {
            margin-top: 40px;
            background: var(--panel);
            border: 2px solid var(--line);
            border-radius: 16px;
            padding: 30px;
        }
        .compatible-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .compatible-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border: 1px solid var(--line);
            border-radius: 20px;
            font-size: 12px;
            color: var(--accent);
            text-decoration: none;
            transition: all .2s;
        }
        .compatible-tag:hover {
            border-color: var(--accent);
            background: rgba(217,180,111,0.08);
        }
        .compatible-brand {
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .part-360-section { margin-top: 40px; text-align: center; }
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
        .viewer-360-controls button {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            padding: 0 4px;
            line-height: 1;
        }
        .viewer-360-auto {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .viewer-360-auto label {
            background: rgba(0,0,0,0.65);
            color: #fff;
            padding: 4px 10px;
            border-radius: 14px;
            font-size: 11px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .viewer-360-auto input[type="checkbox"] { accent-color: #d9b46f; }
        .related-section { margin-top: 50px; }

        @media (max-width: 720px) {
            .part-detail { grid-template-columns: 1fr; }
        }
    </style>
@endpush

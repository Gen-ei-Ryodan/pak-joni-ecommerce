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
                    @if($part->motors->count())
                        @php $firstMotor = $part->motors->first(); @endphp
                        @if($firstMotor->brand)
                            <div class="part-brand">{{ $firstMotor->brand->name }}</div>
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
                                        onclick="document.querySelectorAll('.variant-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');document.querySelector('[data-variant-id]').value=this.dataset.variantId;document.querySelector('[data-price-view]').textContent='Rp '+parseInt(this.dataset.price).toLocaleString('id-ID');var max=parseInt(this.dataset.stock);var qty=document.querySelector('[data-qty]');qty.max=max;if(parseInt(qty.value)>max)qty.value=max;var warn=document.querySelector('[data-stock-warn]');warn.style.display=max<10?'block':'none';warn.textContent='Stok tersedia: '+this.dataset.stock;">
                                        {{ $v->name }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="price" data-price-view style="margin-top:8px;font-size:18px;font-weight:700;">Rp {{ number_format($defaultVariant->price, 0, ',', '.') }}</div>
                        </div>

                        <form method="post" action="{{ route('buyer.cart.store') }}" style="margin-top:16px;display:grid;gap:10px;">
                            @csrf
                            <input type="hidden" name="itemable_type" value="part_variant">
                            <input type="hidden" name="itemable_id" value="{{ $defaultVariant->id }}" data-variant-id>
                            <div>
                                <label style="font-size:12px;color:var(--muted);margin-bottom:4px;display:block;">Qty</label>
                                <input type="number" name="quantity" value="1" min="1" data-qty max="{{ $defaultVariant->stock }}" required style="width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px;">
                                <div style="margin-top:4px;font-size:11px;color:#f87171;display:none;" data-stock-warn></div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%;">
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

                    {{-- Link ke motor --}}
                    @if($part->motors->count())
                        <a href="{{ route('buyer.motors.show', $part->motors->first()->slug) }}" 
                           class="motor-link-btn">
                            Lihat Motor {{ $part->motors->first()->name }} &rarr;
                        </a>
                    @endif
                </div>
            </div>

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

            {{-- Compatible Motors --}}
            @if($part->motors->count())
                <div class="compatible-section">
                    <h2 class="section-title-text" style="margin-bottom:16px;">Kompatibel Dengan Motor</h2>
                    <div class="compatible-list">
                        @foreach($part->motors as $m)
                            <a href="{{ route('buyer.motors.show', $m->slug) }}" class="compatible-tag">
                                @if($m->brand)
                                    <span class="compatible-brand">{{ $m->brand->name }}</span>
                                @endif
                                {{ $m->name }}
                            </a>
                        @endforeach
                    </div>
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
@endsection

@push('head')
    <style>
        .part-detail {
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
        .part-brand {
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 8px;
        }
        .part-cat {
            font-size: 11px;
            background: var(--panel);
            border: 1px solid var(--line);
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            color: var(--muted);
            margin-bottom: 10px;
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
            font-size: 26px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 16px;
        }
        .part-short-desc {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.7;
        }
        .part-variants { margin-top: 24px; }
        .part-variants-label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .part-variants-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .variant-btn {
            padding: 8px 16px;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            background: var(--panel);
            color: var(--text);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s;
        }
        .variant-btn.active {
            border-color: var(--accent);
            background: rgba(217,180,111,0.1);
            color: var(--accent);
        }
        .variant-btn:hover { border-color: var(--accent); }
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

        .part-desc-section {
            margin-top: 40px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 30px;
        }
        .part-desc-content {
            color: var(--muted);
            line-height: 1.8;
            font-size: 14px;
        }

        .compatible-section {
            margin-top: 40px;
            background: var(--panel);
            border: 1px solid var(--line);
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

        .related-section { margin-top: 50px; }

        @media (max-width: 720px) {
            .part-detail { grid-template-columns: 1fr; }
            .gallery-main { height: 280px; }
        }
    </style>
@endpush

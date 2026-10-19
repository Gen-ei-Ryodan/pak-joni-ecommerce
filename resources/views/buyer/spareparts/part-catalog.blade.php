@extends('layouts.buyer')

@section('title', 'Part Katalog')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Part Katalog</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Pilih tipe motor untuk melihat katalog suku cadang lengkap dengan diagram dan exploded view.</p>
            </div>

            <div class="grid grid-3">
                @forelse($catalogs as $cat)
                    <div class="price-card">
                        @if($cat->image_path)
                            <div class="card-media" style="background-image:url('{{ image_url($cat->image_path) }}');background-size:contain;background-repeat:no-repeat;background-position:center;background-color:#f4f6fb;height:220px;"></div>
                        @elseif($cat->item && $cat->item->thumbnail_path)
                            <div class="card-media" style="background-image:url('{{ image_url($cat->item->thumbnail_path) }}');background-size:cover;background-position:center;height:220px;"></div>
                        @else
                            <div class="card-media" style="background:var(--panel);height:220px;display:flex;align-items:center;justify-content:center;color:var(--muted);">No Image</div>
                        @endif
                        <div class="card-body" style="text-align:center;">
                            <div class="card-title">{{ $cat->name }}</div>
                            @if($cat->brand)
                                <div class="card-meta">{{ $cat->brand->name }}</div>
                            @elseif($cat->item)
                                <div class="card-meta">{{ $cat->item->name }}</div>
                            @endif
                            @if(($cat->images_count ?? 0) > 0 && $cat->brand && $cat->slug)
                                <a href="{{ route('buyer.product.brand-pdf.gallery', ['categoryType' => 'sparepart', 'brand' => $cat->brand->slug, 'catalog' => $cat->slug]) }}"
                                   class="btn btn-accent" style="margin-top:12px;width:100%;justify-content:center;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    Lihat Gambar ({{ $cat->images_count }})
                                </a>
                            @endif
                            @if($cat->pdf_path)
                                <a href="{{ image_url($cat->pdf_path) }}" target="_blank" rel="noopener" class="btn" style="margin-top:8px;width:100%;justify-content:center;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    Download PDF
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada part katalog tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('head')
    <style>
        .price-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .price-card:hover {
            border-color: var(--accent);
            transform: translateY(-4px);
        }
    </style>
@endpush

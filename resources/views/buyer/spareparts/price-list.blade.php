@extends('layouts.buyer')

@section('title', 'Daftar Harga')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Daftar Harga Sepeda Motor</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Pilih tipe motor untuk melihat daftar harga suku cadang.</p>
            </div>

            <div class="grid grid-3">
                @forelse($priceLists as $pl)
                    <div class="price-card">
                        @if($pl->item && $pl->item->thumbnail_path)
                            <div class="card-media" style="background-image:url('{{ image_url($pl->item->thumbnail_path) }}');background-size:cover;background-position:center;height:220px;"></div>
                        @else
                            <div class="card-media" style="background:var(--panel);height:220px;display:flex;align-items:center;justify-content:center;color:var(--muted);">No Image</div>
                        @endif
                        <div class="card-body" style="text-align:center;">
                            <div class="card-title">{{ $pl->name }}</div>
                            @if($pl->item)
                                <div class="card-meta">{{ $pl->item->name }}</div>
                            @endif
                            <a href="{{ image_url($pl->pdf_path) }}" target="_blank" rel="noopener" class="btn btn-accent" style="margin-top:12px;width:100%;justify-content:center;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada daftar harga tersedia.</div>
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

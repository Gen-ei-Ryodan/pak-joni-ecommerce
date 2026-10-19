@extends('layouts.buyer')

@section('title', 'Katalog ' . ($catalog->name ?? 'Part') . ' - ' . strtoupper($selectedBrand->name ?? $brand))

@section('content')
    <section class="section" id="catalogTop">
        <div class="container">
            <div class="section-header center">
                <p style="margin-bottom:6px;">
                    <a href="{{ route('buyer.product.brand-pdf', ['categoryType' => $type->slug, 'brand' => $selectedBrand->slug]) }}"
                       style="color:var(--muted);font-size:13px;text-decoration:none;">
                        &larr; Kembali ke daftar model {{ strtoupper($selectedBrand->name) }}
                    </a>
                </p>
                <h2 class="section-title-text">Katalog {{ $catalog->name }}</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:640px;margin:12px auto 0;">
                    {{ $images->count() }} halaman katalog part {{ strtoupper($selectedBrand->name) }} {{ $catalog->name }}.
                </p>
                @if($catalog->pdf_path)
                    <p style="margin-top:16px;">
                        <a href="{{ image_url($catalog->pdf_path) }}" target="_blank" rel="noopener" class="btn btn-primary" style="padding:8px 16px;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            Download PDF Asli
                        </a>
                    </p>
                @endif
            </div>

            @if($images->isNotEmpty())
                <div class="catalog-gallery">
                    @foreach ($images as $image)
                        <figure class="catalog-page">
                            <img src="{{ image_url($image->path) }}"
                                 alt="Halaman {{ $loop->iteration }} - Katalog {{ $catalog->name }}"
                                 loading="lazy">
                            <figcaption>Halaman {{ $loop->iteration }} / {{ $images->count() }}</figcaption>
                        </figure>
                    @endforeach
                </div>

                <p style="text-align:center;margin-top:32px;">
                    <a href="#catalogTop" class="btn" data-no-transition>&uarr; Kembali ke atas</a>
                </p>
            @else
                <div class="empty-state" style="text-align:center;padding:60px 0;color:var(--muted);">
                    <p>Belum ada gambar untuk katalog ini.</p>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        .catalog-gallery {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            margin-top: 32px;
        }

        .catalog-page {
            width: 100%;
            max-width: 900px;
            margin: 0;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .05);
        }

        .catalog-page img {
            display: block;
            width: 100%;
            height: auto;
        }

        .catalog-page figcaption {
            padding: 8px 12px;
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            border-top: 1px solid var(--line);
        }
    </style>
@endpush

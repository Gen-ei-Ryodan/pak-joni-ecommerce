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
                    Klik gambar untuk memperbesar.
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
                <div class="catalog-grid">
                    @foreach ($images as $image)
                        <button type="button" class="catalog-thumb"
                                data-full="{{ image_url($image->path) }}"
                                data-index="{{ $loop->index }}"
                                aria-label="Perbesar halaman {{ $loop->iteration }}">
                            <img src="{{ image_url($image->path) }}"
                                 alt="Halaman {{ $loop->iteration }} - Katalog {{ $catalog->name }}"
                                 loading="lazy">
                            <span class="catalog-thumb-no">{{ $loop->iteration }}</span>
                        </button>
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

    {{-- Lightbox --}}
    <div class="catalog-lightbox" id="catalogLightbox" aria-hidden="true" role="dialog" aria-modal="true">
        <button type="button" class="clb-close" aria-label="Tutup">&times;</button>
        <button type="button" class="clb-nav clb-prev" aria-label="Halaman sebelumnya">&#10094;</button>
        <figure class="clb-figure">
            <img src="" alt="" id="clbImage">
            <figcaption id="clbCaption"></figcaption>
        </figure>
        <button type="button" class="clb-nav clb-next" aria-label="Halaman berikutnya">&#10095;</button>
    </div>
@endsection

@push('head')
    <style>
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
            margin-top: 32px;
        }

        .catalog-thumb {
            position: relative;
            display: block;
            width: 100%;
            padding: 0;
            margin: 0;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            cursor: zoom-in;
            aspect-ratio: 3 / 4;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .05);
            transition: box-shadow .25s ease, transform .25s ease;
        }

        .catalog-thumb:hover,
        .catalog-thumb:focus-visible {
            box-shadow: 0 10px 24px rgba(0, 0, 0, .12);
            transform: translateY(-2px);
            outline: none;
        }

        .catalog-thumb img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .3s ease;
        }

        .catalog-thumb:hover img {
            transform: scale(1.05);
        }

        .catalog-thumb-no {
            position: absolute;
            right: 6px;
            bottom: 6px;
            padding: 2px 8px;
            font-size: 11px;
            line-height: 1.6;
            color: #fff;
            background: rgba(0, 0, 0, .66);
            border-radius: 999px;
        }

        /* Lightbox */
        .catalog-lightbox {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 44px 16px;
            background: rgba(0, 0, 0, .88);
        }

        .catalog-lightbox.open {
            display: flex;
        }

        .clb-figure {
            margin: 0;
            max-width: 100%;
            max-height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #clbImage {
            display: block;
            max-width: 100%;
            max-height: 82vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
            background: #fff;
        }

        #clbCaption {
            margin-top: 12px;
            font-size: 13px;
            color: #fff;
            letter-spacing: .3px;
        }

        .clb-close {
            position: absolute;
            top: 14px;
            right: 18px;
            width: 42px;
            height: 42px;
            font-size: 28px;
            line-height: 1;
            color: #fff;
            background: rgba(255, 255, 255, .12);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: background .2s ease;
        }

        .clb-close:hover {
            background: rgba(255, 255, 255, .25);
        }

        .clb-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            font-size: 20px;
            color: #fff;
            background: rgba(255, 255, 255, .12);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: background .2s ease;
        }

        .clb-nav:hover {
            background: rgba(255, 255, 255, .25);
        }

        .clb-prev { left: 14px; }
        .clb-next { right: 14px; }

        @media (max-width: 600px) {
            .catalog-grid {
                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
                gap: 10px;
            }

            .clb-nav {
                width: 40px;
                height: 40px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var lb = document.getElementById('catalogLightbox');
            if (!lb) return;

            var thumbs = Array.prototype.slice.call(document.querySelectorAll('.catalog-thumb'));
            if (!thumbs.length) return;

            var imgEl = document.getElementById('clbImage');
            var capEl = document.getElementById('clbCaption');
            var total = thumbs.length;
            var current = 0;

            function show(i) {
                current = (i + total) % total;
                var thumb = thumbs[current];
                imgEl.src = thumb.getAttribute('data-full');
                imgEl.alt = (thumb.querySelector('img') || {}).alt || '';
                capEl.textContent = 'Halaman ' + (current + 1) + ' / ' + total;
                lb.classList.add('open');
                lb.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function hide() {
                lb.classList.remove('open');
                lb.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                imgEl.removeAttribute('src');
            }

            thumbs.forEach(function (thumb, i) {
                thumb.addEventListener('click', function () { show(i); });
            });

            lb.querySelector('.clb-close').addEventListener('click', hide);
            lb.querySelector('.clb-prev').addEventListener('click', function (e) {
                e.stopPropagation();
                show(current - 1);
            });
            lb.querySelector('.clb-next').addEventListener('click', function (e) {
                e.stopPropagation();
                show(current + 1);
            });

            // Klik area gelap (di luar gambar) untuk menutup
            lb.addEventListener('click', function (e) {
                if (e.target === lb) hide();
            });

            document.addEventListener('keydown', function (e) {
                if (!lb.classList.contains('open')) return;
                if (e.key === 'Escape') hide();
                else if (e.key === 'ArrowLeft') show(current - 1);
                else if (e.key === 'ArrowRight') show(current + 1);
            });
        })();
    </script>
@endpush

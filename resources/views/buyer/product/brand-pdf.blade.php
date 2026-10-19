@extends('layouts.buyer')

@section('title', 'Part Katalog - ' . strtoupper($selectedBrand->name ?? $brand))

@section('content')
    <section class="section section-category">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Part Katalog - {{ strtoupper($selectedBrand->name) }}</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:640px;margin:12px auto 0;">
                    Pilih model {{ strtoupper($selectedBrand->name) }} untuk melihat katalog part dalam bentuk gambar.
                </p>
            </div>

            <style>
                .catalog-model-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 22px;
                    margin-top: 40px;
                }

                @media (max-width: 980px) {
                    .catalog-model-grid { grid-template-columns: repeat(2, 1fr); }
                }

                @media (max-width: 640px) {
                    .catalog-model-grid { grid-template-columns: 1fr; }
                }

                .catalog-model-card {
                    display: flex;
                    flex-direction: column;
                    background: #fff;
                    border: 1px solid var(--line);
                    border-radius: 16px;
                    overflow: hidden;
                    text-decoration: none;
                    color: inherit;
                    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
                }

                .catalog-model-card:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 16px 40px rgba(0, 0, 0, .08);
                    border-color: var(--primary, #1d4ed8);
                }

                .catalog-model-cover {
                    position: relative;
                    height: 200px;
                    background: #f4f6fb;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                }

                .catalog-model-cover img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    object-position: top center;
                }

                .catalog-model-cover .catalog-empty-img {
                    color: var(--muted);
                    font-size: 13px;
                }

                .catalog-model-badge {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: rgba(17, 24, 39, .82);
                    color: #fff;
                    font-size: 11px;
                    font-weight: 600;
                    padding: 4px 10px;
                    border-radius: 999px;
                }

                .catalog-model-body {
                    padding: 16px 18px 18px;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }

                .catalog-model-title {
                    font-size: 16px;
                    font-weight: 700;
                }

                .catalog-model-actions {
                    display: flex;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .catalog-model-actions .btn { flex: 1; justify-content: center; white-space: nowrap; }
            </style>

            @if($catalogs->isNotEmpty())
                <div class="catalog-model-grid">
                    @foreach ($catalogs as $catalog)
                        @php
                            $cover = $catalog['cover'] ?? null;
                            $galleryUrl = $catalog['gallery_url'] ?? null;
                            $pdfUrl = !empty($catalog['pdf_path']) ? image_url($catalog['pdf_path']) : null;
                        @endphp
                        <div class="catalog-model-card">
                            <a class="catalog-model-cover"
                               href="{{ $galleryUrl ?? $pdfUrl ?? '#' }}"
                               @if(!$galleryUrl) target="_blank" rel="noopener" @endif>
                                @if($cover)
                                    <img src="{{ image_url($cover) }}" alt="{{ $catalog['name'] }}" loading="lazy">
                                @else
                                    <span class="catalog-empty-img">Belum ada gambar</span>
                                @endif
                                @if(($catalog['images_count'] ?? 0) > 0)
                                    <span class="catalog-model-badge">{{ $catalog['images_count'] }} halaman</span>
                                @endif
                            </a>
                            <div class="catalog-model-body">
                                <span class="catalog-model-title">{{ $catalog['name'] }}</span>
                                <div class="catalog-model-actions">
                                    @if($galleryUrl)
                                        <a href="{{ $galleryUrl }}" class="btn btn-primary" style="padding:8px 12px;font-size:12px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                            Lihat Gambar
                                        </a>
                                    @endif
                                    @if($pdfUrl)
                                        <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn" style="padding:8px 12px;font-size:12px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state" style="text-align:center;padding:60px 0;color:var(--muted);">
                    <p>Belum ada katalog part untuk merk ini.</p>
                    <p style="margin-top:12px;">
                        <a href="{{ route('buyer.product.choose', ['categoryType' => $type->slug]) }}" class="btn">Kembali ke Pilih Merk</a>
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection

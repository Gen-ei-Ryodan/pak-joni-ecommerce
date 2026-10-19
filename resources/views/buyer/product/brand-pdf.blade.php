@extends('layouts.buyer')

@section('title', 'Part Katalog - ' . strtoupper($selectedBrand->name ?? $brand))

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Part Katalog - {{ strtoupper($selectedBrand->name) }}</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:640px;margin:12px auto 0;">
                    Daftar katalog part {{ strtoupper($selectedBrand->name) }} per model.
                    Klik "Lihat Gambar" untuk melihat halaman katalog.
                </p>
            </div>

            @if($catalogs->isNotEmpty())
                <div class="pdf-catalog-list">
                    <table class="pdf-table">
                        <thead>
                            <tr>
                                <th class="col-no">No</th>
                                <th class="col-name">Nama Model</th>
                                <th class="col-type">Tipe</th>
                                <th class="col-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($catalogs as $index => $catalog)
                                @php
                                    $galleryUrl = $catalog['gallery_url'] ?? null;
                                    $pdfUrl = !empty($catalog['pdf_path']) ? image_url($catalog['pdf_path']) : null;
                                    $pages = (int) ($catalog['images_count'] ?? 0);
                                @endphp
                                <tr>
                                    <td class="col-no">{{ $index + 1 }}</td>
                                    <td class="col-name">
                                        @if($galleryUrl)
                                            <a href="{{ $galleryUrl }}" class="pdf-name-link">{{ $catalog['name'] }}</a>
                                        @else
                                            <span class="pdf-name-text">{{ $catalog['name'] }}</span>
                                        @endif
                                    </td>
                                    <td class="col-type">
                                        <span class="pdf-type-label">{{ $catalog['type'] ?? 'Part Catalog' }}</span>
                                        @if($pages > 0)
                                            <span class="pdf-pages">{{ $pages }} halaman</span>
                                        @endif
                                    </td>
                                    <td class="col-action">
                                        @if($galleryUrl)
                                            <a href="{{ $galleryUrl }}" class="btn btn-sm btn-primary" style="padding:6px 14px;font-size:12px;display:inline-flex;align-items:center;gap:6px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                                Lihat Gambar
                                            </a>
                                        @endif
                                        @if($pdfUrl)
                                            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-sm" style="padding:6px 14px;font-size:12px;display:inline-flex;align-items:center;gap:6px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                                Buka PDF
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <p style="text-align:center;color:var(--muted);">Belum ada katalog part untuk merk ini.</p>
                    <p style="text-align:center;margin-top:12px;">
                        <a href="{{ route('buyer.product.choose', ['categoryType' => $type->slug]) }}" class="btn">Kembali ke Pilih Merk</a>
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            font-size: 13px;
        }
        .pdf-table th,
        .pdf-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }
        .pdf-table th {
            background: var(--panel);
            border-bottom: 2px solid var(--accent);
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .4px;
            text-align: left;
            white-space: nowrap;
        }
        .pdf-table tbody tr:hover {
            background: var(--panel);
        }

        /* Lebar kolom: nama model mengisi ruang, tipe & aksi cukup selebar isinya */
        .pdf-table .col-no {
            width: 56px;
            color: var(--muted);
            font-weight: 600;
        }
        .pdf-table .col-name {
            width: auto;
        }
        .pdf-table .col-type {
            width: 1%;
            white-space: nowrap;
        }
        .pdf-table .col-action {
            width: 1%;
            white-space: nowrap;
            text-align: right;
        }

        .pdf-name-link {
            color: inherit;
            font-weight: 600;
            text-decoration: none;
        }
        .pdf-name-link:hover {
            color: var(--primary, #1d4ed8);
        }
        .pdf-name-text {
            font-weight: 600;
        }
        .pdf-type-label {
            color: var(--muted);
        }
        .pdf-pages {
            display: inline-block;
            margin-left: 8px;
            padding: 2px 9px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            background: rgba(17, 24, 39, .72);
            border-radius: 999px;
            vertical-align: middle;
        }
        .col-action .btn + .btn {
            margin-left: 8px;
        }

        @media (max-width: 720px) {
            .pdf-catalog-list {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .pdf-table {
                min-width: 640px;
            }
        }
    </style>
@endpush

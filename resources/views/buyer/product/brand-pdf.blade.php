@extends('layouts.buyer')

@section('title', 'Katalog PDF - ' . strtoupper($selectedBrand->name ?? $brand))

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Katalog PDF - {{ strtoupper($selectedBrand->name) }}</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">
                    Daftar katalog PDF resmi untuk merk {{ strtoupper($selectedBrand->name) }}
                </p>
            </div>

            @if($pdfCatalogs->isNotEmpty())
                <div class="pdf-catalog-list">
                    <table class="pdf-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama File</th>
                                <th>Tipe</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pdfCatalogs as $index => $pdf)
                                @php
                                    $pdfName = is_array($pdf) ? ($pdf['name'] ?? 'Catalog Part') : ($pdf->name ?? 'Catalog Part');
                                    $pdfType = is_array($pdf) ? ($pdf['type'] ?? 'Part Catalog') : ($pdf->part_type ?? 'Part');
                                    $pdfPath = is_array($pdf) ? ($pdf['path'] ?? null) : ($pdf->path ?? $pdf->pdf_path ?? null);
                                @endphp
                                <tr>
                                    <td class="pdf-no">{{ $index + 1 }}</td>
                                    <td class="pdf-name">{{ $pdfName }}</td>
                                    <td class="pdf-type">{{ $pdfType }}</td>
                                    <td class="pdf-action">
                                        @if($pdfPath)
                                            <a href="{{ image_url($pdfPath) }}" target="_blank" rel="noopener"
                                               class="btn btn-sm btn-primary" style="padding:6px 14px;font-size:12px;display:inline-flex;align-items:center;gap:6px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                Buka PDF
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="pdf-no" colspan="4">Belum ada katalog PDF untuk merk ini.</td>
                                </tr>
                            @endforelse>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <p style="text-align:center;color:var(--muted);">Belum ada katalog PDF untuk merk ini.</p>
                    <p style="text-align:center;margin-top:12px;color:var(--muted);">
                        <a href="{{ route('buyer.product.choose', ['categoryType' => $type->slug]) }}"
                           class="btn">Kembali ke Pilih Merk</a>
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
            margin-top: 20px;
            font-size: 13px;
        }
        .pdf-table thead {
            background: var(--panel);
            border-bottom: 2px solid var(--accent);
        }
        .pdf-table th,
        .pdf-table td {
            padding: 12px 8px;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }
        .pdf-table th {
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
        }
        .pdf-table-no {
            width: 40px;
            color: var(--muted);
            font-weight: 600;
        }
        .pdf-name {
            flex: 1;
        }
        .pdf-type {
            text-align: center;
            color: var(--muted);
        }
        .pdf-action {
            text-align: center;
        }
        .empty-state {
            text-align: center;
            padding: 60px 0;
            color: var(--muted);
        }
    </style>
@endpush
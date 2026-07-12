@extends('layouts.buyer')

@section('title', 'Katalog Sparepart')

@section('content')
    @php $selectedBrand = $brand ?? $brandSlug ?? ''; @endphp
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Katalog Sparepart</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">
                    Cari dan temukan sparepart asli untuk motor, ATV, dan kendaraan Anda.
                </p>
            </div>

            {{-- Search & Filter Form --}}
            <form method="get" style="margin-bottom:24px;">
                <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
                    <input class="form-input" name="q" value="{{ $q }}" placeholder="Cari nama sparepart / SKU..." style="flex:1;min-width:200px;padding:10px 14px;border:1px solid var(--line);border-radius:10px;background:var(--panel);color:var(--text);font-family:inherit;font-size:13px;">

                    <select name="brand" style="padding:10px 14px;border:1px solid var(--line);border-radius:10px;background:var(--panel);color:var(--text);font-family:inherit;font-size:13px;min-width:140px;">
                        <option value="">Semua Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->slug }}" {{ $selectedBrand === $b->slug ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>

                    <select name="group" style="padding:10px 14px;border:1px solid var(--line);border-radius:10px;background:var(--panel);color:var(--text);font-family:inherit;font-size:13px;min-width:140px;">
                        <option value="">Semua Golongan</option>
                        @foreach($groups as $g)
                            <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary" style="padding:10px 24px;border-radius:10px;">Filter</button>
                    @if($q || $selectedBrand || $category || $group)
                        <a href="{{ route('buyer.category-brand', ['categoryType' => 'sparepart', 'brand' => 'all']) }}" class="btn btn-outline" style="padding:10px 24px;border-radius:10px;">Reset</a>
                    @endif
                </div>
            </form>

            {{-- Active Filters --}}
            @if($q || $selectedBrand || $category || $group)
                <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:20px;">
                    <span style="font-size:12px;color:var(--muted);padding:4px 0;">Filter aktif:</span>
                    @if($q)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;font-size:11px;background:rgba(217,180,111,0.1);color:var(--accent);border-radius:20px;border:1px solid var(--accent);">
                            Pencarian: "{{ $q }}"
                            <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" style="text-decoration:none;color:var(--accent);font-weight:700;">&times;</a>
                        </span>
                    @endif
                    @if($selectedBrand)
                        @php $brandName = $brands->firstWhere('slug', $selectedBrand)?->name ?? $selectedBrand; @endphp
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;font-size:11px;background:rgba(217,180,111,0.1);color:var(--accent);border-radius:20px;border:1px solid var(--accent);">
                            Brand: {{ $brandName }}
                            <a href="{{ request()->fullUrlWithQuery(['brand' => null]) }}" style="text-decoration:none;color:var(--accent);font-weight:700;">&times;</a>
                        </span>
                    @endif
                    @if($group)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;font-size:11px;background:rgba(217,180,111,0.1);color:var(--accent);border-radius:20px;border:1px solid var(--accent);">
                            Golongan: {{ $group }}
                            <a href="{{ request()->fullUrlWithQuery(['group' => null]) }}" style="text-decoration:none;color:var(--accent);font-weight:700;">&times;</a>
                        </span>
                    @endif
                    @if($category)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;font-size:11px;background:rgba(217,180,111,0.1);color:var(--accent);border-radius:20px;border:1px solid var(--accent);">
                            Kategori: {{ $selectedCategoryName }}
                            <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" style="text-decoration:none;color:var(--accent);font-weight:700;">&times;</a>
                        </span>
                    @endif
                </div>
            @endif

            {{-- Results Count --}}
            <div style="text-align:center;font-size:13px;color:var(--muted);margin-bottom:20px;">
                Menampilkan {{ $parts->firstItem() ?? 0 }} - {{ $parts->lastItem() ?? 0 }} dari {{ $parts->total() }} sparepart
            </div>

            {{-- Parts Grid --}}
            <div class="grid grid-3">
                @forelse ($parts as $p)
                    <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $p->thumbnail_path ? image_url($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        <div class="card-body">
                            @php $pBrand = $p->items()->first()?->brand; @endphp
                            @if($pBrand)
                                <div class="card-meta">{{ $pBrand->name }}</div>
                            @endif
                            <div class="card-title">{{ $p->name }}</div>
                            @if($p->category)
                                <div class="card-meta" style="font-size:10px;text-transform:none;letter-spacing:0;margin-top:2px;">
                                    {{ $p->category->group }} &rsaquo; {{ $p->category->name }}
                                </div>
                            @endif
                            @if($p->defaultVariant)
                                <div class="price">Rp {{ number_format($p->defaultVariant->price, 0, ',', '.') }}</div>
                            @elseif($p->base_price)
                                <div class="price">Rp {{ number_format($p->base_price, 0, ',', '.') }}</div>
                            @endif
                            @if($p->stock_status === 'indent')
                                <span class="stock-badge indent">Indent</span>
                            @elseif($p->stock_status === 'ready')
                                <span class="stock-badge ready">Ready Stock ({{ $p->totalStock() }})</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty-state" style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--muted);">
                        Tidak ada sparepart ditemukan dengan filter tersebut.
                    </div>
                @endforelse
            </div>

            @if(method_exists($parts, 'links'))
                <div style="margin-top:30px;">{{ $parts->links('pagination.simple-dark') }}</div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
        }
        select {
            cursor: pointer;
        }
        select:focus {
            outline: none;
            border-color: var(--accent);
        }
        .btn-primary {
            background: linear-gradient(135deg, #b8860b, #9a6f09);
            color: #fff;
            border: none;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .btn-outline {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--line);
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-outline:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        .stock-badge {
            display: inline-block;
            margin-top: 4px;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .stock-badge.ready {
            background: rgba(34,197,94,0.1);
            color: #22c55e;
        }
        .stock-badge.indent {
            background: #fef3c7;
            color: #92400e;
        }
        .card {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .card:hover {
            border-color: var(--accent);
        }
        .card-media {
            background-size: cover;
            background-position: center;
        }
        .card-body {
            padding: 14px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .card-meta {
            font-size: 11px;
            color: var(--muted);
            letter-spacing: 0.5px;
        }
        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }
        .price {
            font-size: 15px;
            font-weight: 700;
            color: var(--accent);
            margin-top: auto;
        }
    </style>
@endpush

@extends('layouts.buyer')

@section('title', $brandModel ? $brandModel->name . ' - ' . $type->name : $type->name)

@section('content')
    <section class="section section-category">
        <div class="container">
            {{-- Header --}}
            <div class="section-header center">
                @php
                    $selectedCategoryModel = $selectedCategory ? $categories->firstWhere('slug', $selectedCategory) : null;
                @endphp
                <h2 class="section-title-text">
                    {{ $brandModel?->name ?? ($selectedCategoryModel?->name ? $type->name . ' ' . $selectedCategoryModel->name : 'Semua ' . $type->name) }}
                </h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">
                    @if($brandModel)
                        Jelajahi koleksi {{ $type->name }} {{ $brandModel->name }} dan sparepart pendukungnya.
                    @elseif($selectedCategoryModel)
                        Jelajahi {{ strtolower($type->name) }} kategori <strong>{{ $selectedCategoryModel->name }}</strong>{{ $type->slug === 'sparepart' && $selectedCategoryModel->group ? ' (' . $selectedCategoryModel->group . ')' : '' }}.
                    @else
                        Jelajahi seluruh koleksi {{ $type->name }} dari berbagai brand.
                    @endif
                </p>
            </div>

            {{-- Category Filter --}}
            @if($categories->isNotEmpty())
                <div class="brand-filter" style="margin-bottom:24px;" id="categoryFilter">
                    <a href="{{ route('buyer.category-brand', ['categoryType' => $type->slug, 'brand' => $brandModel?->slug ?? 'all']) }}"
                       class="filter-tag filter-tag-sm {{ !$selectedCategory ? 'active' : '' }}"
                       data-category="">Semua Kategori</a>
                    @foreach($categories as $cat)
                        <a href="{{ route('buyer.category-brand', ['categoryType' => $type->slug, 'brand' => $brandModel?->slug ?? 'all', 'category' => $cat->slug]) }}"
                           class="filter-tag filter-tag-sm {{ $selectedCategory === $cat->slug ? 'active' : '' }}"
                           data-category="{{ $cat->slug }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            {{-- Product Grid --}}
            <div style="margin-bottom:32px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <h3 style="font-size:20px;font-weight:700;">{{ $type->name }}</h3>
                </div>

                <div class="grid grid-3" id="productGrid">
                    @include('buyer.partials.category-brand-products')
                </div>

                <div id="productPagination" style="margin-top:30px;">
                    @if(method_exists($items, 'links'))
                        {{ $items->links('pagination.simple-dark') }}
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('head')
    <style>
        .section-category {
            background: linear-gradient(160deg, #fff 0%, #eef2ff 30%, #dbeafe 70%, #fff 100%);
        }
        .brand-filter {
            display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:16px;
        }
        .filter-tag {
            display: inline-flex;
            padding: 8px 18px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.6px;
            text-decoration: none;
            color: var(--muted);
            border: 1px solid var(--line);
            border-radius: 20px;
            transition: all 0.2s ease;
        }
        .filter-tag:hover, .filter-tag.active {
            border-color: var(--accent);
            color: var(--accent);
        }
        .filter-tag.active {
            background: rgba(217, 180, 111, 0.1);
        }
        .filter-tag-sm {
            padding: 6px 14px;
            font-size: 11px;
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
        .stock-badge.otr {
            background: #0055DA;
            color: #fff;
            margin-left: 4px;
        }
        .empty-state {
            text-align: center;
            grid-column: 1/-1;
            padding: 60px 0;
            color: var(--muted);
        }
        .motor-card { display: flex; flex-direction: column; overflow: hidden; }
        .card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-top: 1px solid var(--line);
        }
        .card-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            color: var(--muted);
            transition: all .2s;
            letter-spacing: 0.3px;
        }
        .card-action-btn.primary {
            color: var(--accent);
            border-right: 1px solid var(--line);
        }
        .card-action-btn:hover {
            background: rgba(217,180,111,0.08);
            color: var(--accent);
        }
        #productGrid.is-loading {
            opacity: 0.5;
            pointer-events: none;
            transition: opacity 0.2s;
        }
    </style>
@endpush

@push('scripts')
<script>
(function() {
    const filter = document.getElementById('categoryFilter');
    const grid = document.getElementById('productGrid');
    const pagination = document.getElementById('productPagination');
    if (!filter || !grid) return;

    const baseUrl = '{{ route("buyer.category-brand", ["categoryType" => $type->slug, "brand" => $brandModel?->slug ?? "all"]) }}';

    filter.addEventListener('click', function(e) {
        const tag = e.target.closest('.filter-tag');
        if (!tag) return;
        e.preventDefault();

        const category = tag.dataset.category;
        const url = category ? baseUrl + '?category=' + encodeURIComponent(category) : baseUrl;

        // Update active state
        filter.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
        tag.classList.add('active');

        // Show loading
        grid.classList.add('is-loading');

        // Fetch filtered products
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            grid.innerHTML = data.html;
            grid.classList.remove('is-loading');

            if (data.pagination) {
                pagination.innerHTML = data.pagination;
                pagination.style.marginTop = '30px';
            } else {
                pagination.innerHTML = '';
            }

            // Update URL without reload
            history.pushState({ category: data.selectedCategory }, '', url);
        })
        .catch(() => {
            grid.classList.remove('is-loading');
        });
    });

    // Handle browser back/forward
    window.addEventListener('popstate', function() {
        const params = new URLSearchParams(window.location.search);
        const category = params.get('category') || '';
        const url = category ? baseUrl + '?category=' + encodeURIComponent(category) : baseUrl;

        grid.classList.add('is-loading');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            grid.innerHTML = data.html;
            grid.classList.remove('is-loading');

            if (data.pagination) {
                pagination.innerHTML = data.pagination;
            } else {
                pagination.innerHTML = '';
            }

            // Update active state
            filter.querySelectorAll('.filter-tag').forEach(t => {
                t.classList.toggle('active', t.dataset.category === category);
            });
        })
        .catch(() => {
            grid.classList.remove('is-loading');
        });
    });
})();
</script>
@endpush

@extends('layouts.buyer')

@section('title', 'Parts')

@push('head')
    <style>
        .searchable-select-wrap {
            position: relative;
            min-width: 240px;
        }

        .searchable-select-wrap .input {
            width: 100%;
            cursor: text;
        }

        .searchable-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: rgba(16, 20, 26, 0.98);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 6px;
            max-height: 220px;
            overflow-y: auto;
            display: none;
            z-index: 30;
        }

        .searchable-dropdown.open {
            display: block;
        }

        .searchable-option {
            padding: 10px 12px;
            font-size: 13px;
            color: var(--muted);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.1s ease;
        }

        .searchable-option:hover,
        .searchable-option.highlighted {
            color: var(--text);
            background: rgba(255, 255, 255, 0.04);
        }

        .searchable-option.selected {
            color: var(--text);
            font-weight: 500;
        }

        .searchable-option.hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Parts</div>
                <a class="btn" href="{{ route('buyer.products') }}">Products</a>
            </div>

            <div style="height:12px;"></div>

            <form method="get" class="filters">
                <input class="input" name="q" value="{{ $q }}" placeholder="Search part / SKU...">
                <div class="searchable-select-wrap" data-searchable-select>
                    <input class="input" type="text" placeholder="All Categories" value="{{ $selectedCategoryName ?? '' }}" autocomplete="off" data-searchable-input>
                    <input type="hidden" name="category" value="{{ $category }}" data-searchable-hidden>
                    <div class="searchable-dropdown" data-searchable-dropdown>
                        <div class="searchable-option" data-value="">All Categories</div>
                        @foreach ($categories as $cat)
                            <div class="searchable-option" data-value="{{ $cat->slug }}">{{ $cat->group }} — {{ $cat->name }}</div>
                        @endforeach
                    </div>
                </div>
                <button class="btn" type="submit">Filter</button>
            </form>

            <div style="height:14px;"></div>

            <div class="grid grid-3">
                @forelse ($parts as $p)
                    <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $p->thumbnail_path ? asset($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                        <div class="card-body">
                            <div class="card-title">{{ $p->name }}</div>
                            <div class="card-meta">{{ $p->category?->group }} — {{ $p->category?->name }}</div>
                            <div class="price">{{ $p->defaultVariant ? number_format((float) $p->defaultVariant->price, 2, '.', ',') : number_format((float) $p->base_price, 2, '.', ',') }}</div>
                        </div>
                    </a>
                @empty
                    <div class="muted">No parts yet.</div>
                @endforelse
            </div>

            <div style="height:12px;"></div>
            {{ $parts->links() }}
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var wrap = document.querySelector('[data-searchable-select]');
            if (!wrap) return;

            var input = wrap.querySelector('[data-searchable-input]');
            var hidden = wrap.querySelector('[data-searchable-hidden]');
            var dropdown = wrap.querySelector('[data-searchable-dropdown]');
            var options = Array.from(dropdown.querySelectorAll('.searchable-option'));

            function filterOptions(query) {
                var lower = query.toLowerCase();
                var hasVisible = false;
                options.forEach(function (opt) {
                    var text = opt.textContent.toLowerCase();
                    var match = text.indexOf(lower) !== -1;
                    opt.classList.toggle('hidden', !match);
                    if (match) hasVisible = true;
                });
                dropdown.classList.toggle('open', hasVisible || query.length > 0);
                if (!hasVisible && query.length === 0) {
                    dropdown.classList.remove('open');
                }
            }

            function selectOption(opt) {
                options.forEach(function (o) { o.classList.remove('selected'); });
                opt.classList.add('selected');
                input.value = opt.textContent;
                hidden.value = opt.getAttribute('data-value');
                dropdown.classList.remove('open');
            }

            input.addEventListener('focus', function () {
                filterOptions(input.value);
            });

            input.addEventListener('input', function () {
                filterOptions(input.value);
            });

            document.addEventListener('click', function (e) {
                if (!wrap.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });

            options.forEach(function (opt) {
                opt.addEventListener('click', function () {
                    selectOption(opt);
                });
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    dropdown.classList.remove('open');
                }
                if (e.key === 'Enter') {
                    var visible = options.filter(function (o) { return !o.classList.contains('hidden'); });
                    if (visible.length === 1) {
                        selectOption(visible[0]);
                    }
                }
            });

            var initialValue = '{{ $category }}';
            if (initialValue) {
                options.forEach(function (opt) {
                    if (opt.getAttribute('data-value') === initialValue) {
                        opt.classList.add('selected');
                    }
                });
            }
        })();
    </script>
@endpush

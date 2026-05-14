@extends('layouts.buyer')

@section('title', $part->name)

@section('content')
    <section class="section">
        <div class="container">
            @if (session('status'))
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(255,77,77,0.35);background:rgba(255,77,77,0.08);">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $part->name }}</div>
                    <div class="muted" style="margin-top:6px;">SKU: {{ $part->sku }} — {{ $part->category?->group }} / {{ $part->category?->name }}</div>
                </div>
                <a class="btn" href="{{ route('buyer.parts.index') }}">Kembali</a>
            </div>

            <div style="height:16px;"></div>

            <div style="display:grid;grid-template-columns:1fr 420px;gap:16px;">
                <div class="panel" style="padding:12px;">
                    <div style="aspect-ratio:16/10;border-radius:12px;border:1px solid var(--line);overflow:hidden;background:rgba(255,255,255,0.03);">
                        @if ($part->thumbnail_path)
                            <img src="{{ asset($part->thumbnail_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @endif
                    </div>

                    @if ($part->images->count())
                        <div style="height:10px;"></div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            @foreach ($part->images as $img)
                                <img src="{{ asset($img->path) }}" alt="" style="width:120px;border-radius:12px;border:1px solid var(--line);">
                            @endforeach
                        </div>
                    @endif

                    @if ($part->description)
                        <div style="height:16px;"></div>
                        <div style="font-weight:600;">Description</div>
                        <div style="height:8px;"></div>
                        <div class="muted" style="line-height:1.8;">{!! nl2br(e($part->description)) !!}</div>
                    @endif

                    @if ($part->specification)
                        <div style="height:16px;"></div>
                        <div style="font-weight:600;">Specification</div>
                        <div style="height:8px;"></div>
                        <div class="muted" style="line-height:1.8;">{!! nl2br(e($part->specification)) !!}</div>
                    @endif

                    @if ($part->motors->count())
                        <div style="height:16px;"></div>
                        <div style="font-weight:600;">Compatible Motor</div>
                        <div style="height:8px;"></div>
                        <div class="muted">{{ $part->motors->pluck('name')->join(', ') }}</div>
                    @endif
                </div>

                <div class="panel" style="padding:14px;">
                    <div style="display:grid;gap:12px;">
                        <div>
                            <div style="font-weight:600;">Pilih Variant</div>
                            <div style="height:8px;"></div>
                            <select class="select" style="width:100%;" data-variant-select>
                                @foreach ($part->variants as $v)
                                    <option value="{{ $v->id }}" data-price="{{ $v->price }}" data-stock="{{ $v->stock }}" @selected($v->is_default)>
                                        {{ $v->name }} — {{ number_format((float) $v->price, 2, '.', ',') }} (stock: {{ $v->stock }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="price" data-price-view></div>
                        </div>

                        <form method="post" action="{{ route('buyer.cart.store') }}" style="display:grid;gap:12px;">
                            @csrf
                            <input type="hidden" name="variant_id" data-variant-id>
                            <div class="field">
                                <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Qty</label>
                                <input class="input" style="width:100%;min-width:0;" name="quantity" type="number" value="1" min="1" max="99" required>
                            </div>
                            <button class="btn btn-primary" type="submit">Add to Cart</button>
                        </form>

                        <form method="post" action="{{ route('buyer.wishlist.toggle', $part) }}">
                            @csrf
                            <button class="btn" type="submit">Toggle Wishlist</button>
                        </form>

                        <a class="btn" href="{{ route('buyer.cart.index') }}">Go to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@push('scripts')
    <script>
        (function () {
            const select = document.querySelector('[data-variant-select]');
            const idInput = document.querySelector('[data-variant-id]');
            const priceView = document.querySelector('[data-price-view]');
            if (!select || !idInput || !priceView) return;

            function sync() {
                const opt = select.options[select.selectedIndex];
                idInput.value = opt.value;
                const price = opt.getAttribute('data-price') || '0';
                const stock = opt.getAttribute('data-stock') || '0';
                priceView.textContent = `Price: ${price} | Stock: ${stock}`;
            }

            select.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
@endsection

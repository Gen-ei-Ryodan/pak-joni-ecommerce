@extends('layouts.buyer')

@section('title', $part->name)

@push('head')
<style>
.rich-text p { margin-bottom: 8px; }
.rich-text ul, .rich-text ol { padding-left: 22px; margin-bottom: 8px; }
.rich-text li { margin-bottom: 4px; }
.rich-text strong { font-weight: 700; }
.rich-text em { font-style: italic; }
.rich-text a { color: var(--accent); text-decoration: underline; }
.rich-text blockquote {
    border-left: 3px solid var(--line);
    padding-left: 12px;
    margin: 8px 0;
    color: var(--muted);
}
.rich-text h1, .rich-text h2, .rich-text h3 { margin-top: 16px; margin-bottom: 8px; font-weight: 600; }
.rich-text img { max-width: 100%; border-radius: 8px; margin: 8px 0; }
.gallery-main {
    aspect-ratio: 1/1;
    border-radius: 12px;
    border: 1px solid var(--line);
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: opacity .15s ease;
}
.gallery-thumbs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: thin;
}
.gallery-thumbs::-webkit-scrollbar {
    height: 4px;
}
.gallery-thumbs::-webkit-scrollbar-thumb {
    background: var(--line);
    border-radius: 4px;
}
.gallery-thumb {
    flex: 0 0 auto;
    width: 72px;
    height: 72px;
    border-radius: 10px;
    border: 2px solid transparent;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .2s, opacity .2s;
    background: #f0f0f0;
}
.gallery-thumb:hover {
    opacity: .75;
}
.gallery-thumb.active {
    border-color: #d9b46f;
}
.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
@endpush

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
                    <div class="gallery-main">
                        @php
                            $allImages = collect();
                            if ($part->thumbnail_path) {
                                $allImages->push(['url' => image_url($part->thumbnail_path), 'label' => 'Thumbnail']);
                            }
                            foreach ($part->images as $img) {
                                $allImages->push(['url' => image_url($img->path), 'label' => 'Gallery']);
                            }
                        @endphp
                        @if ($allImages->count())
                            <img id="mainPreview" src="{{ $allImages->first()['url'] }}" alt="">
                        @endif
                    </div>

                    @if ($allImages->count() > 1)
                        <div style="height:10px;"></div>
                        <div class="gallery-thumbs" id="galleryThumbs">
                            @foreach ($allImages as $i => $item)
                                <div class="gallery-thumb {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" data-src="{{ $item['url'] }}">
                                    <img src="{{ $item['url'] }}" alt="">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($part->description)
                        <div style="height:16px;"></div>
                        <div style="font-weight:600;">Description</div>
                        <div style="height:8px;"></div>
                        <div class="muted rich-text" style="line-height:1.8;">{!! $part->description !!}</div>
                    @endif

                    @if ($part->specification)
                        <div style="height:16px;"></div>
                        <div style="font-weight:600;">Specification</div>
                        <div style="height:8px;"></div>
                        <div class="muted rich-text" style="line-height:1.8;">{!! $part->specification !!}</div>
                    @endif

                    @if ($part->motors->count())
                        <div style="height:16px;"></div>
                        <div style="font-weight:600;">Compatible Motor</div>
                        <div style="height:8px;"></div>
                        <div class="muted">{{ $part->motors->pluck('name')->join(', ') }}</div>
                    @endif
                </div>

                        <div class="panel" style="padding:14px;">
                            <div id="cart-notification" style="display:none;padding:10px 12px;margin-bottom:12px;border-radius:12px;border:1px solid rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);"></div>

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

                                <form style="display:grid;gap:12px;" data-add-to-cart>
                                    @csrf
                                    <input type="hidden" name="variant_id" data-variant-id>
                                    <div class="field">
                                        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Qty</label>
                                        <input class="input" style="width:100%;min-width:0;" name="quantity" type="number" value="1" min="1" max="99" required data-qty-input>
                                        <div style="margin-top:4px;font-size:11px;color:#f87171;display:none;" data-stock-warning></div>
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
            var thumbs = document.querySelectorAll('.gallery-thumb');
            var mainImg = document.getElementById('mainPreview');

            if (thumbs.length && mainImg) {
                thumbs.forEach(function (el) {
                    el.addEventListener('click', function () {
                        thumbs.forEach(function (t) { t.classList.remove('active'); });
                        el.classList.add('active');
                        mainImg.style.opacity = '0';
                        setTimeout(function () {
                            mainImg.src = el.getAttribute('data-src');
                            mainImg.style.opacity = '1';
                        }, 100);
                    });
                });
            }
        })();
    </script>
    <script>
        (function () {
            const select = document.querySelector('[data-variant-select]');
            const idInput = document.querySelector('[data-variant-id]');
            const priceView = document.querySelector('[data-price-view]');
            const qtyInput = document.querySelector('[data-qty-input]');
            const stockWarning = document.querySelector('[data-stock-warning]');
            const form = document.querySelector('[data-add-to-cart]');
            const notif = document.getElementById('cart-notification');
            const thumb = document.querySelector('.panel:first-child img');
            const cartIcon = document.querySelector('.nav-cart-icon');

            function sync() {
                if (!select || !idInput || !priceView) return;
                const opt = select.options[select.selectedIndex];
                idInput.value = opt.value;
                const price = opt.getAttribute('data-price') || '0';
                const stock = parseInt(opt.getAttribute('data-stock') || '0');
                priceView.textContent = 'Price: ' + price + ' | Stock: ' + stock;

                if (qtyInput) {
                    qtyInput.max = Math.min(99, stock);
                    const qty = parseInt(qtyInput.value) || 1;
                    if (qty > stock) {
                        stockWarning.style.display = '';
                        stockWarning.textContent = 'Stock only ' + stock + ' available';
                    } else {
                        stockWarning.style.display = 'none';
                    }
                }
            }

            if (select) {
                select.addEventListener('change', sync);
            }

            if (qtyInput) {
                qtyInput.addEventListener('input', function () {
                    const opt = select.options[select.selectedIndex];
                    const stock = parseInt(opt.getAttribute('data-stock') || '0');
                    const qty = parseInt(this.value) || 1;
                    if (qty > stock) {
                        stockWarning.style.display = '';
                        stockWarning.textContent = 'Stock only ' + stock + ' available';
                    } else {
                        stockWarning.style.display = 'none';
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const fd = new FormData(this);
                    const qty = parseInt(fd.get('quantity')) || 1;
                    const opt = select ? select.options[select.selectedIndex] : null;
                    const stock = opt ? parseInt(opt.getAttribute('data-stock') || '0') : 0;

                    if (qty > stock) {
                        if (stockWarning) {
                            stockWarning.style.display = '';
                            stockWarning.textContent = 'Stock only ' + stock + ' available';
                        }
                        return;
                    }

                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Adding...';

                    fetch('{{ route('buyer.cart.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        },
                        body: fd,
                    })
                    .then(function (r) {
                        if (r.status === 401) {
                            window.location.href = '{{ route('auth.login') }}';
                            return null;
                        }
                        if (r.redirected) {
                            window.location.href = r.url;
                            return null;
                        }
                        return r.json();
                    })
                    .then(function (data) {
                        if (!data) return;
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Add to Cart';

                        if (data.success) {
                            if (notif) {
                                notif.textContent = data.message || 'Item added to cart.';
                                notif.style.display = '';
                                setTimeout(function () { notif.style.display = 'none'; }, 3000);
                            }

                            if (data.cartCount !== undefined) {
                                var badge = document.querySelector('.cart-badge');
                                if (data.cartCount > 0) {
                                    if (badge) {
                                        badge.textContent = data.cartCount;
                                    } else {
                                        var newBadge = document.createElement('span');
                                        newBadge.className = 'cart-badge';
                                        newBadge.textContent = data.cartCount;
                                        if (cartIcon) cartIcon.appendChild(newBadge);
                                    }
                                } else {
                                    if (badge) badge.remove();
                                }
                            }

                            if (thumb && cartIcon) {
                                var clone = thumb.cloneNode(true);
                                var rect = thumb.getBoundingClientRect();
                                var cartRect = cartIcon.getBoundingClientRect();

                                clone.style.cssText = 'position:fixed;z-index:9999;width:80px;height:60px;object-fit:cover;border-radius:8px;pointer-events:none;transition:all 0.6s cubic-bezier(.25,.46,.45,.94);';
                                clone.style.left = rect.left + 'px';
                                clone.style.top = rect.top + 'px';
                                document.body.appendChild(clone);

                                requestAnimationFrame(function () {
                                    clone.style.left = cartRect.left + (cartRect.width / 2) - 40 + 'px';
                                    clone.style.top = cartRect.top + (cartRect.height / 2) - 30 + 'px';
                                    clone.style.transform = 'scale(0.3)';
                                    clone.style.opacity = '0.3';
                                });

                                setTimeout(function () { clone.remove(); }, 700);
                            }
                        } else {
                            if (notif) {
                                notif.textContent = data.message || 'Failed to add item.';
                                notif.style.borderColor = 'rgba(255,77,77,0.35)';
                                notif.style.background = 'rgba(255,77,77,0.08)';
                                notif.style.display = '';
                                setTimeout(function () {
                                    notif.style.display = 'none';
                                    notif.style.borderColor = 'rgba(217,180,111,0.35)';
                                    notif.style.background = 'rgba(217,180,111,0.08)';
                                }, 3000);
                            }
                        }
                    })
                    .catch(function () {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Add to Cart';
                        if (notif) {
                            notif.textContent = 'Something went wrong.';
                            notif.style.borderColor = 'rgba(255,77,77,0.35)';
                            notif.style.background = 'rgba(255,77,77,0.08)';
                            notif.style.display = '';
                            setTimeout(function () {
                                notif.style.display = 'none';
                                notif.style.borderColor = 'rgba(217,180,111,0.35)';
                                notif.style.background = 'rgba(217,180,111,0.08)';
                            }, 3000);
                        }
                    });
                });
            }

            sync();
        })();
    </script>
@endpush
@endsection

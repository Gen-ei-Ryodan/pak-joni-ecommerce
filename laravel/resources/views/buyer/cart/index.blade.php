@extends('layouts.buyer')

@section('title', 'Cart')

@section('content')
    <section class="section">
        <div class="container">
            @if ($errors->any())
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(255,77,77,0.35);background:rgba(255,77,77,0.08);">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                    {{ session('status') }}
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Cart</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    @if($cart->items->isNotEmpty())
                        <form method="post" action="{{ route('buyer.cart.clear') }}" onsubmit="return confirm('Clear cart?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-danger" type="submit">Clear Cart</button>
                        </form>
                    @endif
                </div>
            </div>

            <div style="height:14px;"></div>

            @if($cart->items->isEmpty())
                <div class="panel" style="padding:20px;text-align:center;color:var(--muted);">Cart is empty.</div>
            @else
                <div style="display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start;">
                    <div class="panel" style="padding:10px;">
                        <div style="display:flex;align-items:center;gap:12px;padding:6px 10px;border-bottom:1px solid var(--line);">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" id="select-all" checked>
                                <span style="font-size:13px;font-weight:500;">Select All</span>
                            </label>
                            <span class="muted" style="font-size:12px;" data-selected-count>{{ $cart->items->count() }} selected</span>
                        </div>

                        <div style="display:grid;" data-cart-list>
                            @foreach ($cart->items as $it)
                                <div class="cart-item-row" data-cart-item data-id="{{ $it->id }}" style="display:flex;gap:14px;padding:14px 10px;border-bottom:1px solid var(--line);align-items:center;">
                                    <input type="checkbox" class="cart-item-checkbox" data-item-id="{{ $it->id }}" checked style="flex-shrink:0;">

                                    <div style="width:80px;height:60px;border-radius:8px;border:1px solid var(--line);overflow:hidden;flex-shrink:0;background:rgba(255,255,255,0.03);">
                                        @if($it->variant->part->thumbnail_path)
                                            <img src="{{ image_url($it->variant->part->thumbnail_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                        @endif
                                    </div>

                                    <div style="flex:1;min-width:0;">
                                        <a href="{{ route('buyer.parts.show', $it->variant->part->slug) }}" style="font-weight:500;">{{ $it->variant->part->name }}</a>
                                        <div class="muted" style="margin-top:4px;font-size:12px;">{{ $it->variant->name }} — {{ $it->variant->sku }}</div>
                                        <div style="margin-top:2px;font-size:11px;{{ $it->variant->stock < 10 ? 'color:#f87171;' : 'color:var(--muted);' }}">
                                            Stock: {{ $it->variant->stock }}
                                        </div>
                                    </div>

                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-family:var(--mono);">Rp {{ number_format((float) $it->price_snapshot, 0, ',', '.') }}</div>
                                    </div>

                                    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                        <form method="post" action="{{ route('buyer.cart.update', $it) }}" style="display:flex;align-items:center;gap:6px;">
                                            @csrf
                                            @method('patch')
                                            <button type="button" class="qty-btn" data-qty-minus style="width:28px;height:28px;border-radius:6px;border:1px solid var(--line);background:none;color:var(--muted);cursor:pointer;font-size:14px;">−</button>
                                            <input type="number" name="quantity" value="{{ $it->quantity }}" min="1" max="99" required style="width:48px;text-align:center;border-radius:6px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:4px;color:var(--text);font-size:13px;">
                                            <button type="button" class="qty-btn" data-qty-plus style="width:28px;height:28px;border-radius:6px;border:1px solid var(--line);background:none;color:var(--muted);cursor:pointer;font-size:14px;">+</button>
                                            <button class="btn" type="submit" style="display:none;" data-qty-update>Update</button>
                                        </form>
                                    </div>

                                    <div style="font-family:var(--mono);font-weight:500;flex-shrink:0;min-width:90px;text-align:right;">
                                        Rp {{ number_format((float) $it->price_snapshot * (int) $it->quantity, 0, ',', '.') }}
                                    </div>

                                    <div style="flex-shrink:0;">
                                        <form method="post" action="{{ route('buyer.cart.destroy', $it) }}" onsubmit="return confirm('Remove item?')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" style="width:28px;height:28px;border-radius:6px;border:1px solid rgba(239,68,68,0.3);background:none;color:#f87171;cursor:pointer;font-size:14px;">×</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="panel" style="padding:16px;position:sticky;top:80px;">
                        <div style="font-weight:600;margin-bottom:12px;">Shopping Summary</div>

                        <div class="muted" style="display:grid;gap:8px;">
                            <div style="display:flex;justify-content:space-between;">
                                <span>Selected Items</span>
                                <span data-summary-count>{{ $cart->items->count() }} item(s)</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Total Price</span>
                                <span style="font-family:var(--mono);font-weight:600;color:var(--text);" data-summary-total>Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div style="height:14px;"></div>

                        <form method="post" action="{{ route('buyer.cart.checkoutSelected') }}" id="checkout-selected-form">
                            @csrf
                            <input type="hidden" name="selected_ids" id="selected-ids-input" value="{{ $cart->items->pluck('id')->join(',') }}">
                            <button class="btn btn-primary" type="submit" style="width:100%;" id="checkout-btn">Checkout</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const checkboxes = document.querySelectorAll('.cart-item-checkbox');
            const selectAll = document.getElementById('select-all');
            const selectedIdsInput = document.getElementById('selected-ids-input');
            const checkoutBtn = document.getElementById('checkout-btn');
            const summaryCount = document.querySelector('[data-summary-count]');
            const summaryTotal = document.querySelector('[data-summary-total]');
            const selectedCountLabel = document.querySelector('[data-selected-count]');
            const qtyMinusBtns = document.querySelectorAll('[data-qty-minus]');
            const qtyPlusBtns = document.querySelectorAll('[data-qty-plus]');

            function updateSummary() {
                var checked = document.querySelectorAll('.cart-item-checkbox:checked');
                var total = 0;
                var ids = [];

                checked.forEach(function (cb) {
                    var row = cb.closest('[data-cart-item]');
                    var id = cb.getAttribute('data-item-id');
                    ids.push(id);

                    var priceText = row.querySelectorAll('div[style*="font-family:var(--mono)"]');
                    var qtyInput = row.querySelector('input[name="quantity"]');
                    var price = 0;
                    if (priceText.length > 0) {
                        var raw = priceText[0].textContent.replace(/[^0-9]/g, '');
                        price = parseInt(raw) || 0;
                    }
                    var qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;
                    total += price * qty;
                });

                var count = checked.length;

                if (selectedIdsInput) selectedIdsInput.value = ids.join(',');
                if (summaryCount) summaryCount.textContent = count + ' item(s)';
                if (summaryTotal) summaryTotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
                if (selectedCountLabel) selectedCountLabel.textContent = count + ' selected';

                if (checkoutBtn) {
                    checkoutBtn.disabled = count === 0;
                    checkoutBtn.style.opacity = count === 0 ? '0.4' : '';
                }
            }

            checkboxes.forEach(function (cb) {
                cb.addEventListener('change', function () {
                    var all = document.querySelectorAll('.cart-item-checkbox');
                    var checked = document.querySelectorAll('.cart-item-checkbox:checked');
                    if (selectAll) selectAll.checked = all.length === checked.length;
                    updateSummary();
                });
            });

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
                    updateSummary();
                });
            }

            qtyMinusBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = this.closest('form').querySelector('input[name="quantity"]');
                    var val = parseInt(input.value) || 1;
                    if (val > 1) {
                        input.value = val - 1;
                        input.closest('form').querySelector('[data-qty-update]').click();
                    }
                });
            });

            qtyPlusBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = this.closest('form').querySelector('input[name="quantity"]');
                    var val = parseInt(input.value) || 1;
                    if (val < 99) {
                        input.value = val + 1;
                        input.closest('form').querySelector('[data-qty-update]').click();
                    }
                });
            });

            updateSummary();
        })();
    </script>
@endpush

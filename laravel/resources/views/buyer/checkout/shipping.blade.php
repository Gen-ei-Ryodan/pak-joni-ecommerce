@extends('layouts.buyer')

@section('title', 'Checkout - Shipping')

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
                <div style="font-size:18px;font-weight:600;">Checkout — Shipping</div>
                <a class="btn" href="{{ route('buyer.checkout.address') }}">Back</a>
            </div>

            <div style="height:14px;"></div>

            <div style="display:grid;grid-template-columns:1fr 420px;gap:16px;">
                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Address</div>
                    <div style="height:8px;"></div>
                    <div class="muted" style="line-height:1.8;">
                        {{ $address->recipient_name }} — {{ $address->phone }}<br>
                        {{ $address->address_line1 }} {{ $address->address_line2 }}<br>
                        {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                    </div>

                    <div style="height:14px;"></div>

                    <div style="font-weight:600;">Select Shipping</div>
                    <div style="height:10px;"></div>

                    {{-- Biteship rates area --}}
                    <div id="shipping-rates">
                        <div id="rates-loading" style="padding:20px;text-align:center;color:var(--muted);">
                            <div class="spinner" style="width:24px;height:24px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin 0.6s linear infinite;margin:0 auto 8px;"></div>
                            Mengambil ongkos kirim...
                        </div>

                        <div id="rates-error" style="display:none;padding:12px;background:rgba(255,77,77,0.08);border:1px solid rgba(255,77,77,0.2);border-radius:8px;margin-bottom:12px;color:#d32f2f;font-size:13px;"></div>

                        <form method="post" action="{{ route('buyer.checkout.setShipping') }}" id="shipping-form" style="display:none;">
                            @csrf
                            <input type="hidden" name="courier" id="input-courier" value="">
                            <input type="hidden" name="service" id="input-service" value="">
                            <input type="hidden" name="shipping_cost" id="input-cost" value="">
                            <input type="hidden" name="courier_name" id="input-courier-name" value="">
                            <input type="hidden" name="service_name" id="input-service-name" value="">

                            <div id="rates-list" style="display:grid;gap:8px;max-width:520px;"></div>

                            <div style="height:14px;"></div>
                            <button class="btn btn-primary" type="submit" id="btn-continue" disabled>Pilih Shipping Dulu</button>
                        </form>
                    </div>
                </div>

                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Summary</div>
                    <div style="height:10px;"></div>

                    <div class="muted" style="display:grid;gap:8px;">
                        <div>Total item: {{ $cart->items->sum('quantity') }}</div>
                        <div>Subtotal: <span style="font-family:var(--mono);">{{ number_format((float) $subtotal, 2, '.', ',') }}</span></div>
                        <div id="selected-shipping-info" style="display:none;">
                            <div>Shipping: <span id="selected-shipping-label" style="font-family:var(--mono);"></span></div>
                            <div>Ongkir: <span id="selected-shipping-cost" style="font-family:var(--mono);"></span></div>
                        </div>
                        @if($hasIndent)
                            <div style="margin-top:6px;padding:8px;background:#fff3cd;border-radius:8px;font-size:12px;color:#856404;">
                                <div style="font-weight:600;margin-bottom:4px;">Indent Order - DP 50%</div>
                                <div>DP (dibayar sekarang): <span style="font-family:var(--mono);">{{ number_format($dpAmount, 2, '.', ',') }}</span></div>
                                <div>Sisa (saat barang ready): <span style="font-family:var(--mono);">{{ number_format($remainingAmount, 2, '.', ',') }}</span></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        .rate-card {
            display:flex;align-items:center;justify-content:space-between;
            padding:12px;border:2px solid var(--border);border-radius:8px;
            cursor:pointer;transition:all 0.15s;
        }
        .rate-card:hover { border-color:var(--primary); }
        .rate-card.selected { border-color:var(--primary); background:rgba(217,180,111,0.06); }
        .rate-card .courier-badge {
            font-size:11px;padding:2px 6px;border-radius:4px;background:var(--border);
            color:var(--muted);margin-left:6px;
        }
        .rate-card .price { font-weight:600; font-size:15px; }
    </style>

    <script>
    (function() {
        const ratesUrl = '{{ route('buyer.checkout.rates') }}';
        const loadingEl = document.getElementById('rates-loading');
        const errorEl = document.getElementById('rates-error');
        const formEl = document.getElementById('shipping-form');
        const listEl = document.getElementById('rates-list');
        const btnContinue = document.getElementById('btn-continue');
        const inputCourier = document.getElementById('input-courier');
        const inputService = document.getElementById('input-service');
        const inputCost = document.getElementById('input-cost');
        const inputCourierName = document.getElementById('input-courier-name');
        const inputServiceName = document.getElementById('input-service-name');
        const shippingInfo = document.getElementById('selected-shipping-info');
        const shippingLabel = document.getElementById('selected-shipping-label');
        const shippingCost = document.getElementById('selected-shipping-cost');

        let selectedCard = null;

        function selectCard(card, data) {
            if (selectedCard) selectedCard.classList.remove('selected');
            card.classList.add('selected');
            selectedCard = card;

            inputCourier.value = data.courier_code;
            inputService.value = data.courier_service_code;
            inputCost.value = data.price;
            inputCourierName.value = data.courier_name;
            inputServiceName.value = data.courier_service_name;

            shippingInfo.style.display = '';
            shippingLabel.textContent = data.courier_name + ' - ' + data.courier_service_name;
            shippingCost.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.price);

            btnContinue.disabled = false;
            btnContinue.textContent = 'Continue to Payment';
        }

        function renderRates(pricing) {
            loadingEl.style.display = 'none';
            errorEl.style.display = 'none';
            formEl.style.display = '';

            if (!pricing || pricing.length === 0) {
                errorEl.textContent = 'Tidak ada kurir yang tersedia untuk alamat tujuan ini.';
                errorEl.style.display = '';
                return;
            }

            // Group by courier_code
            const groups = {};
            pricing.forEach(p => {
                const key = p.courier_code;
                if (!groups[key]) groups[key] = { name: p.courier_name, code: key, services: [] };
                groups[key].services.push(p);
            });

            let html = '';
            Object.values(groups).forEach(group => {
                html += '<div style="font-weight:600;font-size:13px;margin:8px 0 4px;">' + group.name + '</div>';
                group.services.forEach(s => {
                    const dur = s.duration || '';
                    const isCOD = s.available_for_cash_on_delivery ? '<span class="courier-badge">COD</span>' : '';
                    html += `
                        <div class="rate-card" data-courier="${s.courier_code}" data-service="${s.courier_service_code}" data-price="${s.price}" data-courier-name="${s.courier_name}" data-service-name="${s.courier_service_name}">
                            <div>
                                <div style="font-weight:500;">${s.courier_service_name} ${isCOD}</div>
                                <div style="font-size:12px;color:var(--muted);">${s.description || ''} &middot; ${dur}</div>
                            </div>
                            <div class="price">Rp ${new Intl.NumberFormat('id-ID').format(s.price)}</div>
                        </div>`;
                });
            });

            listEl.innerHTML = html;

            // Attach click handlers
            listEl.querySelectorAll('.rate-card').forEach(card => {
                card.addEventListener('click', function() {
                    const data = {
                        courier_code: this.dataset.courier,
                        courier_service_code: this.dataset.service,
                        price: Number(this.dataset.price),
                        courier_name: this.dataset.courierName,
                        courier_service_name: this.dataset.serviceName,
                    };
                    selectCard(this, data);
                });
            });
        }

        function showError(msg) {
            loadingEl.style.display = 'none';
            errorEl.textContent = msg;
            errorEl.style.display = '';
            formEl.style.display = 'none';
        }

        // Fetch rates on load
        fetch(ratesUrl)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderRates(data.pricing);
                } else {
                    showError(data.error || 'Gagal mengambil ongkos kirim.');
                }
            })
            .catch(() => {
                showError('Gagal terhubung ke server ongkir. Coba lagi nanti.');
            });
    })();
    </script>
@endsection

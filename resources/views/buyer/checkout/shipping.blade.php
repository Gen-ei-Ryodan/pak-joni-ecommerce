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
                    <div style="font-weight:600;">Alamat</div>
                    <div style="height:8px;"></div>
                    <div class="muted" style="line-height:1.8;">
                        {{ $address->recipient_name }} — {{ $address->phone }}<br>
                        {{ $address->address_line1 }} {{ $address->address_line2 }}<br>
                        {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                    </div>

                    <div style="height:14px;"></div>

                    <div style="font-weight:600;">Pilih Shipping</div>
                    <div style="height:10px;"></div>

                    <form method="post" action="{{ route('buyer.checkout.setShipping') }}" style="display:grid;gap:12px;max-width:520px;">
                        @csrf
                        <div class="field">
                            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Courier</label>
                            <input class="input" style="width:100%;min-width:0;" name="courier" value="{{ old('courier', $shippingSnapshot['courier'] ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Service</label>
                            <input class="input" style="width:100%;min-width:0;" name="service" value="{{ old('service', $shippingSnapshot['service'] ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Shipping Cost</label>
                            <input class="input" style="width:100%;min-width:0;" name="shipping_cost" type="number" step="0.01" value="{{ old('shipping_cost', $shippingSnapshot['shipping_cost'] ?? 0) }}" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Lanjut Payment</button>
                    </form>
                </div>

                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Ringkasan</div>
                    <div style="height:10px;"></div>

                    <div class="muted" style="display:grid;gap:8px;">
                        <div>Total item: {{ $cart->items->sum('quantity') }}</div>
                        <div>Subtotal: <span style="font-family:var(--mono);">{{ number_format((float) $subtotal, 2, '.', ',') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

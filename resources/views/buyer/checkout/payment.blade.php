@extends('layouts.buyer')

@section('title', 'Checkout - Payment')

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
                <div style="font-size:18px;font-weight:600;">Checkout — Payment</div>
                <a class="btn" href="{{ route('buyer.checkout.shipping') }}">Back</a>
            </div>

            <div style="height:14px;"></div>

            <div style="display:grid;grid-template-columns:1fr 420px;gap:16px;">
                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Items</div>
                    <div style="height:10px;"></div>

                    <div style="display:grid;gap:10px;">
                        @foreach ($cart->items as $it)
                            <div class="panel" style="padding:12px;border-radius:12px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                                <div>
                                    @if ($it->itemable_type === 'App\Models\PartVariant')
                                        <div style="font-weight:600;">{{ $it->itemable->part->name ?? 'N/A' }}</div>
                                        <div class="muted" style="margin-top:6px;">{{ $it->variant_name }} — {{ $it->itemable->sku ?? 'N/A' }}</div>
                                    @elseif ($it->itemable_type === 'App\Models\MotorColor')
                                        <div style="font-weight:600;">{{ $it->itemable->motor->name ?? 'N/A' }}</div>
                                        <div class="muted" style="margin-top:6px;">{{ $it->variant_name }}</div>
                                    @endif
                                </div>
                                <div style="font-family:var(--mono);">
                                    {{ $it->quantity }} x {{ number_format((float) $it->price_snapshot, 2, '.', ',') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div style="height:14px;"></div>

                    <form method="post" action="{{ route('buyer.checkout.place') }}">
                        @csrf
                        <button class="btn btn-primary" type="submit">Place Order</button>
                    </form>
                </div>

                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Summary</div>
                    <div style="height:10px;"></div>

                    <div class="muted" style="display:grid;gap:8px;">
                        <div>Subtotal: <span style="font-family:var(--mono);">{{ number_format((float) $subtotal, 2, '.', ',') }}</span></div>
                        <div>Shipping: <span style="font-family:var(--mono);">{{ number_format((float) $shippingCost, 2, '.', ',') }}</span></div>
                        <div>Total: <span style="font-family:var(--mono);">{{ number_format((float) $total, 2, '.', ',') }}</span></div>
                    </div>

                    <div style="height:14px;"></div>

                    <div style="font-weight:600;">Address</div>
                    <div style="height:8px;"></div>
                    <div class="muted" style="line-height:1.8;">
                        {{ $address->recipient_name }} — {{ $address->phone }}<br>
                        {{ $address->address_line1 }} {{ $address->address_line2 }}<br>
                        {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                    </div>

                    <div style="height:14px;"></div>
                    <div style="font-weight:600;">Shipping</div>
                    <div style="height:8px;"></div>
                    <div class="muted">{{ $shipping['courier'] }} — {{ $shipping['service'] }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection

@extends('layouts.buyer')

@section('title', 'Checkout - Address')

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
                <div style="font-size:18px;font-weight:600;">Checkout — Address</div>
                <a class="btn" href="{{ route('buyer.cart.index') }}">Back to Cart</a>
            </div>

            <div style="height:14px;"></div>

            <div style="display:grid;grid-template-columns:1fr 420px;gap:16px;">
                <div class="panel" style="padding:14px;">
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
                        <div style="font-weight:600;">Select Address</div>
                        <a class="btn" href="{{ route('buyer.addresses.create') }}">Add Address</a>
                    </div>
                    <div style="height:10px;"></div>

                    @if ($addresses->isEmpty())
                        <div class="muted">No addresses yet. Please add an address first.</div>
                    @else
                        <form method="post" action="{{ route('buyer.checkout.setAddress') }}" style="display:grid;gap:10px;">
                            @csrf
                            @foreach ($addresses as $a)
                                <label class="panel" style="padding:12px;border-radius:12px;display:flex;gap:12px;align-items:start;">
                                    <input type="radio" name="address_id" value="{{ $a->id }}" @checked($selectedAddressId === $a->id) required style="margin-top:4px;">
                                    <div>
                                        <div style="font-weight:600;">
                                            {{ $a->label ?: 'Address' }}
                                            @if ($a->is_default)
                                                <span class="muted" style="margin-left:10px;">(default)</span>
                                            @endif
                                        </div>
                                        <div class="muted" style="margin-top:8px;line-height:1.8;">
                                            {{ $a->recipient_name }} — {{ $a->phone }}<br>
                                            {{ $a->address_line1 }} {{ $a->address_line2 }}<br>
                                            {{ $a->village }}, {{ $a->district }}<br>
                                            {{ $a->city }}, {{ $a->province }} {{ $a->postal_code }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach

                            {{-- Dealer Pickup Option --}}
                            <div style="height:6px;"></div>
                            <div style="font-weight:600;font-size:13px;color:var(--muted);">— atau —</div>
                            <div style="height:6px;"></div>

                            <label class="panel" style="padding:12px;border-radius:12px;display:flex;gap:12px;align-items:start;border-color:rgba(217,180,111,0.25);cursor:pointer;transition:border-color 0.15s;">
                                <input type="radio" name="address_id" value="0" @checked(old('address_id', session('checkout.dealer_pickup')) == 0) style="margin-top:4px;">
                                <div>
                                    <div style="font-weight:600;color:var(--accent);">Ambil di Dealer</div>
                                    <div class="muted" style="margin-top:6px;line-height:1.7;font-size:13px;">
                                        Ambil barang langsung di dealer/workshop kami.<br>
                                        <span style="color:#4ade80;font-weight:500;">Tidak ada biaya pengiriman (Gratis Ongkir)</span>
                                    </div>
                                </div>
                            </label>

                            <div style="height:4px;"></div>
                            <button class="btn btn-primary" type="submit">Continue</button>
                        </form>
                    @endif
                </div>

                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Summary</div>
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

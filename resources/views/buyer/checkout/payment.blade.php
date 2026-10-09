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
                                    @elseif ($it->itemable_type === 'App\Models\ItemColor')
                                        <div style="font-weight:600;">{{ $it->itemable->item->name ?? 'N/A' }}</div>
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
                        <div>Ongkos Kirim: <span style="font-family:var(--mono);">@if($isDealerPickup)<span style="color:#4ade80;">Gratis</span>@else{{ number_format((float) $shippingCost, 2, '.', ',') }}@endif</span></div>
                        @if($hasIndent)
                            <div style="margin-top:6px;padding:8px;background:#fff3cd;border-radius:8px;font-size:12px;color:#856404;">
                                <div style="font-weight:600;margin-bottom:4px;">Indent Order - DP 50%</div>
                                <div>DP (dibayar sekarang): <span style="font-family:var(--mono);">{{ number_format($dpAmount, 2, '.', ',') }}</span></div>
                                <div>Sisa (saat barang ready): <span style="font-family:var(--mono);">{{ number_format($remainingAmount, 2, '.', ',') }}</span></div>
                            </div>
                        @endif
                        @if(isset($itemDiscount) && $itemDiscount > 0)
                            <div style="margin-top:6px;padding:8px;background:#e0f7fa;border-radius:8px;font-size:12px;color:#01579b;">
                                <div style="font-weight:600;margin-bottom:4px;">Diskon Item</div>
                                <div>Diskon: <span style="color:#0277bd;">Rp {{ number_format($itemDiscount, 0, ',', '.') }}</div>
                                <div>Harga Akhir: <span style="color:#0277bd;font-weight:500;">Rp {{ number_format($finalPriceAfterItemDiscount, 2, '.', ',') }}</div>
                            </div>
                        @endif
                        @if(isset($voucherDiscount) && $voucherDiscount > 0)
                            <div style="margin-top:6px;padding:8px;background:#f3e5f5;border-radius:8px;font-size:12px;color:#4a148c;">
                                <div style="font-weight:600;margin-bottom:4px;">Voucher</div>
                                <div>Voucher: <span style="color:#4a148c;">{{ $voucherCode }}</div>
                                <div>Diskon Voucher: <span style="color:#4a148c;">Rp {{ number_format($voucherDiscount, 2, '.', ',') }}</div>
                                <div>Total: <span style="color:#4a148c;font-weight:500;">Rp {{ number_format($finalPriceAfterVoucher, 2, '.', ',') }}</div>
                            </div>
                        @endif

                        {{-- Voucher Input Form --}}
                        @if(! $isDealerPickup)
                        <div style="margin-top:20px;padding:12px;border:1px solid var(--line);border-radius:8px;">
                            <div style="font-weight:600;margin-bottom:8px;">Masukkan Voucher</div>
                            <form method="post" action="{{ route('buyer.checkout.place') }}" style="margin-top:12px;">
                                @csrf
                                <div style="display:grid;gap:8px;">
                                    <input type="text" name="voucher_code" placeholder="Kode voucher" class="form-input" style="padding:8px 12px;border:1px solid var(--line);border-radius:4px;">
                                    <button type="submit" class="btn btn-primary" style="width:100%;padding:8px;">Apply Voucher</button>
                                </div>
                            </form>
                            @if(session('voucher_error'))
                                <div style="margin-top:6px;color:#dc2626;font-size:12px;">{{ session('voucher_error') }}</div>
                            @endif
                        </div>
                        @endif
                        <div>Total: <span style="font-family:var(--mono);">{{ number_format((float) $total, 2, '.', ',') }}</span></div>
                    </div>

                    @if($isDealerPickup)
                        <div style="height:14px;"></div>
                        <div style="font-weight:600;">Pengambilan</div>
                        <div style="height:8px;"></div>
                        <div class="muted" style="line-height:1.8;">
                            Ambil di Dealer / Workshop<br>
                            <span style="color:#4ade80;font-weight:500;">Gratis Ongkir — Tidak ada biaya pengiriman</span>
                        </div>
                    @else
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
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

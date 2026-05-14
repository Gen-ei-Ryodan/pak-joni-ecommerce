@extends('layouts.buyer')

@section('title', 'Cart')

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
                <div style="font-size:18px;font-weight:600;">Cart</div>
                <a class="btn btn-primary" href="{{ route('buyer.checkout.address') }}">Checkout</a>
            </div>

            <div style="height:14px;"></div>

            <div class="panel" style="padding:10px;">
                <div style="overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:860px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:12px;">
                                <th style="padding:10px;">Item</th>
                                <th style="padding:10px;">Variant</th>
                                <th style="padding:10px;">Price</th>
                                <th style="padding:10px;">Qty</th>
                                <th style="padding:10px;">Total</th>
                                <th style="padding:10px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cart->items as $it)
                                <tr style="border-top:1px solid var(--line);">
                                    <td style="padding:10px;">
                                        <a href="{{ route('buyer.parts.show', $it->variant->part->slug) }}">{{ $it->variant->part->name }}</a>
                                        <div class="muted" style="margin-top:6px;">{{ $it->variant->sku }}</div>
                                    </td>
                                    <td style="padding:10px;color:var(--muted);">{{ $it->variant->name }}</td>
                                    <td style="padding:10px;">{{ number_format((float) $it->price_snapshot, 2, '.', ',') }}</td>
                                    <td style="padding:10px;">
                                        <form method="post" action="{{ route('buyer.cart.update', $it) }}" style="display:flex;gap:10px;align-items:center;">
                                            @csrf
                                            @method('patch')
                                            <input class="input" style="width:110px;min-width:0;" type="number" name="quantity" value="{{ $it->quantity }}" min="1" max="99" required>
                                            <button class="btn" type="submit">Update</button>
                                        </form>
                                    </td>
                                    <td style="padding:10px;">{{ number_format((float) $it->price_snapshot * (int) $it->quantity, 2, '.', ',') }}</td>
                                    <td style="padding:10px;">
                                        <form method="post" action="{{ route('buyer.cart.destroy', $it) }}" onsubmit="return confirm('Hapus item?')">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-danger" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:12px;color:var(--muted);">Cart kosong.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="height:12px;"></div>

            <div class="panel" style="padding:14px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div class="muted">Subtotal</div>
                <div style="font-family:var(--mono);">{{ number_format((float) $subtotal, 2, '.', ',') }}</div>
            </div>
        </div>
    </section>
@endsection

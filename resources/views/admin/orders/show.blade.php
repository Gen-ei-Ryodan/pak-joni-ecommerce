@extends('layouts.admin')

@section('title', 'Order Detail')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div>
            <div style="font-size:16px;font-weight:600;">Order {{ $order->order_no }}</div>
            <div class="muted" style="margin-top:6px;">Buyer: {{ $order->user?->email }}</div>
        </div>
        <a class="btn" href="{{ route('admin.orders.index') }}">Kembali</a>
    </div>

    <div style="height:14px;"></div>

    <div class="panel" style="padding:16px;">
        <div style="display:grid;gap:10px;">
            <div><span class="muted">Status:</span> {{ $order->status }}</div>
            <div><span class="muted">Subtotal:</span> {{ number_format((float) $order->subtotal, 2, '.', ',') }}</div>
            <div><span class="muted">Shipping:</span> {{ number_format((float) $order->shipping_cost, 2, '.', ',') }}</div>
            <div><span class="muted">Total:</span> {{ number_format((float) $order->total, 2, '.', ',') }}</div>
        </div>

        <div style="height:14px;"></div>

        <div style="font-weight:600;">Items</div>
        <div style="height:10px;"></div>

        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:760px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">SKU</th>
                        <th style="padding:10px;">Name</th>
                        <th style="padding:10px;">Variant</th>
                        <th style="padding:10px;">Qty</th>
                        <th style="padding:10px;">Price</th>
                        <th style="padding:10px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $it)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">{{ $it->sku }}</td>
                            <td style="padding:10px;">{{ $it->name }}</td>
                            <td style="padding:10px;color:var(--muted);">{{ $it->variant_name ?? '-' }}</td>
                            <td style="padding:10px;">{{ $it->quantity }}</td>
                            <td style="padding:10px;">{{ number_format((float) $it->price, 2, '.', ',') }}</td>
                            <td style="padding:10px;">{{ number_format((float) $it->line_total, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="height:16px;"></div>

        <div style="font-weight:600;">Update</div>
        <div style="height:10px;"></div>

        <form method="post" action="{{ route('admin.orders.update', $order) }}" style="display:grid;gap:12px;max-width:520px;">
            @csrf
            @method('put')
            <div class="field">
                <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Status</label>
                @php($statusVal = old('status', $order->status))
                <select name="status" required
                    style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                    @foreach (['pending','paid','expired','cancelled','shipped','completed'] as $s)
                        <option value="{{ $s }}" @selected($statusVal === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Tracking Number (optional)</label>
                <input name="tracking_number" value="{{ old('tracking_number', $order->shipment?->tracking_number ?? '') }}"
                    style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
            </div>

            <button class="btn btn-primary" type="submit">Simpan</button>
        </form>
    </div>
@endsection

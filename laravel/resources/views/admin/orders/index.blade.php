@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Orders</div>
    </div>

    <div style="height:12px;"></div>

    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
        <input name="q" value="{{ $q }}" placeholder="Search invoice..." style="flex:1;min-width:180px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        <select name="status" style="border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);min-width:140px;">
            <option value="">All Status</option>
            @foreach(\App\Models\Order::STATUSES as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>

    <div style="height:14px;"></div>

    <div class="panel" style="padding:10px;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:860px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Invoice</th>
                        <th style="padding:10px;">Buyer</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;">Payment</th>
                        <th style="padding:10px;">Total</th>
                        <th style="padding:10px;">Date</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">{{ $order->order_no }}</td>
                            <td style="padding:10px;color:var(--muted);">{{ $order->user?->email }}</td>
                            <td style="padding:10px;">
                                <span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
                            </td>
                            <td style="padding:10px;">
                                <span class="badge {{ $order->paymentStatusBadge() }}">{{ $order->payment_status }}</span>
                            </td>
                            <td style="padding:10px;">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                            <td style="padding:10px;color:var(--muted);">{{ $order->created_at->format('d M Y') }}</td>
                            <td style="padding:10px;">
                                <a class="btn" href="{{ route('admin.orders.show', $order) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:12px;color:var(--muted);">No data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="height:12px;"></div>
    {{ $orders->links() }}
@endsection

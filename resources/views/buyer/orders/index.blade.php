@extends('layouts.buyer-dashboard')

@section('title', 'My Orders')

@section('dashboard-content')
    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">My Orders</div>
    </div>

    <div style="height:12px;"></div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;flex:1;min-width:0;">
            <input name="search" value="{{ $search }}" placeholder="Search invoice..." style="flex:1;min-width:180px;border-radius:12px;border:1px solid var(--line);background:#fff;padding:10px 12px;color:var(--text);">
            <select name="status" style="border-radius:12px;border:1px solid var(--line);background:#fff;padding:10px 12px;color:var(--text);min-width:140px;">
                <option value="">All Status</option>
                @foreach(\App\Models\Order::STATUSES as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Filter</button>
        </form>
    </div>

    <div style="height:12px;"></div>

    <div style="overflow:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead>
                <tr style="text-align:left;color:var(--muted);font-size:12px;">
                    <th style="padding:10px;">Invoice</th>
                    <th style="padding:10px;">Date</th>
                    <th style="padding:10px;">Status</th>
                    <th style="padding:10px;">Payment</th>
                    <th style="padding:10px;">Total</th>
                    <th style="padding:10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr style="border-top:1px solid var(--line);">
                        <td style="padding:10px;">{{ $o->order_no }}</td>
                        <td style="padding:10px;color:var(--muted);">{{ $o->created_at->format('d M Y') }}</td>
                        <td style="padding:10px;">
                            <span class="badge {{ $o->statusBadge() }}">{{ $o->statusLabel() }}</span>
                        </td>
                        <td style="padding:10px;">
                            <span class="badge {{ $o->paymentStatusBadge() }}">{{ $o->payment_status }}</span>
                        </td>
                        <td style="padding:10px;">Rp {{ number_format((float) $o->total, 0, ',', '.') }} @if($o->is_indent)<span style="display:inline-block;padding:2px 6px;background:#fff3cd;color:#856404;border-radius:4px;font-size:10px;margin-left:4px;">INDENT</span>@endif</td>
                        <td style="padding:10px;">
                            <a class="btn" href="{{ route('buyer.orders.show', $o) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:12px;color:var(--muted);">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="height:12px;"></div>
    {{ $orders->links() }}
@endsection

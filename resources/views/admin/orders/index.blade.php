@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Orders</div>
    </div>

    <div style="height:12px;"></div>

    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
        <input name="q" value="{{ $q }}" placeholder="Search order no..." style="flex:1;min-width:220px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        <button class="btn" type="submit">Filter</button>
    </form>

    <div style="height:14px;"></div>

    <div class="panel" style="padding:10px;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:860px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Order No</th>
                        <th style="padding:10px;">Buyer</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;">Total</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">{{ $order->order_no }}</td>
                            <td style="padding:10px;color:var(--muted);">{{ $order->user?->email }}</td>
                            <td style="padding:10px;">{{ $order->status }}</td>
                            <td style="padding:10px;">{{ number_format((float) $order->total, 2, '.', ',') }}</td>
                            <td style="padding:10px;">
                                <a class="btn" href="{{ route('admin.orders.show', $order) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:12px;color:var(--muted);">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="height:12px;"></div>
    {{ $orders->links() }}
@endsection

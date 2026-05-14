@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:center;">
        <div>
            <div style="font-size:16px;font-weight:600;">Dashboard Admin</div>
            <div class="muted" style="margin-top:6px;">Halo, {{ auth()->user()->name }}</div>
        </div>
    </div>

    <div style="height:16px;"></div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
        <div class="panel" style="padding:14px;">
            <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Total Orders</div>
            <div style="font-size:24px;font-weight:700;margin-top:6px;">{{ $totalOrders }}</div>
        </div>
        <div class="panel" style="padding:14px;">
            <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Unpaid</div>
            <div style="font-size:24px;font-weight:700;margin-top:6px;color:#facc15;">{{ $unpaidOrders }}</div>
        </div>
        <div class="panel" style="padding:14px;">
            <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Paid</div>
            <div style="font-size:24px;font-weight:700;margin-top:6px;color:#4ade80;">{{ $paidOrders }}</div>
        </div>
        <div class="panel" style="padding:14px;">
            <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Revenue</div>
            <div style="font-size:20px;font-weight:700;margin-top:6px;">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
        </div>
        <div class="panel" style="padding:14px;">
            <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Produk</div>
            <div style="font-size:24px;font-weight:700;margin-top:6px;">{{ $totalProducts }}</div>
        </div>
        <div class="panel" style="padding:14px;">
            <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Buyer</div>
            <div style="font-size:24px;font-weight:700;margin-top:6px;">{{ $totalBuyers }}</div>
        </div>
    </div>

    <div style="height:20px;"></div>

    <div style="font-weight:600;">Recent Orders</div>
    <div style="height:10px;"></div>

    <div class="panel" style="padding:10px;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:760px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Invoice</th>
                        <th style="padding:10px;">Buyer</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;">Total</th>
                        <th style="padding:10px;">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $o)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">
                                <a href="{{ route('admin.orders.show', $o) }}">{{ $o->order_no }}</a>
                            </td>
                            <td style="padding:10px;color:var(--muted);">{{ $o->user?->email }}</td>
                            <td style="padding:10px;">
                                <span class="badge {{ $o->statusBadge() }}">{{ $o->statusLabel() }}</span>
                            </td>
                            <td style="padding:10px;">Rp {{ number_format((float) $o->total, 0, ',', '.') }}</td>
                            <td style="padding:10px;color:var(--muted);">{{ $o->created_at->format('d M Y') }}</td>
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
    <a class="btn" href="{{ route('admin.orders.index') }}">Lihat Semua Orders</a>
@endsection

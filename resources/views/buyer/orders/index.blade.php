@extends('layouts.buyer')

@section('title', 'My Orders')

@section('content')
    <section class="section">
        <div class="container">
            @if (session('status'))
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                    {{ session('status') }}
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">My Orders</div>
                <a class="btn" href="{{ url('/dashboard') }}">Dashboard</a>
            </div>

            <div style="height:14px;"></div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;flex:1;min-width:0;">
                    <input name="search" value="{{ $search }}" placeholder="Cari invoice..." style="flex:1;min-width:180px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                    <select name="status" style="border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);min-width:140px;">
                        <option value="">Semua Status</option>
                        @foreach(\App\Models\Order::STATUSES as $s)
                            <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn" type="submit">Filter</button>
                </form>
            </div>

            <div style="height:14px;"></div>

            <div class="panel" style="padding:10px;">
                <div style="overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:760px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:12px;">
                                <th style="padding:10px;">Invoice</th>
                                <th style="padding:10px;">Tanggal</th>
                                <th style="padding:10px;">Status</th>
                                <th style="padding:10px;">Pembayaran</th>
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
                                    <td style="padding:10px;">Rp {{ number_format((float) $o->total, 0, ',', '.') }}</td>
                                    <td style="padding:10px;">
                                        <a class="btn" href="{{ route('buyer.orders.show', $o) }}">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:12px;color:var(--muted);">Belum ada order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="height:12px;"></div>
            {{ $orders->links() }}
        </div>
    </section>
@endsection

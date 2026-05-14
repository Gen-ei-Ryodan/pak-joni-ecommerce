@extends('layouts.buyer')

@section('title', 'Orders')

@section('content')
    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Orders</div>
                <a class="btn" href="{{ url('/dashboard') }}">Dashboard</a>
            </div>

            <div style="height:14px;"></div>

            <div class="panel" style="padding:10px;">
                <div style="overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:760px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:12px;">
                                <th style="padding:10px;">Order No</th>
                                <th style="padding:10px;">Status</th>
                                <th style="padding:10px;">Total</th>
                                <th style="padding:10px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $o)
                                <tr style="border-top:1px solid var(--line);">
                                    <td style="padding:10px;">{{ $o->order_no }}</td>
                                    <td style="padding:10px;">{{ $o->status }}</td>
                                    <td style="padding:10px;">{{ number_format((float) $o->total, 2, '.', ',') }}</td>
                                    <td style="padding:10px;">
                                        <a class="btn" href="{{ route('buyer.orders.show', $o) }}">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding:12px;color:var(--muted);">Belum ada order.</td>
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

@extends('layouts.buyer')

@section('title', 'Order Created')

@section('content')
    <section class="section" style="min-height:70vh;display:flex;align-items:center;">
        <div class="container" style="max-width:420px;">
            <div class="panel" style="padding:32px 24px;text-align:center;">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(74,222,128,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>

                <div style="height:20px;"></div>

                <div style="font-size:20px;font-weight:700;">Congratulations!</div>
                <div class="muted" style="margin-top:8px;line-height:1.6;font-size:14px;">
                    Your order has been placed successfully.<br>
                    Please check your order for updates.
                </div>

                <div style="height:20px;"></div>

                <div style="background:rgba(255,255,255,0.03);border-radius:12px;padding:14px;border:1px solid var(--line);">
                    <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Invoice Number</div>
                    <div style="font-family:var(--mono);font-size:15px;font-weight:600;margin-top:6px;">{{ $order->order_no }}</div>
                </div>

                <div style="height:20px;"></div>

                <a class="btn btn-primary" href="{{ route('buyer.orders.show', $order) }}" style="width:100%;">Check My Order</a>
            </div>
        </div>
    </section>
@endsection

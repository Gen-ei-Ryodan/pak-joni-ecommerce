@extends('layouts.buyer')

@section('title', 'Order Created')

@section('content')
    <section class="section" style="min-height:70vh;display:flex;align-items:center;">
        <div class="container" style="max-width:480px;">
            <div class="panel" style="padding:32px 24px;text-align:center;">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(74,222,128,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>

                <div style="height:20px;"></div>

                <div style="font-size:20px;font-weight:700;">Pesanan Berhasil Dibuat!</div>
                <div class="muted" style="margin-top:8px;line-height:1.6;font-size:14px;">
                    Silakan selesaikan pembayaran untuk memproses pesanan Anda.
                </div>

                <div style="height:16px;"></div>

                <div style="background:rgba(255,255,255,0.03);border-radius:12px;padding:14px;border:1px solid var(--line);">
                    <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Nomor Invoice</div>
                    <div style="font-family:var(--mono);font-size:15px;font-weight:600;margin-top:6px;">{{ $order->order_no }}</div>
                </div>

                <div style="height:20px;"></div>

                @if($snapToken)
                    <button id="pay-button" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">
                        Bayar Sekarang — Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </button>
                @else
                    <form method="post" action="{{ route('buyer.orders.simulatePayment', $order) }}">
                        @csrf
                        <button class="btn btn-primary" type="submit" style="width:100%;padding:14px;font-size:15px;">
                            Bayar (Simulasi) — Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                        </button>
                    </form>
                @endif

                <div style="height:12px;"></div>

                <a class="btn" href="{{ route('buyer.orders.show', $order) }}" style="width:100%;">Lihat Pesanan Saya</a>

                <div class="muted" style="margin-top:16px;font-size:12px;line-height:1.6;">
                    Setelah pembayaran selesai, halaman ini akan otomatis mengarahkan ke detail pesanan.
                </div>
            </div>
        </div>
    </section>
@endsection

@if($snapToken)
    @push('head')
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    @endpush

    @push('scripts')
        <script>
            document.getElementById('pay-button').addEventListener('click', function () {
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        window.location.href = '{{ route('buyer.orders.show', $order) }}';
                    },
                    onPending: function(result) {
                        window.location.href = '{{ route('buyer.orders.show', $order) }}';
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal. Silakan coba lagi.');
                    },
                    onClose: function() {
                        // User closed the popup without completing payment
                    }
                });
            });
        </script>
    @endpush
@endif

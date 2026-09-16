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
                    Scan QRIS berikut untuk menyelesaikan pembayaran.
                </div>

                <div style="height:16px;"></div>
                <div style="background:rgba(255,255,255,0.03);border-radius:12px;padding:14px;border:1px solid var(--line);">
                    <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Nomor Invoice</div>
                    <div style="font-family:var(--mono);font-size:15px;font-weight:600;margin-top:6px;">{{ $order->order_no }}</div>
                    <div style="margin-top:8px;color:var(--accent);font-weight:700;">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</div>
                </div>

                @if($qrContent)
                    <div id="qris-code" style="background:#fff;padding:16px;width:272px;min-height:272px;margin:20px auto 12px;border-radius:12px;display:flex;align-items:center;justify-content:center;"></div>
                    <div id="payment-hint" class="muted" style="font-size:12px;line-height:1.6;">Menunggu konfirmasi pembayaran...</div>
                @else
                    <div style="margin-top:20px;color:#ef4444;font-size:13px;">{{ $qrError ?? 'QR pembayaran belum tersedia.' }}</div>
                @endif

                <div style="height:16px;"></div>
                <a class="btn btn-primary" href="{{ route('buyer.orders.show', $order) }}" style="width:100%;padding:14px;font-size:15px;">Lihat Pesanan Saya</a>
            </div>
        </div>
    </section>
@endsection

@if($qrContent)
    @push('head')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    @endpush

    @push('scripts')
        <script>
            (function () {
                var qrContent = @json($qrContent);
                var qrElement = document.getElementById('qris-code');
                var hint = document.getElementById('payment-hint');
                var statusUrl = @json(route('payment.ocbc.status', $order));
                var orderUrl = @json(route('buyer.orders.show', $order));
                var attempts = 0;

                new QRCode(qrElement, { text: qrContent, width: 240, height: 240 });

                function checkStatus() {
                    attempts++;
                    fetch(statusUrl)
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (data.paid || data.status === 'paid') {
                                hint.textContent = 'Pembayaran berhasil. Mengalihkan...';
                                window.location.href = orderUrl;
                                return;
                            }
                            if (data.status === 'failed' || data.status === 'expired') {
                                hint.textContent = 'Pembayaran gagal atau kedaluwarsa.';
                                return;
                            }
                            hint.textContent = 'Menunggu konfirmasi pembayaran... (' + attempts + ')';
                            window.setTimeout(checkStatus, 5000);
                        })
                        .catch(function () { window.setTimeout(checkStatus, 8000); });
                }

                checkStatus();
            })();
        </script>
    @endpush
@endif

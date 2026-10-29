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
                    <div id="qris-countdown" style="margin:20px auto 0;padding:10px 12px;border-radius:10px;background:rgba(255,255,255,0.04);border:1px solid var(--line);font-family:var(--mono);font-size:15px;font-weight:700;letter-spacing:1px;">
                        Sisa Waktu Pembayaran: <span id="qris-countdown-value">03:00</span>
                    </div>
                    <div id="qris-code" style="background:#fff;padding:16px;width:272px;min-height:272px;margin:12px auto;border-radius:12px;display:flex;align-items:center;justify-content:center;"></div>
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
    @push('scripts')
        @include('partials.qrcode-loader')

        <script>
            (function () {
                var qrContent = @json($qrContent);
                var statusUrl = @json(route('payment.ocbc.status', $order));
                var orderUrl = @json(route('buyer.orders.show', $order));
                var expiresAt = @json($expiresAt ?? null);
                var attempts = 0;
                var expired = false;
                var pollTimer = null;
                var deadline = parseDeadline(expiresAt);

                function parseDeadline(value) {
                    var parsed = value ? Date.parse(value) : NaN;
                    // Fallback: 3 menit dari sekarang kalau server tidak kirim expiry.
                    return isNaN(parsed) ? Date.now() + 180000 : parsed;
                }

                function pad(n) {
                    return (n < 10 ? '0' : '') + n;
                }

                function startCountdown() {
                    var valueEl = document.getElementById('qris-countdown-value');
                    var box = document.getElementById('qris-countdown');

                    function tick() {
                        var left = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
                        if (valueEl) valueEl.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);

                        if (left <= 0) {
                            expired = true;
                            if (pollTimer) {
                                clearTimeout(pollTimer);
                                pollTimer = null;
                            }
                            if (box) {
                                box.style.color = '#ef4444';
                                box.style.borderColor = 'rgba(239,68,68,0.4)';
                            }
                            setHint('QR Code Kadaluarsa / Expired. Pembayaran tidak dapat diproses lagi.');
                            return;
                        }

                        window.setTimeout(tick, 1000);
                    }

                    tick();
                }

                function fail() {
                    var qrElement = document.getElementById('qris-code');
                    var hint = document.getElementById('payment-hint');
                    if (qrElement) qrElement.innerHTML = '';
                    if (hint) hint.textContent = 'QR gagal dimuat. Muat ulang halaman atau buka detail pesanan untuk mencoba lagi.';
                }

                function setHint(text) {
                    var hint = document.getElementById('payment-hint');
                    if (hint) hint.textContent = text;
                }

                function schedule(fn, ms) {
                    pollTimer = window.setTimeout(fn, ms);
                }

                function checkStatus() {
                    if (expired) return;
                    attempts++;
                    fetch(statusUrl)
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (expired) return;
                            if (data.paid || data.status === 'paid') {
                                setHint('Pembayaran berhasil. Mengalihkan...');
                                window.location.href = orderUrl;
                                return;
                            }
                            if (data.status === 'failed' || data.status === 'expired') {
                                setHint('Pembayaran gagal atau kedaluwarsa.');
                                return;
                            }
                            setHint('Menunggu konfirmasi pembayaran... (' + attempts + ')');
                            schedule(checkStatus, 5000);
                        })
                        .catch(function () {
                            if (expired) return;
                            schedule(checkStatus, 8000);
                        });
                }

                function render() {
                    var qrElement = document.getElementById('qris-code');
                    if (!qrElement || typeof QRCode === 'undefined') {
                        fail();
                        return;
                    }

                    try {
                        new QRCode(qrElement, {
                            text: qrContent,
                            width: 240,
                            height: 240,
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    } catch (e) {
                        fail();
                        return;
                    }

                    checkStatus();
                }

                startCountdown();
                window.qrCodeReady(render);
            })();
        </script>
    @endpush
@endif

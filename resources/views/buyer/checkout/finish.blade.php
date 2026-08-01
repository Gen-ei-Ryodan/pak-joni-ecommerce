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

                @if($order->status === 'unpaid' && $order->payment_status === 'pending')
                    <div style="height:12px;"></div>
                    <div id="countdown-area" style="background:rgba(255,255,255,0.03);border-radius:12px;padding:14px;border:1px solid var(--line);text-align:center;">
                        <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Batas Waktu Pembayaran</div>
                        <div id="countdown-timer" style="font-family:var(--mono);font-size:18px;font-weight:700;margin-top:6px;color:var(--accent);">
                            {{ $order->paymentExpiresAt()->diffForHumans(null, true) }}
                        </div>
                        <div class="muted" style="font-size:11px;margin-top:4px;">
                            {{ $order->paymentExpiresAt()->isoFormat('dddd, D MMMM Y HH:mm') }} WIB
                        </div>
                        <div id="countdown-expired" style="display:none;margin-top:4px;color:#ef4444;font-weight:600;font-size:13px;">
                            Waktu pembayaran telah habis. Pesanan akan otomatis dibatalkan.
                        </div>
                    </div>
                    <script>
                        (function () {
                            var remaining = {{ $order->paymentRemainingSeconds() }};
                            var timerEl = document.getElementById('countdown-timer');
                            var expiredEl = document.getElementById('countdown-expired');
                            var payBtn = document.getElementById('pay-button');
                            var reopenArea = document.getElementById('reopen-area');

                            function pad(n) { return n < 10 ? '0' + n : n; }

                            function updateTimer() {
                                if (remaining <= 0) {
                                    timerEl.textContent = '00:00:00';
                                    timerEl.style.color = '#ef4444';
                                    if (expiredEl) expiredEl.style.display = 'block';
                                    if (payBtn) payBtn.style.display = 'none';
                                    if (reopenArea) reopenArea.style.display = 'none';
                                    return;
                                }
                                var h = Math.floor(remaining / 3600);
                                var m = Math.floor((remaining % 3600) / 60);
                                var s = remaining % 60;
                                timerEl.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
                                remaining--;
                            }

                            updateTimer();
                            setInterval(updateTimer, 1000);
                        })();
                    </script>
                @endif

                <div style="height:20px;"></div>

                @if($snapToken)
                    <button id="pay-button" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">
                        Bayar Sekarang — Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </button>

                    {{-- Re-open button (hidden initially, shown after popup closed) --}}
                    <div id="reopen-area" style="display:none;">
                        <div style="height:12px;"></div>
                        <div style="background:rgba(255,193,7,0.12);border:1px solid rgba(255,193,7,0.3);border-radius:10px;padding:12px;margin-bottom:12px;">
                            <div style="font-size:13px;color:#856404;text-align:center;">
                                Pembayaran belum selesai. Anda dapat membuka kembali halaman pembayaran.
                            </div>
                        </div>
                        <button id="reopen-button" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">
                            Buka Pembayaran Lagi
                        </button>
                        <div style="height:8px;"></div>
                        <button id="check-status-button" class="btn" style="width:100%;padding:12px;font-size:13px;">
                            Cek Status Pembayaran
                        </button>
                    </div>
                @else
                    <a class="btn btn-primary" href="{{ route('buyer.orders.show', $order) }}" style="width:100%;padding:14px;font-size:15px;">
                        Bayar Sekarang — Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </a>
                @endif

                <div style="height:12px;"></div>

                <a class="btn" href="{{ route('buyer.orders.show', $order) }}" style="width:100%;">Lihat Pesanan Saya</a>

                <div id="payment-hint" class="muted" style="margin-top:16px;font-size:12px;line-height:1.6;">
                    Setelah pembayaran selesai, halaman ini akan otomatis mengarahkan ke detail pesanan.
                </div>
            </div>
        </div>
    </section>
@endsection

@if($snapToken)
    @push('head')
        <script src="https://{{ config('services.midtrans.is_production') ? 'app.midtrans.com' : 'app.sandbox.midtrans.com' }}/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    @endpush

    @push('scripts')
        <script>
            var snapToken = '{{ $snapToken }}';
            var checkStatusUrl = '{{ route('payment.midtrans.status', $order) }}';
            var orderShowUrl = '{{ route('buyer.orders.show', $order) }}';
            var isPaying = false;
            var pollTimer = null;

            function startPolling(maxAttempts) {
                maxAttempts = maxAttempts || 30;
                var attempts = 0;
                var hint = document.getElementById('payment-hint');

                function poll() {
                    attempts++;
                    if (hint) {
                        hint.textContent = 'Memeriksa status pembayaran... (' + attempts + '/' + maxAttempts + ')';
                    }

                    fetch(checkStatusUrl)
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.paid || data.status === 'paid') {
                                stopPolling();
                                if (hint) hint.textContent = 'Pembayaran berhasil! Mengalihkan...';
                                setTimeout(function() { window.location.href = orderShowUrl; }, 1000);
                            } else if (data.status === 'failed' || data.status === 'expired') {
                                stopPolling();
                                alert('Pembayaran ' + (data.status === 'expired' ? 'kadaluwarsa' : 'gagal') + '.');
                                window.location.href = '{{ route('buyer.cart.index') }}';
                            } else if (attempts >= maxAttempts) {
                                stopPolling();
                                if (hint) hint.textContent = 'Status belum terkonfirmasi. Silakan cek di halaman pesanan.';
                                resetPayButtons();
                            } else {
                                pollTimer = setTimeout(poll, 3000);
                            }
                        })
                        .catch(function () {
                            if (attempts < maxAttempts) {
                                pollTimer = setTimeout(poll, 5000);
                            } else {
                                stopPolling();
                                resetPayButtons();
                            }
                        });
                }

                poll();
            }

            function stopPolling() {
                if (pollTimer) {
                    clearTimeout(pollTimer);
                    pollTimer = null;
                }
            }

            function resetPayButtons() {
                isPaying = false;
                var payBtn = document.getElementById('pay-button');
                var reopenBtn = document.getElementById('reopen-button');
                if (payBtn) {
                    payBtn.disabled = false;
                    payBtn.textContent = 'Bayar Sekarang — Rp {{ number_format((float) $order->total, 0, ',', '.') }}';
                }
                if (reopenBtn) {
                    reopenBtn.disabled = false;
                    reopenBtn.textContent = 'Buka Pembayaran Lagi';
                }
            }

            function openSnapPopup() {
                if (isPaying) return;
                isPaying = true;

                var payBtn = document.getElementById('pay-button');
                var reopenBtn = document.getElementById('reopen-button');
                if (payBtn) {
                    payBtn.disabled = true;
                    payBtn.textContent = 'Memproses...';
                }
                if (reopenBtn) {
                    reopenBtn.disabled = true;
                    reopenBtn.textContent = 'Memproses...';
                }

                snap.pay(snapToken, {
                    onSuccess: function(result) {
                        startPolling(30);
                    },
                    onPending: function(result) {
                        startPolling(30);
                    },
                    onError: function(result) {
                        resetPayButtons();
                        alert('Pembayaran gagal. Silakan coba lagi.');
                    },
                    onClose: function() {
                        isPaying = false;
                        if (payBtn) payBtn.style.display = 'none';
                        var reopenArea = document.getElementById('reopen-area');
                        if (reopenArea) reopenArea.style.display = 'block';
                        var hint = document.getElementById('payment-hint');
                        if (hint) {
                            hint.textContent = 'Pop-up pembayaran ditutup. Anda bisa membukanya kembali atau cek status.';
                        }
                    }
                });
            }

            document.getElementById('pay-button').addEventListener('click', openSnapPopup);

            var reopenBtn = document.getElementById('reopen-button');
            if (reopenBtn) {
                reopenBtn.addEventListener('click', openSnapPopup);
            }

            var checkBtn = document.getElementById('check-status-button');
            if (checkBtn) {
                checkBtn.addEventListener('click', function () {
                    checkBtn.disabled = true;
                    checkBtn.textContent = 'Memeriksa...';

                    fetch(checkStatusUrl)
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.paid || data.status === 'paid') {
                                window.location.href = orderShowUrl;
                            } else if (data.status === 'failed' || data.status === 'expired') {
                                alert('Pembayaran ' + (data.status === 'expired' ? 'kadaluwarsa' : 'gagal') + '. Silakan buat pesanan baru.');
                                window.location.href = '{{ route('buyer.cart.index') }}';
                            } else {
                                alert('Status: ' + (data.transaction_status || data.status) + '. Silakan selesaikan pembayaran Anda.');
                            }
                            checkBtn.disabled = false;
                            checkBtn.textContent = 'Cek Status Pembayaran';
                        })
                        .catch(function () {
                            alert('Gagal memeriksa status. Silakan coba lagi.');
                            checkBtn.disabled = false;
                            checkBtn.textContent = 'Cek Status Pembayaran';
                        });
                });
            }
        </script>
    @endpush
@endif

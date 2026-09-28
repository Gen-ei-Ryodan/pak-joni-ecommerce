<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OcbcPaymentService
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    public function generateQr(Order $order): array
    {
        $payment = $order->payment()->firstOrCreate(
            ['order_id' => $order->id],
            ['provider' => 'ocbc', 'status' => 'pending', 'payload' => []],
        );

        $payload = $payment->payload ?? [];
        if (! empty($payload['qr_content']) && $payment->provider === 'ocbc') {
            return [
                'success' => true,
                'qr_content' => $payload['qr_content'],
                'reference_no' => $payment->provider_reference,
            ];
        }

        $path = '/v2.0/qr/qr-mpm-generate';
        $body = [
            'merchantId' => $this->merchantId(),
            'terminalId' => $this->required('terminal_id'),
            'partnerReferenceNo' => $this->partnerReference($order),
            'amount' => [
                'value' => number_format((float) $order->total, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'additionalInfo' => [
                'memberBank' => $this->memberBank(),
            ],
        ];

        // Yokke tidak mengembalikan X-EXTERNAL-ID di response header (header itu
        // dikirim client -> server). Generate sendiri lalu simpan ke payload.
        $externalId = $this->externalId();
        $response = $this->request('POST', $path, $body, serviceCode: '47', externalId: $externalId);
        $data = $this->successfulJson($response);

        if (($data['responseCode'] ?? '') !== '2004700' || empty($data['qrContent'])) {
            throw new RuntimeException($data['responseMessage'] ?? 'OCBC gagal membuat QR pembayaran.');
        }

        $payment->update([
            'provider' => 'ocbc',
            'provider_reference' => $data['referenceNo'] ?? null,
            'status' => 'pending',
            'payload' => array_merge($payload, [
                'qr_content' => $data['qrContent'],
                'partner_reference_no' => (string) ($data['partnerReferenceNo'] ?? $body['partnerReferenceNo']),
                'external_id' => $externalId,
                'merchant_id' => $data['additionalInfo']['merchantId'] ?? $body['merchantId'],
                'terminal_id' => $data['terminalId'] ?? $body['terminalId'],
                'generated_response' => $data,
            ]),
        ]);

        return [
            'success' => true,
            'qr_content' => $data['qrContent'],
            'reference_no' => $data['referenceNo'] ?? null,
        ];
    }

    public function query(Order $order): array
    {
        $payment = $order->payment;
        $payload = $payment?->payload ?? [];
        $referenceNo = $payment?->provider_reference;

        if (! $payment || $payment->provider !== 'ocbc' || ! $referenceNo) {
            return ['paid' => $order->payment_status === 'paid', 'status' => 'pending'];
        }

        $path = '/v3.0/qr/qr-mpm-query';
        $body = [
            'originalReferenceNo' => $referenceNo,
            'originalExternalId' => $this->originalExternalId($payload),
            'serviceCode' => '47',
            'merchantId' => $this->merchantId(),
            'additionalInfo' => [
                'originalTransactionDate' => $order->created_at->format('Ymd'),
                'terminalId' => $payload['terminal_id'] ?? $this->required('terminal_id'),
                'memberBank' => $this->memberBank(),
            ],
        ];

        $data = $this->successfulJson($this->request('POST', $path, $body, serviceCode: '51'));
        $status = (string) ($data['latestTransactionStatus'] ?? '03');

        $this->applyStatus($order, $status, $data);

        return [
            'paid' => $status === '00',
            'status' => $this->localStatus($status),
            'transaction_status' => $status,
        ];
    }

    public function processNotification(array $payload, array $headers): array
    {
        if (! $this->verifyNotification($payload, $headers)) {
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        $payment = $this->findPayment($payload, $headers);

        if (! $payment?->order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $order = $payment->order;
        $amount = $this->notificationAmount($payload);
        if ($amount === null || abs($amount - (float) $order->total) > 0.001) {
            Log::warning('OCBC notification amount mismatch', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'expected' => (float) $order->total,
                'received' => $amount,
                'payload' => $payload,
            ]);

            return ['success' => false, 'message' => 'Amount mismatch'];
        }

        $status = (string) ($payload['latestTransactionStatus'] ?? '07');
        $this->applyStatus($order, $status, $payload);

        return ['success' => true, 'message' => 'Notification processed'];
    }

    /**
     * Lookup payment fleksibel: Yokke/MTI tidak selalu mengirim referensi yang sama
     * dengan yang tersimpan (format JSON, referensi beda digit, atau hanya
     * merchant/terminal). X-EXTERNAL-ID di header adalah external_id generate QR.
     */
    private function findPayment(array $payload, array $headers): ?Payment
    {
        $candidates = $this->notificationReferences($payload, $headers);
        $amount = $this->notificationAmount($payload);

        $payment = $this->matchExactReference($candidates);
        if ($payment) {
            Log::info('OCBC notification matched by reference', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'candidates' => $candidates,
            ]);

            return $payment;
        }

        $payment = $this->matchFuzzyReference($candidates, $amount);
        if ($payment) {
            Log::info('OCBC notification matched by fuzzy reference', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'candidates' => $candidates,
            ]);

            return $payment;
        }

        $payment = $this->matchMerchantTerminal($payload, $amount);
        if ($payment) {
            Log::warning('OCBC notification matched by merchant/terminal fallback', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'candidates' => $candidates,
            ]);

            return $payment;
        }

        Log::warning('OCBC notification payment not found', [
            'candidates' => $candidates,
            'amount' => $amount,
            'payload' => $payload,
        ]);

        return null;
    }

    /**
     * Semua kandidat identifier dari body notify + header X-EXTERNAL-ID,
     * dinormalisasi (bisa terkirim sebagai JSON string/array).
     */
    private function notificationReferences(array $payload, array $headers): array
    {
        $keys = [
            'originalReferenceNo',
            'referenceNo',
            'originalPartnerReferenceNo',
            'partnerReferenceNo',
            'additionalInfo.originalReferenceNo',
            'additionalInfo.partnerReferenceNo',
            'additionalInfo.originalPartnerReferenceNo',
        ];

        $references = [];
        foreach ($keys as $key) {
            $value = $this->normalizeReference(data_get($payload, $key));
            if ($value !== null) {
                $references[$value] = $value;
            }
        }

        foreach ([$headers['x-external-id'] ?? null, $payload['external_id'] ?? null] as $value) {
            $value = $this->normalizeReference($value);
            if ($value !== null) {
                $references[$value] = $value;
            }
        }

        return array_values($references);
    }

    private function matchExactReference(array $candidates): ?Payment
    {
        if ($candidates === []) {
            return null;
        }

        return Payment::query()
            ->where('provider', 'ocbc')
            ->where(function ($query) use ($candidates) {
                $first = true;
                foreach ($candidates as $candidate) {
                    foreach (['provider_reference', 'payload->partner_reference_no', 'payload->external_id'] as $column) {
                        if ($first) {
                            $query->where($column, $candidate);
                            $first = false;
                        } else {
                            $query->orWhere($column, $candidate);
                        }
                    }
                }

                if ($first) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->with('order')
            ->latest()
            ->first();
    }

    /**
     * originalReferenceNo kadang beda beberapa digit (leading zero / typo ampuh).
     * Batasi ke pembayaran 2 hari terakhir + amount cocok supaya tidak salah kait.
     */
    private function matchFuzzyReference(array $candidates, ?float $amount): ?Payment
    {
        if ($candidates === [] || $amount === null) {
            return null;
        }

        $payments = Payment::query()
            ->where('provider', 'ocbc')
            ->where('created_at', '>=', now()->subDays(2))
            ->with('order')
            ->latest()
            ->limit(500)
            ->get();

        foreach ($payments as $payment) {
            if (! $this->amountMatches($payment->order, $amount)) {
                continue;
            }

            foreach (['provider_reference', 'payload.partner_reference_no', 'payload.external_id'] as $field) {
                $stored = $this->normalizeReference(data_get($payment, $field));
                if ($stored === null) {
                    continue;
                }

                foreach ($candidates as $candidate) {
                    if ($this->closeEnough($stored, $candidate)) {
                        return $payment;
                    }
                }
            }
        }

        return null;
    }

    private function matchMerchantTerminal(array $payload, ?float $amount): ?Payment
    {
        $merchantId = $this->normalizeReference(data_get($payload, 'additionalInfo.merchantId')
            ?? data_get($payload, 'merchant_id')
            ?? data_get($payload, 'merchantId'));
        $terminalId = $this->normalizeReference(data_get($payload, 'additionalInfo.terminalId')
            ?? data_get($payload, 'terminal_id')
            ?? data_get($payload, 'terminalId'));

        if ($merchantId === null || $terminalId === null) {
            return null;
        }

        $merchantId = str_pad($merchantId, 15, '0', STR_PAD_LEFT);

        return Payment::query()
            ->where('provider', 'ocbc')
            ->where('payload->terminal_id', $terminalId)
            ->where('created_at', '>=', now()->subDays(2))
            ->with('order')
            ->latest()
            ->limit(50)
            ->get()
            ->first(function (Payment $payment) use ($merchantId, $amount) {
                $stored = str_pad((string) ($payment->payload['merchant_id'] ?? ''), 15, '0', STR_PAD_LEFT);

                return $stored === $merchantId
                    && ($amount === null || $this->amountMatches($payment->order, $amount));
            });
    }

    private function closeEnough(string $stored, string $candidate): bool
    {
        $stored = ltrim($stored, '0');
        $candidate = ltrim($candidate, '0');

        if ($stored === '' || $candidate === '') {
            return false;
        }

        return $stored === $candidate
            || (strlen($stored) === strlen($candidate) && levenshtein($stored, $candidate) <= 2);
    }

    private function amountMatches(?Order $order, ?float $amount): bool
    {
        return $order !== null && $amount !== null && abs($amount - (float) $order->total) <= 0.001;
    }

    private function notificationAmount(array $payload): ?float
    {
        $value = data_get($payload, 'amount.value');
        if ($value === null) {
            // Dokumen Yokke memakai "amount " (spasi) pada beberapa contoh payload.
            $value = data_get($payload, 'amount ', data_get($payload, 'amount'));
        }

        if (is_array($value)) {
            $value = $value['value'] ?? null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeReference(mixed $value): ?string
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $normalized = $this->normalizeReference($item);
                if ($normalized !== null) {
                    return $normalized;
                }
            }

            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '"') || str_starts_with($value, '[') || str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizeReference($decoded);
            }
        }

        return $value;
    }

    private function applyStatus(Order $order, string $status, array $payload): void
    {
        $providerReference = $this->normalizeReference($payload['originalReferenceNo'] ?? null)
            ?? $order->payment?->provider_reference;

        if ($status === '00' && $order->payment_status !== 'paid') {
            $this->orderService->markAsPaid($order, [
                'payment_method' => 'qris',
                'payment_provider' => 'ocbc',
                'payment_reference' => $providerReference,
            ]);
        }

        $paymentStatus = match ($status) {
            '00' => 'success',
            '05', '06' => 'failed',
            default => 'pending',
        };

        $order->payment()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'provider' => 'ocbc',
                'provider_reference' => $providerReference,
                'status' => $paymentStatus,
                'payload' => array_merge($order->payment?->payload ?? [], [
                    'last_status' => $status,
                    'last_status_payload' => $payload,
                ]),
            ],
        );
    }

    private function request(string $method, string $path, array $body, string $serviceCode, ?string $externalId = null): Response
    {
        $token = $this->accessToken();
        $timestamp = $this->timestamp();
        $externalId ??= $this->externalId();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $this->hmacSignature($method, $path, $token, $body, $timestamp),
            'X-EXTERNAL-ID' => $externalId,
            'X-PARTNER-ID' => $this->required('partner_id'),
            'CHANNEL-ID' => $this->required('channel_id'),
        ];

        // Retry hanya untuk gangguan koneksi. Retry pada response error 4xx/5xx
        // akan mengulang request dengan X-EXTERNAL-ID yang sama → ditolak OCBC 409
        // ("Cannot use same X-EXTERNAL-ID in same day").
        return Http::withHeaders($headers)
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 500, fn ($e) => $e instanceof ConnectionException, throw: false)
            ->send($method, rtrim($this->required('base_url'), '/').$path, ['json' => $body]);
    }

    private function accessToken(): string
    {
        return Cache::remember('ocbc.access_token', now()->addSeconds(840), function () {
            $timestamp = $this->timestamp();
            $clientKey = $this->required('client_key');
            $signature = $this->rsaSignature($clientKey.'|'.$timestamp);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-TIMESTAMP' => $timestamp,
                'X-CLIENT-KEY' => $clientKey,
                'X-SIGNATURE' => $signature,
            ])->timeout(20)->retry(2, 500, throw: false)->post(
                rtrim($this->required('base_url'), '/').'/qr/v2.0/access-token/b2b',
                ['grantType' => 'client_credentials'],
            );

            $data = $this->successfulJson($response);
            if (empty($data['accessToken'])) {
                throw new RuntimeException($data['responseMessage'] ?? 'OCBC tidak mengembalikan access token.');
            }

            return $data['accessToken'];
        });
    }

    private function hmacSignature(string $method, string $path, string $token, array $body, string $timestamp): string
    {
        $bodyHash = strtolower(hash('sha256', $this->minify($body)));
        $stringToSign = $method.':'.$path.':'.$token.':'.$bodyHash.':'.$timestamp;

        return base64_encode(hash_hmac('sha512', $stringToSign, $this->required('client_secret'), true));
    }

    private function timestamp(): string
    {
        return now('Asia/Jakarta')->toIso8601String();
    }

    private function verifyNotification(array $payload, array $headers): bool
    {
        $signature = $headers['x-signature'] ?? '';
        $timestamp = $headers['x-timestamp'] ?? '';
        if ($signature === '' || $timestamp === '') {
            return false;
        }

        $path = '/v1.0/qr/qr-mpm-notify';
        $bodyHash = strtolower(hash('sha256', $this->minify($payload)));
        $stringToSign = 'POST:'.$path.':'.$bodyHash.':'.$timestamp;

        if (config('services.ocbc.notify_signature_mode', 'hmac') === 'rsa') {
            $publicKeyPath = config('services.ocbc.notify_public_key_path');
            $publicKey = is_string($publicKeyPath) ? @openssl_pkey_get_public(@file_get_contents($publicKeyPath)) : false;
            if (! $publicKey) {
                return false;
            }

            $encodedSignature = config('services.ocbc.notify_signature_encoding', 'base64') === 'hex'
                ? hex2bin($signature)
                : base64_decode($signature, true);

            return is_string($encodedSignature)
                && openssl_verify($stringToSign, $encodedSignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
        }

        $expected = base64_encode(hash_hmac('sha512', $stringToSign, $this->required('client_secret'), true));

        return hash_equals($expected, $signature);
    }

    private function rsaSignature(string $value): string
    {
        $keyPath = $this->required('private_key_path');
        $privateKey = @openssl_pkey_get_private(file_get_contents($keyPath));
        if (! $privateKey || ! openssl_sign($value, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('OCBC private key tidak valid atau tidak dapat digunakan.');
        }

        return config('services.ocbc.signature_encoding', 'base64') === 'hex'
            ? bin2hex($signature)
            : base64_encode($signature);
    }

    private function successfulJson(Response $response): array
    {
        $data = $response->json();
        if (! $response->successful() || ! is_array($data)) {
            $body = $response->body();
            Log::error('OCBC API request failed', [
                'status' => $response->status(),
                'response_code' => $data['responseCode'] ?? null,
                'response_message' => $data['responseMessage'] ?? null,
                'body' => mb_substr($body, 0, 2000),
            ]);

            throw new RuntimeException(sprintf(
                'OCBC API error HTTP %d [%s]: %s',
                $response->status(),
                $data['responseCode'] ?? '-',
                $data['responseMessage'] ?? mb_substr($body, 0, 500),
            ));
        }

        return $data;
    }

    private function minify(array $body): string
    {
        return json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * OCBC/Yokke hanya menerima partnerReferenceNo numeric tepat 20 digit
     * (4004701 "Invalid Field Format" untuk order_no berhuruf).
     * Format: ymd (6) + order_id (10, padded) + random (4) — unik per attempt.
     */
    private function partnerReference(Order $order): string
    {
        return $order->created_at->format('ymd')
            .str_pad((string) $order->id, 10, '0', STR_PAD_LEFT)
            .str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * originalExternalId wajib numeric (Yokke: 15 digit). Ambil external_id yang
     * dikirim saat generate QR; kalau payload lama kosong/bukan numeric, fallback
     * ke format tanggal YmdHis+digit agar request tetap diterima.
     */
    private function originalExternalId(array $payload): string
    {
        $externalId = trim((string) ($payload['external_id'] ?? ''));
        if ($externalId === '' || ! ctype_digit($externalId) || strlen($externalId) > 15) {
            return $this->externalId();
        }

        return $externalId;
    }

    private function externalId(): string
    {
        return now()->format('YmdHis').random_int(0, 9);
    }

    private function localStatus(string $status): string
    {
        return match ($status) {
            '00' => 'paid',
            '05', '06' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Yokke/OCBC merchantId harus 15 digit; credential 11 digit di-pad kiri.
     */
    private function merchantId(): string
    {
        return str_pad($this->required('merchant_id'), 15, '0', STR_PAD_LEFT);
    }

    private function memberBank(): string
    {
        $value = config('services.ocbc.member_bank');

        return is_string($value) && trim($value) !== '' ? $value : '028';
    }

    private function required(string $key): string
    {
        $value = config('services.ocbc.'.$key);
        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException('OCBC configuration missing: '.$key);
        }

        return $value;
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
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
            'merchantId' => $this->required('merchant_id'),
            'terminalId' => $this->required('terminal_id'),
            'partnerReferenceNo' => $this->partnerReference($order),
            'amount' => [
                'value' => number_format((float) $order->total, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'additionalInfo' => [
                'memberBank' => config('services.ocbc.member_bank', '999'),
            ],
        ];

        $response = $this->request('POST', $path, $body, serviceCode: '47');
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
                'external_id' => $response->header('X-EXTERNAL-ID'),
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
            'originalExternalId' => $payload['external_id'] ?? $this->externalId(),
            'serviceCode' => '47',
            'merchantId' => $this->required('merchant_id'),
            'additionalInfo' => [
                'originalTransactionDate' => $order->created_at->format('Ymd'),
                'terminalId' => $payload['terminal_id'] ?? $this->required('terminal_id'),
                'memberBank' => config('services.ocbc.member_bank', '999'),
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

        $referenceNo = (string) ($payload['originalReferenceNo'] ?? '');
        $partnerReferenceNo = (string) ($payload['originalPartnerReferenceNo'] ?? '');
        $payment = Payment::query()
            ->where('provider', 'ocbc')
            ->where(function ($query) use ($referenceNo, $partnerReferenceNo) {
                $query->where('provider_reference', $referenceNo);
                if ($partnerReferenceNo !== '') {
                    $query->orWhereJsonContains('payload->partner_reference_no', $partnerReferenceNo);
                }
            })
            ->with('order')
            ->first();

        if (! $payment?->order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $order = $payment->order;
        $amount = (float) data_get($payload, 'amount.value', 0);
        if (abs($amount - (float) $order->total) > 0.001) {
            Log::warning('OCBC notification amount mismatch', [
                'order_id' => $order->id,
                'expected' => (float) $order->total,
                'received' => $amount,
            ]);

            return ['success' => false, 'message' => 'Amount mismatch'];
        }

        $status = (string) ($payload['latestTransactionStatus'] ?? '07');
        $this->applyStatus($order, $status, $payload);

        return ['success' => true, 'message' => 'Notification processed'];
    }

    private function applyStatus(Order $order, string $status, array $payload): void
    {
        if ($status === '00' && $order->payment_status !== 'paid') {
            $this->orderService->markAsPaid($order, [
                'payment_method' => 'qris',
                'payment_provider' => 'ocbc',
                'payment_reference' => $payload['originalReferenceNo'] ?? $order->payment?->provider_reference,
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
                'provider_reference' => $payload['originalReferenceNo'] ?? $order->payment?->provider_reference,
                'status' => $paymentStatus,
                'payload' => array_merge($order->payment?->payload ?? [], [
                    'last_status' => $status,
                    'last_status_payload' => $payload,
                ]),
            ],
        );
    }

    private function request(string $method, string $path, array $body, string $serviceCode): Response
    {
        $token = $this->accessToken();
        $timestamp = now()->toIso8601String();
        $externalId = $this->externalId();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $this->hmacSignature($method, $path, $token, $body, $timestamp),
            'X-EXTERNAL-ID' => $externalId,
            'X-PARTNER-ID' => $this->required('partner_id'),
            'CHANNEL-ID' => $this->required('channel_id'),
        ];

        return Http::withHeaders($headers)
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 500, throw: false)
            ->send($method, rtrim($this->required('base_url'), '/').$path, ['json' => $body]);
    }

    private function accessToken(): string
    {
        return Cache::remember('ocbc.access_token', now()->addSeconds(840), function () {
            $timestamp = now()->toIso8601String();
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
            throw new RuntimeException('OCBC API error: '.$response->status());
        }

        return $data;
    }

    private function minify(array $body): string
    {
        return json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function partnerReference(Order $order): string
    {
        return substr($order->order_no, 0, 20);
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

    private function required(string $key): string
    {
        $value = config('services.ocbc.'.$key);
        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException('OCBC configuration missing: '.$key);
        }

        return $value;
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OcbcPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class OcbcPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_ocbc_notification_marks_order_paid(): void
    {
        config(['services.ocbc.client_secret' => 'test-secret']);

        $user = User::factory()->create(['role' => 'buyer']);
        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ'.now()->format('ymd').'OCBC01',
            'status' => 'unpaid',
            'payment_status' => 'pending',
            'subtotal' => 500000,
            'shipping_cost' => 0,
            'total' => 500000,
            'address_snapshot' => [],
        ]);
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'ocbc',
            'provider_reference' => 'OCBC-REF-1',
            'status' => 'pending',
            'payload' => [],
        ]);

        $payload = [
            'originalReferenceNo' => 'OCBC-REF-1',
            'latestTransactionStatus' => '00',
            'transactionStatusDesc' => 'Success',
            'amount' => ['value' => '500000.00', 'currency' => 'IDR'],
        ];
        $timestamp = now()->toIso8601String();
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $stringToSign = 'POST:/v1.0/qr/qr-mpm-notify:'.strtolower(hash('sha256', $body)).':'.$timestamp;
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, 'test-secret', true));

        $this->withHeaders([
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
        ])->postJson('/v1.0/qr/qr-mpm-notify', $payload)
            ->assertOk()
            ->assertJsonPath('responseCode', '2005200');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);
    }

    public function test_invalid_ocbc_notification_is_rejected(): void
    {
        config(['services.ocbc.client_secret' => 'test-secret']);

        $this->withHeaders([
            'X-TIMESTAMP' => now()->toIso8601String(),
            'X-SIGNATURE' => 'invalid',
        ])->postJson('/v1.0/qr/qr-mpm-notify', [])
            ->assertUnauthorized();
    }

    public function test_config_default_member_bank_is_028(): void
    {
        $this->assertSame('028', config('services.ocbc.member_bank'));

        config(['services.ocbc.member_bank' => null]);
        $service = app(OcbcPaymentService::class);
        $memberBank = new \ReflectionMethod($service, 'memberBank');
        $this->assertSame('028', $memberBank->invoke($service));

        config(['services.ocbc.member_bank' => '999']);
        $this->assertSame('999', $memberBank->invoke($service), 'env override tetap dihormati');
    }

    public function test_merchant_id_is_padded_to_15_digits(): void
    {
        $service = app(OcbcPaymentService::class);
        $merchantId = new \ReflectionMethod($service, 'merchantId');

        config(['services.ocbc.merchant_id' => '71007568773']);
        $this->assertSame('000071007568773', $merchantId->invoke($service));

        config(['services.ocbc.merchant_id' => '000071007568773']);
        $this->assertSame('000071007568773', $merchantId->invoke($service), 'sudah 15 digit tidak double-pad');
    }

    public function test_partner_reference_is_20_numeric_digits_and_traceable(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ'.now()->format('ymd').'OCBC02',
            'status' => 'unpaid',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total' => 100000,
            'address_snapshot' => [],
        ]);

        $service = app(OcbcPaymentService::class);
        $method = new \ReflectionMethod($service, 'partnerReference');

        $ref = $method->invoke($service, $order);

        $this->assertMatchesRegularExpression('/^\d{20}$/', $ref);
        $this->assertSame($order->created_at->format('ymd'), substr($ref, 0, 6));
        $this->assertSame(str_pad((string) $order->id, 10, '0', STR_PAD_LEFT), substr($ref, 6, 10));
        $this->assertMatchesRegularExpression('/^\d{4}$/', substr($ref, 16, 4));

        $refs = array_map(fn () => $method->invoke($service, $order), range(1, 50));
        $this->assertGreaterThan(1, count(array_unique($refs)));
    }

    public function test_generate_qr_persists_the_external_id_it_sent(): void
    {
        $order = $this->makeOrder(500000, 'EXT01');
        $this->fakeOcbc();

        app(OcbcPaymentService::class)->generateQr($order);

        $sentExternalId = $this->sentHeader('qr-mpm-generate', 'X-EXTERNAL-ID');
        $this->assertMatchesRegularExpression('/^\d{15}$/', $sentExternalId);

        $payment = $order->payment()->firstOrFail();
        $this->assertSame($sentExternalId, $payment->payload['external_id']);
        $this->assertNotNull($payment->provider_reference);
    }

    public function test_query_reuses_stored_external_id_and_sends_fresh_header(): void
    {
        $order = $this->makeOrder(500000, 'EXT02');
        $this->fakeOcbc();

        app(OcbcPaymentService::class)->generateQr($order);
        $stored = $order->payment()->firstOrFail()->payload['external_id'];

        app(OcbcPaymentService::class)->query($order);

        $sent = $this->sentRequest('qr-mpm-query');
        $body = json_decode($sent->body(), true);
        $this->assertSame($stored, $body['originalExternalId']);
        $this->assertMatchesRegularExpression('/^\d{15}$/', $sent->header('X-EXTERNAL-ID')[0] ?? '');
        $this->assertNotEmpty($body['originalReferenceNo']);
    }

    public function test_query_falls_back_when_payload_external_id_is_empty(): void
    {
        $order = $this->makeOrder(500000, 'EXT03');
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'ocbc',
            'provider_reference' => '506511669694',
            'status' => 'pending',
            'payload' => ['external_id' => ''],
        ]);
        $this->fakeOcbc();

        app(OcbcPaymentService::class)->query($order);

        $body = json_decode($this->sentRequest('qr-mpm-query')->body(), true);
        $this->assertMatchesRegularExpression('/^\d{14,15}$/', $body['originalExternalId']);
        $this->assertSame('506511669694', $body['originalReferenceNo']);
    }

    public function test_notify_logs_full_payload_and_headers(): void
    {
        Log::spy();

        $payload = [
            'originalReferenceNo' => '506511669694',
            'latestTransactionStatus' => '00',
            'amount' => ['value' => '500000.00', 'currency' => 'IDR'],
        ];

        $this->withHeaders($this->notifyHeaders($payload, '202609280701122'))
            ->postJson('/v1.0/qr/qr-mpm-notify', $payload);

        Log::shouldHaveReceived('info')->withArgs(
            fn ($message, $context) => $message === 'OCBC notify received'
                && ($context['payload']['originalReferenceNo'] ?? null) === '506511669694'
                && array_key_exists('x-timestamp', $context['headers'] ?? [])
                && array_key_exists('x-signature', $context['headers'] ?? [])
                && ($context['external_id'] ?? null) === '202609280701122'
        )->once();

        Log::shouldHaveReceived('debug')->withArgs(
            fn ($message, $context) => $message === 'OCBC notify raw body'
                && str_contains((string) ($context['raw'] ?? ''), 'originalReferenceNo')
        )->once();
    }

    public function test_notify_matches_by_external_id_header_when_reference_is_unknown(): void
    {
        $order = $this->makeOrder(500000, 'NTF01');
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'ocbc',
            'provider_reference' => '506511669694',
            'status' => 'pending',
            'payload' => ['external_id' => '202609280701122'],
        ]);

        $payload = [
            'originalReferenceNo' => '999999999999',
            'latestTransactionStatus' => '00',
            'transactionStatusDesc' => 'Success',
            'amount' => ['value' => '500000.00', 'currency' => 'IDR'],
        ];

        $this->withHeaders($this->notifyHeaders($payload, '202609280701122'))
            ->postJson('/v1.0/qr/qr-mpm-notify', $payload)
            ->assertOk()
            ->assertJsonPath('responseCode', '2005200');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }

    public function test_notify_matches_json_encoded_original_reference_no(): void
    {
        $order = $this->makeOrder(500000, 'NTF02');
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'ocbc',
            'provider_reference' => '506511669694',
            'status' => 'pending',
            'payload' => [],
        ]);

        $payload = [
            'originalReferenceNo' => '"506511669694"',
            'latestTransactionStatus' => '00',
            'amount' => ['value' => '500000.00', 'currency' => 'IDR'],
        ];

        $this->withHeaders($this->notifyHeaders($payload, '202609280701123'))
            ->postJson('/v1.0/qr/qr-mpm-notify', $payload)
            ->assertOk()
            ->assertJsonPath('responseCode', '2005200');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }

    public function test_notify_matches_when_reference_differs_by_two_digits(): void
    {
        $order = $this->makeOrder(500000, 'NTF03');
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'ocbc',
            'provider_reference' => '506511669694',
            'status' => 'pending',
            'payload' => [],
        ]);

        $payload = [
            'originalReferenceNo' => '506511669696',
            'latestTransactionStatus' => '00',
            'amount' => ['value' => '500000.00', 'currency' => 'IDR'],
        ];

        $this->withHeaders($this->notifyHeaders($payload, '202609280701124'))
            ->postJson('/v1.0/qr/qr-mpm-notify', $payload)
            ->assertOk()
            ->assertJsonPath('responseCode', '2005200');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }

    public function test_notify_falls_back_to_merchant_and_terminal(): void
    {
        $order = $this->makeOrder(500000, 'NTF04');
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'ocbc',
            'provider_reference' => '506511669694',
            'status' => 'pending',
            'payload' => ['merchant_id' => '000071007568773', 'terminal_id' => '72001126'],
        ]);

        $payload = [
            'originalReferenceNo' => '888888888888',
            'latestTransactionStatus' => '00',
            'amount' => ['value' => '500000.00', 'currency' => 'IDR'],
            'additionalInfo' => ['merchantId' => '71007568773', 'terminalId' => '72001126'],
        ];

        $this->withHeaders($this->notifyHeaders($payload, '202609280701125'))
            ->postJson('/v1.0/qr/qr-mpm-notify', $payload)
            ->assertOk()
            ->assertJsonPath('responseCode', '2005200');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }

    public function test_notify_still_rejects_amount_mismatch(): void
    {
        $order = $this->makeOrder(500000, 'NTF05');
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'ocbc',
            'provider_reference' => '506511669694',
            'status' => 'pending',
            'payload' => [],
        ]);

        $payload = [
            'originalReferenceNo' => '506511669694',
            'latestTransactionStatus' => '00',
            'amount' => ['value' => '100000.00', 'currency' => 'IDR'],
        ];

        $this->withHeaders($this->notifyHeaders($payload, '202609280701126'))
            ->postJson('/v1.0/qr/qr-mpm-notify', $payload)
            ->assertUnauthorized();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
    }

    private function makeOrder(float $total, string $suffix): Order
    {
        $user = User::factory()->create(['role' => 'buyer']);

        return Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ'.now()->format('ymd').$suffix,
            'status' => 'unpaid',
            'payment_status' => 'pending',
            'subtotal' => $total,
            'shipping_cost' => 0,
            'total' => $total,
            'address_snapshot' => [],
        ]);
    }

    private function fakeOcbc(): void
    {
        config([
            'services.ocbc.base_url' => 'https://tst.yokke.co.id:7778',
            'services.ocbc.client_key' => 'test-client-key',
            'services.ocbc.client_secret' => 'test-secret',
            'services.ocbc.merchant_id' => '71007568773',
            'services.ocbc.terminal_id' => '72001126',
            'services.ocbc.partner_id' => 'MTI-STORE',
            'services.ocbc.channel_id' => '02',
            'services.ocbc.private_key_path' => storage_path('app/private/ocbc-client-private.pem'),
            'services.ocbc.notify_signature_mode' => 'hmac',
        ]);

        Http::fake([
            '*access-token*' => Http::response(['accessToken' => 'test-token']),
            '*qr-mpm-generate*' => Http::response([
                'responseCode' => '2004700',
                'responseMessage' => 'Successful',
                'referenceNo' => '506511669694',
                'partnerReferenceNo' => '26092800000000011234',
                'qrContent' => '00020101021226610014COM.GO-JEK.WWW01189360091432506147410210G2506147410303UMI51440014ID.CO.QRIS.WWW0215ID10200276412010303UMI5204581253033605802ID5909TEMANJON6007JAKARTA61051011063046344',
                'additionalInfo' => ['merchantId' => '000071007568773'],
                'terminalId' => '72001126',
            ]),
            '*qr-mpm-query*' => Http::response([
                'responseCode' => '2005100',
                'responseMessage' => 'Successful',
                'originalReferenceNo' => '506511669694',
                'latestTransactionStatus' => '03',
                'transactionStatusDesc' => 'Pending',
                'amount' => ['value' => '500000.00', 'currency' => 'IDR'],
            ]),
        ]);
    }

    private function sentRequest(string $needle): Request
    {
        $pair = Http::recorded(fn (Request $request) => str_contains($request->url(), $needle))->first();
        $this->assertNotNull($pair, "request ke {$needle} tidak tercatat");

        return $pair[0];
    }

    private function sentHeader(string $needle, string $header): string
    {
        return (string) ($this->sentRequest($needle)->header($header)[0] ?? '');
    }

    private function notifyHeaders(array $payload, string $externalId): array
    {
        config(['services.ocbc.client_secret' => 'test-secret']);
        $timestamp = now()->toIso8601String();
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $stringToSign = 'POST:/v1.0/qr/qr-mpm-notify:'.strtolower(hash('sha256', $body)).':'.$timestamp;

        return [
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => base64_encode(hash_hmac('sha512', $stringToSign, 'test-secret', true)),
            'X-EXTERNAL-ID' => $externalId,
        ];
    }
}

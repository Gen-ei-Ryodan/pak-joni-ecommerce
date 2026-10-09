<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OcbcPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

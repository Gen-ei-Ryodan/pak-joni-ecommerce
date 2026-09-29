<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\OcbcPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentQrFixTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $user): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ'.now()->format('ymd').'QRFIX1',
            'status' => 'unpaid',
            'payment_status' => 'pending',
            'subtotal' => 23000,
            'shipping_cost' => 0,
            'total' => 23000,
            'address_snapshot' => [],
        ]);
    }

    public function test_sustained_status_polling_is_not_throttled(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($user);

        $this->actingAs($user);

        // Finish page polls status tiap 5 detik (12/menit). Limit lama 10/menit
        // membuat request ke-11 balik 429 HTML.
        for ($i = 1; $i <= 30; $i++) {
            $response = $this->getJson("/payment/ocbc/status/{$order->id}");
            $this->assertNotEquals(429, $response->getStatusCode(), "status request #{$i} ter-throttle");
            $response->assertOk();
        }
    }

    public function test_generate_qr_still_works_after_status_polling(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($user);

        $service = $this->mock(OcbcPaymentService::class);
        $service->shouldReceive('query')->andReturn(['paid' => false, 'status' => 'pending']);
        $service->shouldReceive('generateQr')->andReturn(['success' => true, 'qr_content' => '000201FIXTEST']);

        $this->actingAs($user);

        for ($i = 0; $i < 15; $i++) {
            $this->getJson("/payment/ocbc/status/{$order->id}")->assertOk();
        }

        $this->getJson("/payment/ocbc/qr/{$order->id}")
            ->assertOk()
            ->assertJsonPath('qr_content', '000201FIXTEST');
    }

    public function test_finish_page_loads_local_qrcode_script_with_fallback(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($user);

        $this->mock(OcbcPaymentService::class)
            ->shouldReceive('generateQr')
            ->andReturn(['success' => true, 'qr_content' => '000201FIXTEST']);

        $response = $this->actingAs($user)->get('/checkout/finish/'.$order->id);

        $response->assertOk();
        $response->assertSee('assets/js/qrcode.min.js');
        $response->assertDontSee('cdnjs.cloudflare.com');
        $response->assertSee('QR gagal dimuat');
        // QR dirender hanya setelah library siap (tanpa race window.QRCode).
        $response->assertSee('qrCodeReady');
        $this->assertFileExists(public_path('assets/js/qrcode.min.js'));
    }

    public function test_order_detail_page_loads_local_qrcode_script(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($user);

        $response = $this->actingAs($user)->get('/my/orders/'.$order->order_no);

        $response->assertOk();
        $response->assertSee('assets/js/qrcode.min.js');
        $response->assertDontSee('cdnjs.cloudflare.com');
        $response->assertSee('Terlalu banyak permintaan');
        // Tombol bayar mana pun wajib membuka modal QRIS di tengah layar.
        $response->assertSee('qrCodeReady');
        $response->assertSee('scrollIntoView');
        $response->assertSee('id="qris-modal"', false);
        $response->assertSee('Download QR');
        $response->assertSee('Pembayaran QRIS');
        // Kontainer QR hanya boleh ada di modal (bukan di sidebar / banner).
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'id="qris-payment"'),
            'id qris-payment hanya boleh ada satu (di modal)'
        );
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'id="payment-banner-text"'),
            'id payment-banner-text tidak boleh duplikat (banner & sidebar)'
        );
        // Banner atas tidak boleh lagi menampilkan progres QR (menyesatkan user).
        $this->assertStringNotContainsString(
            'js-pay-status',
            $response->getContent(),
            'teks banner tidak boleh diubah jadi status QR'
        );
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A forged GET to the Midtrans "finish" redirect must NOT mark an order as paid
     * without a signed server-to-server notification.
     */
    public function test_payment_finish_redirect_does_not_mark_order_paid(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ'.now()->format('ymd').'TEST01',
            'status' => 'unpaid',
            'payment_status' => 'pending',
            'subtotal' => 500000,
            'shipping_cost' => 0,
            'total' => 500000,
            'address_snapshot' => [],
        ]);

        $this->actingAs($user)
            ->get('/payment/midtrans/finish?order_id='.$order->order_no.'&transaction_status=settlement')
            ->assertRedirect(route('buyer.orders.show', $order));

        $order->refresh();
        $this->assertSame('unpaid', $order->status);
        $this->assertSame('pending', $order->payment_status);
    }

    /**
     * A guest (unauthenticated) hitting the finish URL must simply be redirected,
     * never trigger a payment state change.
     */
    public function test_payment_finish_redirect_unauthenticated_does_not_process_payment(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ'.now()->format('ymd').'TEST02',
            'status' => 'unpaid',
            'payment_status' => 'pending',
            'subtotal' => 250000,
            'shipping_cost' => 0,
            'total' => 250000,
            'address_snapshot' => [],
        ]);

        $this->get('/payment/midtrans/finish?order_id='.$order->order_no.'&transaction_status=settlement')
            ->assertRedirect(route('buyer.dashboard'));

        $order->refresh();
        $this->assertSame('unpaid', $order->status);
        $this->assertSame('pending', $order->payment_status);
    }

    /**
     * Login must not perform an open redirect to an external host.
     */
    public function test_login_rejects_external_redirect(): void
    {
        $user = User::factory()->create([
            'role' => 'buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => 'buyer@example.com',
            'password' => 'password123',
            'redirect' => 'https://evil.example.com/phish',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Response carries baseline security headers.
     */
    public function test_security_headers_are_present(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}

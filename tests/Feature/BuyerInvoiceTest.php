<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_view_invoice_after_order_creation(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ260928INVOIC01',
            'status' => 'paid',
            'payment_status' => 'paid',
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'shipping_type' => 'courier',
            'total' => 100000,
            'paid_at' => now(),
            'address_snapshot' => [
                'recipient_name' => 'Buyer',
                'phone' => '08123456789',
                'address_line1' => 'Jl. Test',
                'city' => 'Jakarta',
                'province' => 'DKI',
                'postal_code' => '12345',
            ],
        ]);

        $response = $this->actingAs($user)
            ->get(route('buyer.orders.invoice', ['order' => $order->order_no]));

        $response->assertOk();
        $response->assertSee('INVOICE');
        $response->assertSee('Cetak Dokumen (Print)');
    }
}

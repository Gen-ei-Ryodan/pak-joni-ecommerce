<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_login_records_timestamp_ip_and_count(): void
    {
        $user = User::factory()->create([
            'email' => 'client@test.com',
            'password' => bcrypt('password123'),
            'role' => 'buyer',
            'last_login_at' => null,
            'login_count' => 0,
        ]);

        $this->post('/login', [
            'email' => 'client@test.com',
            'password' => 'password123',
        ], ['REMOTE_ADDR' => '192.168.1.50']);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertEquals(1, $user->login_count);
        $this->assertEquals('192.168.1.50', $user->last_login_ip);

        // Logout first so guest middleware allows second login
        $this->post('/logout');

        // Second login increments count
        $this->post('/login', [
            'email' => 'client@test.com',
            'password' => 'password123',
        ], ['REMOTE_ADDR' => '10.0.0.1']);

        $user->refresh();
        $this->assertEquals(2, $user->login_count);
        $this->assertEquals('10.0.0.1', $user->last_login_ip);
    }

    public function test_user_registration_records_login_tracking(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'budi@test.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->last_login_at);
        $this->assertGreaterThanOrEqual(1, $user->login_count);
    }

    public function test_admin_can_access_user_management_index_and_view(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $client = User::factory()->create([
            'name' => 'Joni Client',
            'email' => 'joni@client.com',
            'role' => 'buyer',
            'phone' => '081234567890',
            'last_login_at' => now(),
            'login_count' => 5,
        ]);

        $order = Order::create([
            'user_id' => $client->id,
            'order_no' => 'ORD-TEST-001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'subtotal' => 25000000,
            'total' => 25000000,
            'purchase_type' => 'vehicle',
            'address_snapshot' => [
                'recipient_name' => 'Joni Client',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 1',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
            ],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'sku' => 'MTR-001',
            'name' => 'Yamaha Aerox 155',
            'price' => 25000000,
            'quantity' => 1,
            'line_total' => 25000000,
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertSuccessful()
            ->assertSee('Joni Client')
            ->assertSee('joni@client.com');

        $this->actingAs($admin)
            ->get("/admin/users/{$client->id}")
            ->assertSuccessful()
            ->assertSee('Joni Client')
            ->assertSee('ORD-TEST-001')
            ->assertSee('Yamaha Aerox 155');
    }
}

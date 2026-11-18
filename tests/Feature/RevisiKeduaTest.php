<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemColor;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevisiKeduaTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_and_update_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'buyer',
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone' => null,
            'ktp_no' => null,
        ]);

        $this->actingAs($user)
            ->get(route('buyer.profile.edit'))
            ->assertStatus(200)
            ->assertSee('Profile Saya');

        $response = $this->actingAs($user)
            ->put(route('buyer.profile.update'), [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'phone' => '081234567890',
                'ktp_no' => '3201012345670001',
            ]);

        $response->assertRedirect(route('buyer.profile.edit'))
            ->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $user->refresh();
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('budi.santoso@example.com', $user->email);
        $this->assertSame('081234567890', $user->phone);
        $this->assertSame('3201012345670001', $user->ktp_no);
    }

    public function test_cash_vehicle_checkout_with_dp_and_pelunasan_workflow(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'buyer',
            'name' => 'Ahmad Dani',
            'email' => 'ahmad@example.com',
            'phone' => '081199998888',
            'ktp_no' => '3171012345670002',
        ]);

        $type = CategoryType::create([
            'name' => 'Motor Sport',
            'slug' => 'motor-sport-'.uniqid(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = Item::create([
            'category_type_id' => $type->id,
            'name' => 'Yamaha R25',
            'slug' => 'yamaha-r25-'.uniqid(),
            'price' => 70000000,
            'status' => 'active',
            'is_active' => true,
        ]);

        $color = ItemColor::create([
            'item_id' => $item->id,
            'name' => 'Racing Blue',
            'stock' => 5,
        ]);

        $dest = Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Ahmad Dani',
            'phone' => '081199998888',
            'address_line1' => 'Jl. Merdeka Barat No. 5',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
            'is_default' => true,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'itemable_type' => ItemColor::class,
            'itemable_id' => $color->id,
            'quantity' => 1,
            'price_snapshot' => 70000000,
            'product_name' => 'Yamaha R25',
            'variant_name' => 'Racing Blue',
        ]);

        // Session cart
        $sessionData = [
            'checkout.address_id' => $dest->id,
            'checkout.shipping' => [
                'courier' => 'STORE_PICKUP',
                'service' => 'STORE_PICKUP',
                'description' => 'Ambil di Showroom',
                'shipping_cost' => 0,
            ],
        ];

        $ktpFile = UploadedFile::fake()->image('ktp_ahmad.jpg');

        $response = $this->actingAs($user)
            ->withSession($sessionData)
            ->post('/checkout/place', [
                'payment_method' => 'qris',
                'notes' => 'Tolong disiapkan helm & jaket',
                'needs_tax_invoice' => '1',
                'stnk_name' => 'Ahmad Dani STNK',
                'stnk_address' => 'Jl. Merdeka Barat No. 5, Jakarta Pusat',
                'ktp_photo' => $ktpFile,
            ]);

        $response->assertRedirect();

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->isCashVehicle());
        $this->assertTrue((bool) $order->needs_tax_invoice);
        $this->assertSame('Ahmad Dani STNK', $order->stnk_name);
        $this->assertSame('Jl. Merdeka Barat No. 5, Jakarta Pusat', $order->stnk_address);
        $this->assertNotNull($order->ktp_image_path);

        // Skema DP Rp 500.000
        $this->assertEquals(500000, $order->dp_amount);
        $this->assertEquals(70000000 - 500000, $order->remaining_amount);
        $this->assertFalse($order->isDpPaid());
        $this->assertFalse($order->isFullyPaid());

        // SPK cannot be downloaded before admin verification & DP payment
        $this->actingAs($user)
            ->get(route('buyer.orders.spk', $order))
            ->assertStatus(403);

        // Simulate admin verification and DP paid
        $order->update([
            'is_admin_verified' => true,
            'payment_status' => 'dp_paid',
            'is_dp_paid' => true,
        ]);
        $order->refresh();

        $this->assertTrue($order->isDpPaid());
        $this->assertFalse($order->isFullyPaid());

        // Now buyer can download Kwitansi DP & SPK
        $this->actingAs($user)
            ->get(route('buyer.orders.receipt', ['order' => $order, 'type' => 'dp']))
            ->assertStatus(200);

        $this->actingAs($user)
            ->get(route('buyer.orders.spk', $order))
            ->assertStatus(200);

        // Faktur Motor is still locked before pelunasan (returns 403)
        $this->actingAs($user)
            ->get(route('buyer.orders.motor-invoice', $order))
            ->assertStatus(403);

        // Buyer pays remaining amount via payRemaining
        $payResponse = $this->actingAs($user)
            ->post(route('buyer.orders.payRemaining', $order));

        $payResponse->assertRedirect(route('buyer.orders.show', $order));

        $order->refresh();
        $this->assertTrue($order->isFullyPaid());
        $this->assertEquals(0, $order->remaining_amount);
        $this->assertSame('paid', $order->payment_status);

        // Now buyer can download Faktur Motor and Kwitansi Pelunasan
        $this->actingAs($user)
            ->get(route('buyer.orders.motor-invoice', $order))
            ->assertStatus(200);

        $this->actingAs($user)
            ->get(route('buyer.orders.receipt', ['order' => $order, 'type' => 'full']))
            ->assertStatus(200);
    }
}

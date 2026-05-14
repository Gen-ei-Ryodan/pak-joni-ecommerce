<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_add_to_cart_and_place_order(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        $cat = PartCategory::create([
            'group' => 'part',
            'name' => 'ECU',
            'slug' => 'ecu',
            'sort_order' => 0,
        ]);

        $part = Part::create([
            'sku' => 'P-ECU-001',
            'name' => 'ECU Racing',
            'slug' => 'ecu-racing',
            'part_category_id' => $cat->id,
            'base_price' => 1000000,
            'status' => 'active',
        ]);

        $variant = PartVariant::create([
            'part_id' => $part->id,
            'sku' => 'P-ECU-001-V1',
            'name' => 'V1',
            'price' => 1200000,
            'stock' => 10,
            'is_default' => true,
        ]);

        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Buyer',
            'phone' => '08123456789',
            'address_line1' => 'Jl. Test',
            'city' => 'Jakarta',
            'province' => 'DKI',
            'postal_code' => '12345',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->post('/cart/items', ['variant_id' => $variant->id, 'quantity' => 2])
            ->assertRedirect('/cart');

        $this->assertDatabaseHas('cart_items', [
            'part_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->post('/checkout/address', ['address_id' => $address->id])
            ->assertRedirect('/checkout/shipping');

        $this->actingAs($user)
            ->post('/checkout/shipping', ['courier' => 'JNE', 'service' => 'REG', 'shipping_cost' => 10000])
            ->assertRedirect('/checkout/payment');

        $this->actingAs($user)
            ->post('/checkout/place')
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'unpaid',
            'payment_status' => 'pending',
        ]);

        $variant->refresh();
        $this->assertSame(8, $variant->stock);

        $this->assertDatabaseMissing('cart_items', [
            'part_variant_id' => $variant->id,
        ]);
    }
}


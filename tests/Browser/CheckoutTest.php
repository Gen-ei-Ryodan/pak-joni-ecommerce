<?php

namespace Tests\Browser;

use App\Models\Address;
use App\Models\PartVariant;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CheckoutTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::where('email', 'like', 'dusk-%@test.com')->delete();
    }

    public function test_checkout_with_dealer_pickup()
    {
        $email = 'dusk-co-'.uniqid().'@test.com';
        $password = 'password123';

        $variant = PartVariant::with('part')
            ->where('stock', '>', 5)
            ->whereHas('part', fn ($q) => $q->where('status', 'active'))
            ->first();

        if (!$variant) {
            $this->markTestSkipped('No part variant with stock > 5 found. Run seeders first.');
        }

        $this->browse(function (Browser $browser) use ($email, $password, $variant) {
            $browser->visit('/register')
                ->waitForText('Daftar')
                ->script([
                    "document.getElementById('name').value = 'Dusk Co'",
                    "document.getElementById('email').value = '{$email}'",
                    "document.getElementById('password').value = '{$password}'",
                    "document.getElementById('password_confirmation').value = '{$password}'",
                    "document.querySelector('form .btn-primary').click()",
                ]);

            $browser->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');

            $user = User::where('email', $email)->first();

            Address::create([
                'user_id' => $user->id,
                'recipient_name' => 'Dusk User',
                'phone' => '08123456789',
                'address_line1' => 'Jl. Testing No. 1',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => true,
            ]);

            $browser->visit('/parts/'.$variant->part->slug)
                ->waitForText($variant->part->name)
                ->script([
                    "document.querySelector('input[name=\"quantity\"]').value = '1'",
                    "document.getElementById('add-to-cart-form').submit()",
                ]);

            $browser->waitForLocation('/cart')
                ->assertSee('Cart');

            $browser->press('Checkout')
                ->waitForLocation('/checkout')
                ->assertSee('Checkout');

            $browser->radio('address_id', '0')
                ->press('Continue')
                ->waitForLocation('/checkout/payment')
                ->assertSee('Payment');

            $browser->press('Place Order')
                ->waitForText('Pesanan Berhasil Dibuat')
                ->assertSee('Pesanan Berhasil Dibuat');
        });

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->orders()->delete();
            $user->cart?->items()->delete();
            $user->cart()->delete();
            $user->addresses()->delete();
            $user->delete();
        }
    }
}

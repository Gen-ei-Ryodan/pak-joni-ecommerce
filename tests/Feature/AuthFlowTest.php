<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_guest_can_register_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);
    }

    public function test_admin_user_dashboard_redirects_to_admin_area(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect('/admin');
    }

    public function test_buyer_cannot_access_admin_area(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $this->actingAs($buyer)
            ->get('/admin')
            ->assertForbidden();
    }
}

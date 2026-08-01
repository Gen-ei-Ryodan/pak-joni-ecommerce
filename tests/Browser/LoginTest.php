<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::where('email', 'like', 'dusk-%@test.com')->delete();
    }

    public function test_user_can_register_and_login()
    {
        $email = 'dusk-login-'.uniqid().'@test.com';
        $password = 'password123';

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->visit('/register')
                ->waitForText('Daftar')
                ->script([
                    "document.getElementById('name').value = 'Dusk User'",
                    "document.getElementById('email').value = '{$email}'",
                    "document.getElementById('password').value = '{$password}'",
                    "document.getElementById('password_confirmation').value = '{$password}'",
                    "document.querySelector('form .btn-primary').click()",
                ]);

            $browser->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');

            $browser->script("document.querySelector('form[action*=\"/logout\"]').submit();");
            $browser->waitForLocation('/');

            $browser->visit('/login')
                ->waitForText('Masuk')
                ->script([
                    "document.getElementById('email').value = '{$email}'",
                    "document.getElementById('password').value = '{$password}'",
                    "document.querySelector('form .btn-primary').click()",
                ]);

            $browser->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');
        });
    }

    public function test_login_with_wrong_password_stays_on_login_page()
    {
        $email = 'dusk-wrong-'.uniqid().'@test.com';
        $password = 'password123';

        User::create([
            'name' => 'Dusk Wrong',
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/login')
                ->waitForText('Masuk')
                ->script([
                    "document.getElementById('email').value = '{$email}'",
                    "document.getElementById('password').value = 'wrongpassword'",
                    "document.querySelector('form .btn-primary').click()",
                ]);

            $browser->waitForText('Email atau password')
                ->assertSee('salah');
        });

        User::where('email', $email)->delete();
    }
}

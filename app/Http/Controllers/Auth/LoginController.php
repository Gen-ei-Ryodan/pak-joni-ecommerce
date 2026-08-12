<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // Only allow redirect to internal (same-origin) paths to block open redirects.
        $redirect = $request->input('redirect');

        if ($redirect && $this->isSafeRedirect($redirect)) {
            return redirect($redirect);
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Ensure the target redirect path is internal (relative path on this host).
     */
    private function isSafeRedirect(string $target): bool
    {
        if (str_starts_with($target, '//')) {
            return false;
        }

        if (str_starts_with($target, '/')) {
            return true;
        }

        $host = parse_url($target, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        return $host === request()->getHost();
    }
}

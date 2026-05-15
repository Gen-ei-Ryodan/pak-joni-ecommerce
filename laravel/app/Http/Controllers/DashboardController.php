<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect('/admin');
        }

        $ordersCount = $user->orders()->count();
        $wishlistCount = $user->wishlists()->count();
        $addressCount = $user->addresses()->count();

        return view('buyer.dashboard', compact('ordersCount', 'wishlistCount', 'addressCount'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

        $recentOrders = $user->orders()
            ->with('items')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $indentOrders = $user->orders()
            ->where('is_indent', true)
            ->where('indent_status', '!=', 'paid_full')
            ->orderByDesc('id')
            ->get();

        return view('buyer.dashboard', compact(
            'ordersCount', 'wishlistCount', 'addressCount',
            'recentOrders', 'indentOrders'
        ));
    }
}

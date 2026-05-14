<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Part;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalOrders = Order::query()->count();
        $unpaidOrders = Order::query()->where('status', 'unpaid')->count();
        $paidOrders = Order::query()->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])->count();
        $revenue = (float) Order::query()->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])->sum('total');
        $totalProducts = Part::query()->count();
        $totalBuyers = User::query()->where('role', '!=', 'admin')->count();

        $recentOrders = Order::query()
            ->with(['user'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'unpaidOrders',
            'paidOrders',
            'revenue',
            'totalProducts',
            'totalBuyers',
            'recentOrders'
        ));
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Part;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Order::whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
            ->sum('total');

        $pendingOrders = Order::whereIn('status', ['unpaid', 'paid'])->count();

        return [
            Stat::make('Total Orders', Order::count())
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Total Revenue', 'Rp ' . number_format((float) $totalRevenue, 0, ',', '.'))
                ->icon('heroicon-o-banknotes'),

            Stat::make('Pending Orders', $pendingOrders)
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Active Products', Part::where('status', 'active')->count())
                ->icon('heroicon-o-cog-6-tooth'),

            Stat::make('Customers', User::where('role', 'buyer')->count())
                ->icon('heroicon-o-users'),
        ];
    }
}

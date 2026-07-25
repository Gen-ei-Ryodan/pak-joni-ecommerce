<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Services\MforceSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncMforce')
                ->label('Sync from MForce')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (MforceSyncService $service) {
                    $stats = $service->syncAll('admin');

                    Notification::make()
                        ->title('Sync Selesai')
                        ->body("Created: {$stats['created']} | Updated: {$stats['updated']} | Skipped: {$stats['skipped']} | Archived: {$stats['archived']}")
                        ->success()
                        ->send();
                }),
        ];
    }
}

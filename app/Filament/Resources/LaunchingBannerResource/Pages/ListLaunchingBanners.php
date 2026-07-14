<?php

namespace App\Filament\Resources\LaunchingBannerResource\Pages;

use App\Filament\Resources\LaunchingBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaunchingBanners extends ListRecords
{
    protected static string $resource = LaunchingBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

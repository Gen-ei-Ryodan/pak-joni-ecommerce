<?php

namespace App\Filament\Resources\ActivityBannerResource\Pages;

use App\Filament\Resources\ActivityBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivityBanners extends ListRecords
{
    protected static string $resource = ActivityBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

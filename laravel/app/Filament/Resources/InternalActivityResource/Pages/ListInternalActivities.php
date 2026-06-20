<?php

namespace App\Filament\Resources\InternalActivityResource\Pages;

use App\Filament\Resources\InternalActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternalActivities extends ListRecords
{
    protected static string $resource = InternalActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\MapsLocationResource\Pages;

use App\Filament\Resources\MapsLocationResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMapsLocation extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = MapsLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

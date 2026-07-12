<?php

namespace App\Filament\Resources\StoreAddressResource\Pages;

use App\Filament\Resources\StoreAddressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditStoreAddress extends EditRecord
{
    protected static string $resource = StoreAddressResource::class;


    use RedirectsToList;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

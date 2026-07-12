<?php

namespace App\Filament\Resources\StoreAddressResource\Pages;

use App\Filament\Resources\StoreAddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoreAddresses extends ListRecords
{
    protected static string $resource = StoreAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

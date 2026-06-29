<?php

namespace App\Filament\Resources\StoreAddressResource\Pages;

use App\Filament\Resources\StoreAddressResource;
use Filament\Resources\Pages\CreateRecord;


use App\Filament\Traits\RedirectsToList;class CreateStoreAddress extends CreateRecord
{
    protected static string $resource = StoreAddressResource::class;

    use RedirectsToList;
}

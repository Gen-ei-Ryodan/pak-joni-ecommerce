<?php

namespace App\Filament\Resources\PriceListResource\Pages;

use App\Filament\Resources\PriceListResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceList extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = PriceListResource::class;
}

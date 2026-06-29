<?php

namespace App\Filament\Resources\MapsLocationResource\Pages;

use App\Filament\Resources\MapsLocationResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Resources\Pages\CreateRecord;

class CreateMapsLocation extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = MapsLocationResource::class;
}

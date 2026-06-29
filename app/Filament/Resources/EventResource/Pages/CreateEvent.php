<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Resources\Pages\CreateRecord;


use App\Filament\Traits\RedirectsToList;class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    use RedirectsToList;
}

<?php

namespace App\Filament\Resources\DealerResource\Pages;

use App\Filament\Resources\DealerResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Resources\Pages\CreateRecord;

class CreateDealer extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = DealerResource::class;
}

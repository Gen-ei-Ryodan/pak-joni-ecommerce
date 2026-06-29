<?php

namespace App\Filament\Resources\PartCatalogResource\Pages;

use App\Filament\Resources\PartCatalogResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Resources\Pages\CreateRecord;

class CreatePartCatalog extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = PartCatalogResource::class;
}

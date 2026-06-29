<?php

namespace App\Filament\Resources\ProductHighlightResource\Pages;

use App\Filament\Resources\ProductHighlightResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Resources\Pages\CreateRecord;

class CreateProductHighlight extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = ProductHighlightResource::class;
}

<?php

namespace App\Filament\Resources\PartCatalogResource\Pages;

use App\Filament\Resources\PartCatalogResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartCatalog extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = PartCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

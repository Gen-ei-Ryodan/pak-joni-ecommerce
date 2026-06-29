<?php

namespace App\Filament\Resources\ProductHighlightResource\Pages;

use App\Filament\Resources\ProductHighlightResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductHighlight extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = ProductHighlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CategoryTypeResource\Pages;

use App\Filament\Resources\CategoryTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryTypes extends ListRecords
{
    protected static string $resource = CategoryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

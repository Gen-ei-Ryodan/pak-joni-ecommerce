<?php

namespace App\Filament\Resources\MotorCategoryResource\Pages;

use App\Filament\Resources\MotorCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMotorCategories extends ListRecords
{
    protected static string $resource = MotorCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

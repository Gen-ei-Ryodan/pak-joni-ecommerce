<?php

namespace App\Filament\Resources\PartCategoryResource\Pages;

use App\Filament\Resources\PartCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;


use App\Filament\Traits\RedirectsToList;class CreatePartCategory extends CreateRecord
{
    protected static string $resource = PartCategoryResource::class;


    use RedirectsToList;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $data;
    }
}

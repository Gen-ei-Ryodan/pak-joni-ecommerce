<?php

namespace App\Filament\Resources\PartCategoryResource\Pages;

use App\Filament\Resources\PartCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

use App\Filament\Traits\RedirectsToList;
class CreatePartCategory extends CreateRecord
{
    protected static string $resource = PartCategoryResource::class;

    use RedirectsToList;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (request()->query('category_type_id')) {
            $data['category_type_id'] = request()->query('category_type_id');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        $typeId = $this->record->category_type_id;
        if ($typeId) {
            return $this->getResource()::getUrl('byType', ['categoryType' => $typeId]);
        }
        return $this->getResource()::getUrl('index');
    }
}

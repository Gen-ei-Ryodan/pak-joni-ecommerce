<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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
        return $this->getResource()::getUrl('byType', ['categoryType' => 0]);
    }
}

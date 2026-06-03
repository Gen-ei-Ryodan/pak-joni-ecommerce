<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['logo_path']) && $data['logo_path']) {
            $data['logo_path'] = str_replace('storage/', '', $data['logo_path']);
        }

        return $data;
    }
}

<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\EditRecord;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['logo_path']) && $data['logo_path']) {
            $data['logo_path'] = str_replace('storage/', '', $data['logo_path']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['logo_path'])) {
            $data['logo_path'] = str_replace('storage/', '', $data['logo_path']);
        }

        return $data;
    }
}

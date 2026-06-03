<?php

namespace App\Filament\Resources\MotorResource\Pages;

use App\Filament\Resources\MotorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMotor extends CreateRecord
{
    protected static string $resource = MotorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (!empty($data['thumbnail_path'])) {
            $data['thumbnail_path'] = str_replace('storage/', '', $data['thumbnail_path']);
        }

        return $data;
    }
}

<?php

namespace App\Filament\Resources\MotorResource\Pages;

use App\Filament\Resources\MotorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditMotor extends EditRecord
{
    protected static string $resource = MotorResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (!empty($data['thumbnail_path'])) {
            $data['thumbnail_path'] = str_replace('storage/', '', $data['thumbnail_path']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!empty($data['thumbnail_path'])) {
            $data['thumbnail_path'] = str_replace('storage/', '', $data['thumbnail_path']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

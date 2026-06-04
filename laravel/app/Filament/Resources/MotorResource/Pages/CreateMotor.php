<?php

namespace App\Filament\Resources\MotorResource\Pages;

use App\Filament\Resources\MotorResource;
use App\Models\MotorImage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMotor extends CreateRecord
{
    protected static string $resource = MotorResource::class;

    private array $galleryPaths = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (!empty($data['thumbnail_path'])) {
            $data['thumbnail_path'] = str_replace('motors/thumbnails/', '', $data['thumbnail_path']);
        }

        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $motor = $this->record;

        if (! empty($this->galleryPaths)) {
            foreach ($this->galleryPaths as $idx => $path) {
                MotorImage::create([
                    'motor_id' => $motor->id,
                    'path' => $path,
                    'sort_order' => $idx,
                ]);
            }
        }
    }
}

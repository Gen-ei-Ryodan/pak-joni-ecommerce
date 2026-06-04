<?php

namespace App\Filament\Resources\MotorResource\Pages;

use App\Filament\Resources\MotorResource;
use App\Models\MotorImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditMotor extends EditRecord
{
    protected static string $resource = MotorResource::class;

    private array $galleryPaths = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!empty($data['thumbnail_path'])) {
            $data['thumbnail_path'] = str_replace('motors/thumbnails/', '', $data['thumbnail_path']);
        }

        if (!empty($data['colors'])) {
            foreach ($data['colors'] as &$color) {
                if (!empty($color['image_path'])) {
                    $color['image_path'] = str_replace('motors/colors/', '', $color['image_path']);
                }
            }
        }

        $data['gallery'] = $this->record->images()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($img) => str_replace('motors/gallery/', '', $img->path))
            ->values()
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (!empty($data['thumbnail_path'])) {
            $data['thumbnail_path'] = str_replace('motors/thumbnails/', '', $data['thumbnail_path']);
        }

        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);

        return $data;
    }

    protected function afterSave(): void
    {
        $motor = $this->record;

        if (! empty($this->galleryPaths)) {
            $start = (int) ($motor->images()->max('sort_order') ?? 0) + 1;
            foreach ($this->galleryPaths as $idx => $path) {
                MotorImage::create([
                    'motor_id' => $motor->id,
                    'path' => $path,
                    'sort_order' => $start + $idx,
                ]);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

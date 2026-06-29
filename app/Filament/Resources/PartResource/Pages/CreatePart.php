<?php

namespace App\Filament\Resources\PartResource\Pages;

use App\Filament\Resources\PartResource;
use App\Models\PartImage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;


use App\Filament\Traits\RedirectsToList;class CreatePart extends CreateRecord
{
    protected static string $resource = PartResource::class;


    use RedirectsToList;
    private array $motorIds = [];
    private array $galleryPaths = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (isset($data['motor_ids'])) {
            $this->motorIds = $data['motor_ids'];
            unset($data['motor_ids']);
        }

        // Filament FileUpload di public disk — path sudah relatif (misal parts/thumbnails/foo.jpg)
        $data['thumbnail_path'] = $data['thumbnail'] ?? null;
        unset($data['thumbnail']);

        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $part = $this->record;

        if (! empty($this->galleryPaths)) {
            foreach ($this->galleryPaths as $idx => $path) {
                PartImage::create([
                    'part_id' => $part->id,
                    'path' => $path,
                    'sort_order' => $idx,
                ]);
            }
        }

        if (! empty($this->motorIds)) {
            $part->motors()->sync($this->motorIds);
        }
    }
}

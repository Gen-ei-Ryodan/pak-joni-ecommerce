<?php

namespace App\Filament\Resources\PartResource\Pages;

use App\Filament\Resources\PartResource;
use App\Models\PartImage;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;

    private array $galleryPaths = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['motor_ids'] = $this->record->motors()->pluck('id')->toArray();

        if ($this->record->thumbnail_path) {
            $data['thumbnail'] = str_replace('storage/', '', $this->record->thumbnail_path);
        }

        $data['gallery'] = $this->record->images()
            ->orderBy('sort_order')
            ->get()
            ->pluck('path')
            ->map(fn ($p) => str_replace('storage/', '', $p))
            ->values()
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (isset($data['thumbnail'])) {
            $data['thumbnail_path'] = 'storage/' . $data['thumbnail'];
        }
        unset($data['thumbnail']);

        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);

        return $data;
    }

    protected function afterSave(): void
    {
        $part = $this->record;

        if (! empty($this->galleryPaths)) {
            $start = (int) ($part->images()->max('sort_order') ?? 0) + 1;
            foreach ($this->galleryPaths as $idx => $path) {
                PartImage::create([
                    'part_id' => $part->id,
                    'path' => 'storage/' . $path,
                    'sort_order' => $start + $idx,
                ]);
            }
        }

        if (isset($this->data['motor_ids'])) {
            $part->motors()->sync($this->data['motor_ids']);
        }
    }
}

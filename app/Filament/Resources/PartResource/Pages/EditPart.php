<?php

namespace App\Filament\Resources\PartResource\Pages;

use App\Filament\Resources\PartResource;
use App\Models\PartImage;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;


use App\Filament\Traits\RedirectsToList;class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;


    use RedirectsToList;
    private array $galleryPaths = [];
    private array $compatibleItemIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['motor_ids'] = $this->record->motors()->pluck('id')->toArray();

        $itemIds = $this->record->items()->pluck('items.id')->toArray();
        if (! empty($itemIds)) {
            $itemsByType = \App\Models\Item::whereIn('id', $itemIds)
                ->get()
                ->groupBy('category_type_id');

            foreach ($itemsByType as $typeId => $items) {
                $data['_compatible_items_' . $typeId] = $items->pluck('id')->toArray();
            }
        }

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
            $data['thumbnail_path'] = $data['thumbnail'];
        }
        unset($data['thumbnail']);

        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);

        $this->compatibleItemIds = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, '_compatible_items_') && is_array($value)) {
                $this->compatibleItemIds = array_merge($this->compatibleItemIds, $value);
                unset($data[$key]);
            }
        }

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
                    'path' => $path,
                    'sort_order' => $start + $idx,
                ]);
            }
        }

        if (isset($this->data['motor_ids'])) {
            $part->motors()->sync($this->data['motor_ids']);
        }

        if (isset($this->compatibleItemIds)) {
            $part->items()->sync($this->compatibleItemIds);
        }
    }
}

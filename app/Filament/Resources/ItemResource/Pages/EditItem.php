<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\ItemImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    private array $galleryPaths = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
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

        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);

        return $data;
    }

    protected function afterSave(): void
    {
        $item = $this->record;

        if (! empty($this->galleryPaths)) {
            $item->images()->delete();
            foreach ($this->galleryPaths as $idx => $path) {
                ItemImage::create([
                    'item_id' => $item->id,
                    'path' => $path,
                    'sort_order' => $idx,
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

    protected function getRedirectUrl(): string
    {
        $typeId = $this->record->category_type_id;
        if ($typeId) {
            return $this->getResource()::getUrl('byType', ['categoryType' => $typeId]);
        }
        return $this->getResource()::getUrl('byType', ['categoryType' => 0]);
    }
}

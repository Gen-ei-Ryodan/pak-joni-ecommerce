<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\ItemImage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    private array $galleryPaths = [];

    public ?string $categoryTypeId = null;

    public function mount(): void
    {
        parent::mount();

        $this->categoryTypeId = request()->query('category_type_id');

        if ($this->categoryTypeId) {
            $this->data['category_type_id'] = $this->categoryTypeId;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['category_type_id']) && $this->categoryTypeId) {
            $data['category_type_id'] = $this->categoryTypeId;
        }

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $item = $this->record;

        if (! empty($this->galleryPaths)) {
            foreach ($this->galleryPaths as $idx => $path) {
                ItemImage::create([
                    'item_id' => $item->id,
                    'path' => $path,
                    'sort_order' => $idx,
                ]);
            }
        }
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

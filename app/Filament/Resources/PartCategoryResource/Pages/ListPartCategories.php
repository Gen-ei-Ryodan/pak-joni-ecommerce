<?php

namespace App\Filament\Resources\PartCategoryResource\Pages;

use App\Filament\Resources\PartCategoryResource;
use App\Models\CategoryType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartCategories extends ListRecords
{
    protected static string $resource = PartCategoryResource::class;

    protected function getHeaderActions(): array
    {
        $typeId = $this->getCategoryTypeId();

        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) use ($typeId) {
                    if ($typeId) {
                        $data['category_type_id'] = $typeId;
                    }
                    return $data;
                })
                ->url(fn () => $typeId
                    ? PartCategoryResource::getUrl('create', ['category_type_id' => $typeId])
                    : PartCategoryResource::getUrl('create')),
        ];
    }

    protected function getCategoryTypeId(): ?int
    {
        $type = request()->route('categoryType');
        if ($type instanceof CategoryType) {
            return $type->id;
        }
        if (is_numeric($type)) {
            return (int) $type;
        }
        return null;
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();
        $typeId = $this->getCategoryTypeId();

        if ($typeId) {
            $query->where('category_type_id', $typeId);
        }

        return $query;
    }

    public function getTitle(): string
    {
        $typeId = $this->getCategoryTypeId();
        if ($typeId) {
            $type = CategoryType::find($typeId);
            if ($type) {
                return 'Kategori Parts ' . $type->name;
            }
        }
        return parent::getTitle();
    }
}

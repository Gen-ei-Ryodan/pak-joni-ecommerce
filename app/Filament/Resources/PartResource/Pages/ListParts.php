<?php

namespace App\Filament\Resources\PartResource\Pages;

use App\Filament\Resources\PartResource;
use App\Models\CategoryType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParts extends ListRecords
{
    protected static string $resource = PartResource::class;

    protected function getHeaderActions(): array
    {
        $typeId = $this->getCategoryTypeId();

        return [
            Actions\CreateAction::make()
                ->url(fn () => $typeId
                    ? PartResource::getUrl('create', ['category_type_id' => $typeId])
                    : PartResource::getUrl('create')),
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
                return 'Parts ' . $type->name;
            }
        }
        return parent::getTitle();
    }
}

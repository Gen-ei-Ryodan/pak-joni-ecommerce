<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\CategoryType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCategoriesByType extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getCategoryTypeId(): int
    {
        $param = request()?->route('categoryType');

        return match (true) {
            $param instanceof CategoryType => $param->id,
            is_numeric($param) => (int) $param,
            default => 0,
        };
    }

    public function getCategoryType(): ?CategoryType
    {
        $id = $this->getCategoryTypeId();
        return $id ? CategoryType::find($id) : null;
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('category_type_id', $this->getCategoryTypeId());
    }

    protected function getHeaderActions(): array
    {
        $typeId = $this->getCategoryTypeId();
        return [
            Actions\CreateAction::make()
                ->url(fn () => CategoryResource::getUrl('create', ['category_type_id' => $typeId])),
        ];
    }

    public function getTitle(): string
    {
        $type = $this->getCategoryType();
        return $type ? 'Kategori ' . $type->name : 'Kategori';
    }
}

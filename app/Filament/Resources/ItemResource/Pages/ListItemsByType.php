<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\CategoryType;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListItemsByType extends ListRecords
{
    protected static string $resource = ItemResource::class;

    public ?string $activeCategoryTypeId = null;

    public function mount(): void
    {
        parent::mount();

        $this->activeCategoryTypeId = (string) $this->resolveCategoryTypeId();
    }

    public function getCategoryTypeId(): int
    {
        if ($this->activeCategoryTypeId !== null) {
            return (int) $this->activeCategoryTypeId;
        }

        return $this->resolveCategoryTypeId();
    }

    private function resolveCategoryTypeId(): int
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
                ->url(fn () => ItemResource::getUrl('create', ['category_type_id' => $typeId])),
        ];
    }

    public function getTitle(): string
    {
        $type = $this->getCategoryType();
        return $type ? $type->name : 'Item';
    }
}

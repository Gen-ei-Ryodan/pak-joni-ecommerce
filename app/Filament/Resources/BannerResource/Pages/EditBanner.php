<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;


    use RedirectsToList;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['image_path']) && $this->record->image_path) {
            $data['image_path'] = $this->record->image_path;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

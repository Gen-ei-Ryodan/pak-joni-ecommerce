<?php

namespace App\Filament\Resources\WhyChooseUsResource\Pages;

use App\Filament\Resources\WhyChooseUsResource;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditWhyChooseUs extends EditRecord
{
    protected static string $resource = WhyChooseUsResource::class;


    use RedirectsToList;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['icon_image']) && $this->record->icon_image) {
            $data['icon_image'] = $this->record->icon_image;
        }

        return $data;
    }
}

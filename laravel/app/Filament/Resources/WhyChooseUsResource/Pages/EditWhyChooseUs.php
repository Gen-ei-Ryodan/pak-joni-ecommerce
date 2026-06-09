<?php

namespace App\Filament\Resources\WhyChooseUsResource\Pages;

use App\Filament\Resources\WhyChooseUsResource;
use Filament\Resources\Pages\EditRecord;

class EditWhyChooseUs extends EditRecord
{
    protected static string $resource = WhyChooseUsResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['icon_image']) && $this->record->icon_image) {
            $data['icon_image'] = $this->record->icon_image;
        }

        return $data;
    }
}

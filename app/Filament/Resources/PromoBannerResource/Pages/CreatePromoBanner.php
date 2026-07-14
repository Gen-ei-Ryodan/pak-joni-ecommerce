<?php

namespace App\Filament\Resources\PromoBannerResource\Pages;

use App\Filament\Resources\PromoBannerResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\RedirectsToList;

class CreatePromoBanner extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = PromoBannerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'promo';

        return $data;
    }
}

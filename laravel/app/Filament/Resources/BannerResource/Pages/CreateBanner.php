<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\RedirectsToList;

class CreateBanner extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = BannerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['title'] = 'Banner';
        $data['type'] = 'hero';
        $data['is_active'] = true;
        $data['sort_order'] = 0;

        return $data;
    }
}

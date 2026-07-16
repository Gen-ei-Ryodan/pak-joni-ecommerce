<?php

namespace App\Filament\Resources\ActivityBannerResource\Pages;

use App\Filament\Resources\ActivityBannerResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\RedirectsToList;

class CreateActivityBanner extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = ActivityBannerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'kegiatan';

        return $data;
    }
}

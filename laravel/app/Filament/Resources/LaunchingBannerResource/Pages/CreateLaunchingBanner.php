<?php

namespace App\Filament\Resources\LaunchingBannerResource\Pages;

use App\Filament\Resources\LaunchingBannerResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\RedirectsToList;

class CreateLaunchingBanner extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = LaunchingBannerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'launching';

        return $data;
    }
}

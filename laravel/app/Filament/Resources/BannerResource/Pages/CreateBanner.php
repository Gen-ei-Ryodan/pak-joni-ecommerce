<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;


use App\Filament\Traits\RedirectsToList;class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    use RedirectsToList;
}

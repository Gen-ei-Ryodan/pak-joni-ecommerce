<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;
}

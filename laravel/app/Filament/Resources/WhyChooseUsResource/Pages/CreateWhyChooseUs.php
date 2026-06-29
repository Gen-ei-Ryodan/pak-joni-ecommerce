<?php

namespace App\Filament\Resources\WhyChooseUsResource\Pages;

use App\Filament\Resources\WhyChooseUsResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Resources\Pages\CreateRecord;

class CreateWhyChooseUs extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = WhyChooseUsResource::class;
}

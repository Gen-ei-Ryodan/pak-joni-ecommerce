<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;


use App\Filament\Traits\RedirectsToList;class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    use RedirectsToList;
}

<?php

namespace App\Filament\Resources\InternalActivityResource\Pages;

use App\Filament\Resources\InternalActivityResource;
use Filament\Resources\Pages\CreateRecord;


use App\Filament\Traits\RedirectsToList;class CreateInternalActivity extends CreateRecord
{
    protected static string $resource = InternalActivityResource::class;

    use RedirectsToList;
}

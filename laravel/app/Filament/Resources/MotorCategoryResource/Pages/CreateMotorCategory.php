<?php

namespace App\Filament\Resources\MotorCategoryResource\Pages;

use App\Filament\Resources\MotorCategoryResource;
use Filament\Resources\Pages\CreateRecord;


use App\Filament\Traits\RedirectsToList;class CreateMotorCategory extends CreateRecord
{
    protected static string $resource = MotorCategoryResource::class;

    use RedirectsToList;
}

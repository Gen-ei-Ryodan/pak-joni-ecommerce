<?php

namespace App\Filament\Resources\MotorCategoryResource\Pages;

use App\Filament\Resources\MotorCategoryResource;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditMotorCategory extends EditRecord
{
    protected static string $resource = MotorCategoryResource::class;

    use RedirectsToList;
}

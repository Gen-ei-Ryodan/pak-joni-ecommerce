<?php

namespace App\Filament\Resources\ShowroomGalleryResource\Pages;

use App\Filament\Resources\ShowroomGalleryResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Resources\Pages\CreateRecord;

class CreateShowroomGallery extends CreateRecord
{
    use RedirectsToList;

    protected static string $resource = ShowroomGalleryResource::class;
}

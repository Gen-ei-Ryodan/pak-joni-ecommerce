<?php

namespace App\Filament\Resources\ShowroomGalleryResource\Pages;

use App\Filament\Resources\ShowroomGalleryResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShowroomGallery extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = ShowroomGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

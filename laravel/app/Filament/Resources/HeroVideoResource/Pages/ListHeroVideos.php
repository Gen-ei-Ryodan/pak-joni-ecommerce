<?php

namespace App\Filament\Resources\HeroVideoResource\Pages;

use App\Filament\Resources\HeroVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHeroVideos extends ListRecords
{
    protected static string $resource = HeroVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

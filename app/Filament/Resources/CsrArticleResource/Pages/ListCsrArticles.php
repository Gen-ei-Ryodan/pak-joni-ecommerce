<?php

namespace App\Filament\Resources\CsrArticleResource\Pages;

use App\Filament\Resources\CsrArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCsrArticles extends ListRecords
{
    protected static string $resource = CsrArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CsrArticleResource\Pages;

use App\Filament\Resources\CsrArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditCsrArticle extends EditRecord
{
    protected static string $resource = CsrArticleResource::class;


    use RedirectsToList;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Lihat di Website')
                ->icon('heroicon-o-eye')
                ->url(fn() => $this->record->slug ? route('buyer.csr.show', $this->record->slug) : '#')
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}

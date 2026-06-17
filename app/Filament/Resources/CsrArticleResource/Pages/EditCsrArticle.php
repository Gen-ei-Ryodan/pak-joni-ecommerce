<?php

namespace App\Filament\Resources\CsrArticleResource\Pages;

use App\Filament\Resources\CsrArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCsrArticle extends EditRecord
{
    protected static string $resource = CsrArticleResource::class;

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

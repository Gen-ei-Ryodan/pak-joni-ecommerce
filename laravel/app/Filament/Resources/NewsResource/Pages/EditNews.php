<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;


    use RedirectsToList;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Lihat di Website')
                ->icon('heroicon-o-eye')
                ->url(fn() => $this->record->slug ? route('buyer.news.show', $this->record->slug) : '#')
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}

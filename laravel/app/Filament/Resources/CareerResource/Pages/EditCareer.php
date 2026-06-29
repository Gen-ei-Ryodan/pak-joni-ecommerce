<?php

namespace App\Filament\Resources\CareerResource\Pages;

use App\Filament\Resources\CareerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditCareer extends EditRecord
{
    protected static string $resource = CareerResource::class;


    use RedirectsToList;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Lihat di Website')
                ->icon('heroicon-o-eye')
                ->url(fn() => route('buyer.careers.show', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}

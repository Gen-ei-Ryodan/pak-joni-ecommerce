<?php

namespace App\Filament\Resources\InternalActivityResource\Pages;

use App\Filament\Resources\InternalActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;


use App\Filament\Traits\RedirectsToList;class EditInternalActivity extends EditRecord
{
    protected static string $resource = InternalActivityResource::class;


    use RedirectsToList;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Lihat di Website')
                ->icon('heroicon-o-eye')
                ->url(fn() => $this->record->slug ? route('buyer.internal-activities.show', $this->record->slug) : '#')
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}

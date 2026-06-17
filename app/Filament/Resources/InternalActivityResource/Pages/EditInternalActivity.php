<?php

namespace App\Filament\Resources\InternalActivityResource\Pages;

use App\Filament\Resources\InternalActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalActivity extends EditRecord
{
    protected static string $resource = InternalActivityResource::class;

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

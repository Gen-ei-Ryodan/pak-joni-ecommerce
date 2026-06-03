<?php

namespace App\Filament\Resources\QuotationRequestResource\Pages;

use App\Filament\Resources\QuotationRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewQuotationRequest extends ViewRecord
{
    protected static string $resource = QuotationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_read')
                ->label('Tandai Dibaca')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function () {
                    $this->record->update(['is_read' => true]);
                    $this->refreshFormData(['is_read']);
                })
                ->hidden(fn () => $this->record->is_read),

            Actions\DeleteAction::make(),
        ];
    }
}

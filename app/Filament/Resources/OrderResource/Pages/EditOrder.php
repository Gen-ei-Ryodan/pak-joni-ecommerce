<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_motor_invoice')
                ->label('Cetak Faktur Motor')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->url(fn (): string => route('admin.orders.motor-invoice', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->motorInvoiceData() as $key => $value) {
            if (($data[$key] ?? null) === null || $data[$key] === '') {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}

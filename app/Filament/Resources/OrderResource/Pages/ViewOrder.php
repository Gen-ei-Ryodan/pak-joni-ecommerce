<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Faktur Motor')
                ->icon('heroicon-o-pencil-square'),
            Actions\Action::make('print_motor_invoice')
                ->label('Cetak Faktur Motor')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->url(fn (): string => route('admin.orders.motor-invoice', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\Action::make('print_spk')
                ->label('Cetak SPK')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->url(fn (): string => route('admin.orders.spk', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\Action::make('print_parts_invoice')
                ->label('Cetak Faktur Sukucadang')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->url(fn (): string => route('admin.orders.parts-invoice', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\Action::make('print_receipt')
                ->label('Cetak Kwitansi')
                ->icon('heroicon-o-receipt-percent')
                ->color('success')
                ->url(fn (): string => route('admin.orders.receipt', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->label('Delete Order')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete Order')
                ->modalDescription('Are you sure you want to delete this order? This action cannot be undone and will also delete all related items, payments, and shipments.')
                ->modalSubmitActionLabel('Yes, delete order'),
        ];
    }
}

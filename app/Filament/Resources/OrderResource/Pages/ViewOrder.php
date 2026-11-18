<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Faktur & SPK')
                ->icon('heroicon-o-pencil-square'),
            Actions\Action::make('verify_order')
                ->label('Verifikasi Dokumen')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (): bool => ! $this->record->is_admin_verified)
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Data Faktur & SPK')
                ->modalDescription('Apakah Anda yakin ingin memverifikasi dokumen pesanan ini dan merilis izin cetak ke customer?')
                ->action(function () {
                    $this->record->update([
                        'is_admin_verified' => true,
                        'admin_verified_at' => now(),
                        'admin_verified_by' => auth()->id(),
                    ]);
                    Notification::make()
                        ->title('Dokumen Berhasil Diverifikasi')
                        ->body("Order {$this->record->order_no} berhasil diverifikasi.")
                        ->success()
                        ->send();
                }),
            Actions\Action::make('print_motor_invoice')
                ->label('Cetak Faktur Motor')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->visible(fn (): bool => $this->record->hasVehicle())
                ->url(fn (): string => route('admin.orders.motor-invoice', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\Action::make('print_spk')
                ->label('Cetak SPK')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->visible(fn (): bool => $this->record->hasVehicle())
                ->url(fn (): string => route('admin.orders.spk', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\Action::make('print_parts_invoice')
                ->label('Cetak Faktur Sukucadang')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->visible(fn (): bool => $this->record->hasParts())
                ->url(fn (): string => route('admin.orders.parts-invoice', ['order' => $this->record]))
                ->openUrlInNewTab(),
            Actions\Action::make('print_receipt')
                ->label('Cetak Kwitansi')
                ->icon('heroicon-o-receipt-percent')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'paid' || $this->record->payment_status === 'paid' || (bool) $this->record->paid_at)
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

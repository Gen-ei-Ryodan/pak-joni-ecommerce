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

        $spk = $this->record->spkData();
        $spkKeys = [
            'spk_no', 'spk_date', 'spk_order_date', 'stnk_name', 'stnk_address',
            'motor_color', 'manufacturing_year', 'spk_payment_type', 'leasing_provider',
            'spk_dp_amount', 'credit_tenor', 'monthly_installment', 'booking_fee',
            'spk_checklist_docs', 'spk_notes',
        ];
        foreach ($spkKeys as $key) {
            if (($data[$key] ?? null) === null || $data[$key] === '') {
                if ($key === 'spk_checklist_docs') {
                    $data[$key] = $this->record->spk_checklist_docs ?? $spk['checklist_docs'] ?? [];
                } elseif (isset($spk[$key])) {
                    $data[$key] = $spk[$key];
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['is_admin_verified'])) {
            if (!$this->record->is_admin_verified || empty($this->record->admin_verified_at)) {
                $data['admin_verified_at'] = now();
                $data['admin_verified_by'] = auth()->id();
            }
        } else {
            $data['is_admin_verified'] = false;
        }

        return $data;
    }
}

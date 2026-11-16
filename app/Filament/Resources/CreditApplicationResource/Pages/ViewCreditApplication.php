<?php

namespace App\Filament\Resources\CreditApplicationResource\Pages;

use App\Exceptions\CreditException;
use App\Filament\Resources\CreditApplicationResource;
use App\Filament\Resources\OrderResource;
use App\Models\CreditApplication;
use App\Models\CreditPayment;
use App\Services\CreditService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

/**
 * Detail pengajuan kredit + aksi tahapan timeline.
 *
 * Aksi tersedia mengikuti urutan tahapan dan hanya muncul saat tahapan
 * terkait memang sedang berjalan (tidak dapat melompat tahapan).
 */
class ViewCreditApplication extends ViewRecord
{
    protected static string $resource = CreditApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open_order')
                ->label('Buka Pesanan')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray')
                ->url(fn (): string => OrderResource::getUrl('view', ['record' => $this->record->order]))
                ->openUrlInNewTab(),

            $this->issueSpkAction(),
            $this->recordSurveyAction(),
            $this->approveSurveyAction(),
            $this->rejectSurveyAction(),
            $this->recordPoAction(),
            $this->prepareUnitAction(),
            $this->recordDpPaymentAction(),
            $this->recordPelunasanPaymentAction(),
            $this->confirmPickupAction(),
        ];
    }

    protected function issueSpkAction(): Actions\Action
    {
        return Actions\Action::make('issue_spk')
            ->label('Terbitkan SPK')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->visible(fn (): bool => $this->stageIs('spk'))
            ->schema([
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->maxLength(1000)
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->issueSpk($this->record, $data['notes'] ?? null),
                    'SPK berhasil diterbitkan. Tahap berikutnya: Survey.'
                );
            });
    }

    protected function recordSurveyAction(): Actions\Action
    {
        return Actions\Action::make('record_survey')
            ->label('Selesaikan Survey')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('info')
            ->visible(fn (): bool => $this->stageIs('survey'))
            ->schema([
                Textarea::make('notes')
                    ->label('Catatan Hasil Survey')
                    ->rows(2)
                    ->maxLength(1000)
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->recordSurvey($this->record, $data['notes'] ?? null),
                    'Survey tercatat. Menunggu persetujuan survey.'
                );
            });
    }

    protected function approveSurveyAction(): Actions\Action
    {
        return Actions\Action::make('approve_survey')
            ->label('Setujui Survey')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn (): bool => $this->stageIs('survey_approved'))
            ->schema([
                Textarea::make('notes')
                    ->label('Catatan Persetujuan')
                    ->rows(2)
                    ->maxLength(1000)
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->approveSurvey($this->record, $data['notes'] ?? null),
                    'Survey disetujui. Tahap berikutnya: Persiapan Unit.'
                );
            });
    }

    protected function rejectSurveyAction(): Actions\Action
    {
        return Actions\Action::make('reject_survey')
            ->label('Tolak Pengajuan')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (): bool => $this->stageIs('survey_approved'))
            ->schema([
                Textarea::make('notes')
                    ->label('Alasan Penolakan')
                    ->rows(2)
                    ->required()
                    ->maxLength(1000),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->rejectSurvey($this->record, $data['notes'] ?? null),
                    'Pengajuan kredit ditolak.'
                );
            });
    }

    protected function recordPoAction(): Actions\Action
    {
        return Actions\Action::make('record_po')
            ->label('Catat PO Leasing')
            ->icon('heroicon-o-document-check')
            ->color('info')
            ->visible(fn (): bool => $this->record->status === CreditApplication::STATUS_IN_PROGRESS
                && ! $this->record->dpPayment()
                && $this->record->survey_approved_at !== null)
            ->schema([
                TextInput::make('leasing_name')
                    ->label('Nama Leasing')
                    ->required()
                    ->maxLength(255)
                    ->default(fn (): ?string => $this->record->leasing_name),
                TextInput::make('po_number')
                    ->label('No. PO Leasing')
                    ->required()
                    ->maxLength(255)
                    ->default(fn (): ?string => $this->record->po_number),
                DatePicker::make('po_date')
                    ->label('Tanggal PO')
                    ->required()
                    ->default(fn () => $this->record->po_date ?? now()),
                TextInput::make('dp_amount')
                    ->label('Nominal DP (Rp)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(fn (): float => (float) $this->record->order?->total)
                    ->helperText('Nominal DP yang harus dibayarkan customer langsung ke dealer.')
                    ->default(fn (): ?float => $this->record->dp_amount > 0 ? (float) $this->record->dp_amount : null),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->recordPo($this->record, $data),
                    'PO leasing berhasil dicatat.'
                );
            });
    }

    protected function prepareUnitAction(): Actions\Action
    {
        return Actions\Action::make('prepare_unit')
            ->label('Unit Disiapkan')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('warning')
            ->visible(fn (): bool => $this->stageIs('unit_preparation'))
            ->schema([
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->maxLength(1000)
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->prepareUnit($this->record, $data['notes'] ?? null),
                    'Persiapan unit selesai. Tahap berikutnya: Pembayaran.'
                );
            });
    }

    protected function recordDpPaymentAction(): Actions\Action
    {
        return Actions\Action::make('record_dp')
            ->label('Catat DP Customer')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->visible(fn (): bool => $this->record->status === CreditApplication::STATUS_IN_PROGRESS
                && ! $this->record->dpPayment()
                && $this->record->unit_preparation_at !== null
                && $this->record->dp_amount > 0)
            ->schema([
                TextInput::make('amount')
                    ->label('Nominal DP (Rp)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(fn (): float => (float) $this->record->order?->total)
                    ->default(fn (): ?float => $this->record->dp_amount > 0 ? (float) $this->record->dp_amount : null),
                DatePicker::make('paid_at')
                    ->label('Tanggal Bayar')
                    ->required()
                    ->default(now()),
                Select::make('method')
                    ->label('Metode')
                    ->options(CreditPayment::METHODS)
                    ->default('transfer')
                    ->required(),
                TextInput::make('reference')
                    ->label('Ref / No. Bukti Transfer')
                    ->maxLength(255)
                    ->nullable(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->maxLength(1000)
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->recordDpPayment($this->record, $data, auth()->user()),
                    'DP dari customer berhasil dicatat.'
                );
            });
    }

    protected function recordPelunasanPaymentAction(): Actions\Action
    {
        return Actions\Action::make('record_pelunasan')
            ->label('Catat Pelunasan Leasing')
            ->icon('heroicon-o-banknotes')
            ->color('purple')
            ->visible(fn (): bool => $this->record->status === CreditApplication::STATUS_IN_PROGRESS
                && $this->record->dpPayment() !== null
                && $this->record->pelunasanPayment() === null)
            ->schema([
                TextInput::make('amount')
                    ->label('Nominal Pelunasan (Rp)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(fn (): float => max(0, (float) $this->record->order?->total - $this->record->totalPaid()))
                    ->default(fn (): float => max(0, (float) $this->record->order?->total - $this->record->totalPaid()))
                    ->helperText('Dibayarkan oleh pihak leasing ke dealer.'),
                DatePicker::make('paid_at')
                    ->label('Tanggal Bayar')
                    ->required()
                    ->default(now()),
                Select::make('method')
                    ->label('Metode')
                    ->options(CreditPayment::METHODS)
                    ->default('transfer')
                    ->required(),
                TextInput::make('reference')
                    ->label('Ref / No. Bukti Transfer')
                    ->maxLength(255)
                    ->nullable(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->maxLength(1000)
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->recordPelunasanPayment($this->record, $data, auth()->user()),
                    'Pelunasan dari leasing berhasil dicatat.'
                );
            });
    }

    protected function confirmPickupAction(): Actions\Action
    {
        return Actions\Action::make('confirm_pickup')
            ->label('Konfirmasi Pengambilan Unit')
            ->icon('heroicon-o-truck')
            ->color('success')
            ->visible(fn (): bool => $this->stageIs('unit_pickup'))
            ->schema([
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->maxLength(1000)
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->runCreditAction(
                    fn () => $this->service()->confirmPickup($this->record, $data['notes'] ?? null),
                    'Unit diambil customer. Pengajuan kredit selesai.'
                );
            });
    }

    private function stageIs(string $stage): bool
    {
        return $this->record->status === CreditApplication::STATUS_IN_PROGRESS
            && $this->record->currentStage() === $stage;
    }

    private function service(): CreditService
    {
        return app(CreditService::class);
    }

    /**
     * Jalankan aksi bisnis kredit: sukses → notifikasi hijau, aturan bisnis
     * dilanggar (CreditException) → notifikasi merah dengan pesan yang jelas.
     */
    private function runCreditAction(callable $callback, string $successTitle): void
    {
        try {
            $callback();
            $this->record->refresh();

            Notification::make()
                ->title($successTitle)
                ->success()
                ->send();
        } catch (CreditException $e) {
            Notification::make()
                ->title('Tidak dapat diproses')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

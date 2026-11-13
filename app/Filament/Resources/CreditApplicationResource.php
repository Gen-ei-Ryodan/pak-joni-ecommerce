<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditApplicationResource\Pages;
use App\Models\CreditApplication;
use App\Models\CreditPayment;
use BackedEnum;
use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Admin pengajuan pembelian kredit (leasing).
 *
 * Menampilkan timeline tahapan (SPK → Survey → Persetujuan Survey →
 * Persiapan Unit → Pembayaran → Pengambilan Unit) dan pembayaran
 * terpisah antara customer (DP) dan leasing (pelunasan).
 */
class CreditApplicationResource extends Resource
{
    protected static ?string $model = CreditApplication::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Kredit Motor';

    protected static ?string $modelLabel = 'Pengajuan Kredit';

    protected static ?string $pluralModelLabel = 'Pengajuan Kredit';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['order.user', 'payments']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('application_no')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_no')
                    ->label('Order No')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('order.user.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('leasing_name')
                    ->label('Leasing')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('stage')
                    ->label('Tahap')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'spk' => 'gray',
                        'survey' => 'info',
                        'survey_approved' => 'purple',
                        'unit_preparation' => 'warning',
                        'payment' => 'orange',
                        'unit_pickup' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => CreditApplication::stageLabelStatic($state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CreditApplication::STATUS_IN_PROGRESS => 'warning',
                        CreditApplication::STATUS_COMPLETED => 'success',
                        CreditApplication::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        CreditApplication::STATUS_IN_PROGRESS => 'Diproses',
                        CreditApplication::STATUS_COMPLETED => 'Selesai',
                        CreditApplication::STATUS_REJECTED => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('dp_amount')
                    ->label('DP (PO Leasing)')
                    ->money('IDR')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('paid_summary')
                    ->label('Tercatat')
                    ->state(fn (CreditApplication $record): string => 'Rp '.number_format($record->totalPaid(), 0, ',', '.'))
                    ->description(fn (CreditApplication $record): string => 'dari Rp '.number_format((float) $record->order?->total, 0, ',', '.')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->label('Tahap')
                    ->options(CreditApplication::STAGES),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        CreditApplication::STATUS_IN_PROGRESS => 'Diproses',
                        CreditApplication::STATUS_COMPLETED => 'Selesai',
                        CreditApplication::STATUS_REJECTED => 'Ditolak',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('application_no')
                                    ->label('No. Pengajuan')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        CreditApplication::STATUS_IN_PROGRESS => 'warning',
                                        CreditApplication::STATUS_COMPLETED => 'success',
                                        CreditApplication::STATUS_REJECTED => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        CreditApplication::STATUS_IN_PROGRESS => 'Diproses',
                                        CreditApplication::STATUS_COMPLETED => 'Selesai',
                                        CreditApplication::STATUS_REJECTED => 'Ditolak',
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('stage')
                                    ->label('Tahap Saat Ini')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'spk' => 'gray',
                                        'survey' => 'info',
                                        'survey_approved' => 'purple',
                                        'unit_preparation' => 'warning',
                                        'payment' => 'orange',
                                        'unit_pickup' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => CreditApplication::stageLabelStatic($state)),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('order.order_no')
                                    ->label('Order No'),
                                Infolists\Components\TextEntry::make('order.user.name')
                                    ->label('Customer'),
                                Infolists\Components\TextEntry::make('order.total')
                                    ->label('Total Pesanan')
                                    ->money('IDR'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('leasing_name')
                                    ->label('Leasing')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('po_number')
                                    ->label('No. PO Leasing')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('po_date')
                                    ->label('Tanggal PO')
                                    ->date('d M Y')
                                    ->placeholder('-'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('dp_amount')
                                    ->label('Nominal DP (dari Leasing)')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('total_paid')
                                    ->label('Total Tercatat')
                                    ->state(fn (CreditApplication $record): string => 'Rp '.number_format($record->totalPaid(), 0, ',', '.')),
                                Infolists\Components\TextEntry::make('sisa_amount')
                                    ->label('Sisa Belum Dibayar')
                                    ->state(fn (CreditApplication $record): string => 'Rp '.number_format($record->sisaAmount(), 0, ',', '.')),
                            ]),
                    ]),

                Section::make('Timeline Proses Pembelian Kredit')
                    ->description('Urutan tahapan: SPK → Survey → Persetujuan Survey → Persiapan Unit → Pembayaran → Pengambilan Unit.')
                    ->schema(
                        collect(array_keys(CreditApplication::STAGES))
                            ->map(fn (string $key, int $index) => Infolists\Components\TextEntry::make(CreditApplication::stageTimestampColumn($key))
                                ->label(($index + 1).'. '.CreditApplication::stageLabelStatic($key).' — '.CreditApplication::stageDescription($key))
                                ->formatStateUsing(fn ($state) => $state ? $state->format('d M Y H:i') : 'Belum selesai')
                                ->color(fn ($state) => $state ? 'success' : 'gray')
                                ->weight('medium'))
                            ->all()
                    ),

                Section::make('Pembayaran — DP (Customer) & Pelunasan (Leasing)')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('payments')
                            ->label('')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('type')
                                            ->label('Jenis')
                                            ->badge()
                                            ->color(fn (string $state): string => $state === CreditPayment::TYPE_DP ? 'info' : 'success')
                                            ->formatStateUsing(fn (string $state): string => $state === CreditPayment::TYPE_DP ? 'DP' : 'Pelunasan'),
                                        Infolists\Components\TextEntry::make('payer_type')
                                            ->label('Dibayar Oleh')
                                            ->badge()
                                            ->color(fn (string $state): string => $state === CreditPayment::PAYER_CUSTOMER ? 'warning' : 'purple')
                                            ->formatStateUsing(fn (string $state): string => $state === CreditPayment::PAYER_CUSTOMER ? 'Customer' : 'Leasing'),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->label('Nominal')
                                            ->money('IDR'),
                                        Infolists\Components\TextEntry::make('paid_at')
                                            ->label('Tanggal Bayar')
                                            ->dateTime('d M Y H:i')
                                            ->placeholder('-'),
                                        Infolists\Components\TextEntry::make('method')
                                            ->label('Metode')
                                            ->formatStateUsing(fn (?string $state) => CreditPayment::METHODS[$state] ?? ($state ?: '-'))
                                            ->placeholder('-'),
                                        Infolists\Components\TextEntry::make('reference')
                                            ->label('Ref / No. Bukti')
                                            ->placeholder('-'),
                                    ]),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->columnSpanFull()
                                    ->placeholder('-'),
                            ])
                            ->columns(1)
                            ->placeholder('Belum ada pembayaran tercatat.'),
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('dp_summary')
                                    ->label('DP Tercatat (Customer)')
                                    ->state(fn (CreditApplication $record): string => $record->dpPayment()
                                        ? 'Rp '.number_format((float) $record->dpPayment()->amount, 0, ',', '.')
                                        : 'Belum ada'),
                                Infolists\Components\TextEntry::make('pelunasan_summary')
                                    ->label('Pelunasan Tercatat (Leasing)')
                                    ->state(fn (CreditApplication $record): string => $record->pelunasanPayment()
                                        ? 'Rp '.number_format((float) $record->pelunasanPayment()->amount, 0, ',', '.')
                                        : 'Belum ada'),
                                Infolists\Components\TextEntry::make('total_summary')
                                    ->label('Total Tercatat')
                                    ->state(fn (CreditApplication $record): string => 'Rp '.number_format($record->totalPaid(), 0, ',', '.')),
                            ]),
                    ]),

                Section::make('Catatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->getStateUsing(fn (CreditApplication $record) => $record->notes ?: 'Tidak ada catatan.')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Diajukan Pada')
                            ->dateTime('d M Y H:i'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditApplications::route('/'),
            'view' => Pages\ViewCreditApplication::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', CreditApplication::STATUS_IN_PROGRESS)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}

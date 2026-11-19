<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Orders';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_no')
                    ->label('Order No')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'info',
                        'processing' => 'purple',
                        'shipped' => 'orange',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('shipping_type')
                    ->label('Pengiriman')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'dealer_pickup' => 'success',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'dealer_pickup' => 'Ambil di Dealer',
                        default => 'Kurir',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dp_amount')
                    ->label('DP (50%)')
                    ->money('IDR')
                    ->visible(fn ($record) => $record?->is_indent ?? false),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->money('IDR')
                    ->visible(fn ($record) => ($record?->is_indent ?? false) && $record->remaining_amount > 0),

                Tables\Columns\TextColumn::make('indent_status')
                    ->label('Indent')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'waiting_stock' => 'warning',
                        'ready_for_delivery' => 'info',
                        'waiting_payment' => 'orange',
                        'paid_full' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? \App\Models\Order::indentStatusLabelStatic($state) : '-')
                    ->visible(fn ($record) => $record?->is_indent ?? false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->searchable(),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                    ])
                    ->searchable(),
            ])
            ->headerActions([
                Actions\Action::make('export_orders')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $export = new \App\Filament\Exports\OrderExport();
                        $fileName = $export->export();

                        return redirect()->to(\Illuminate\Support\Facades\Storage::disk('public')->url('exports/' . $fileName));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'unpaid')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(\App\Services\OrderService::class)->markAsPaid($record);
                    }),
                Actions\Action::make('ready_for_delivery')
                    ->label('Ready For Delivery')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (?Order $record) => $record?->is_indent && $record?->indent_status === 'waiting_stock')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->update([
                            'indent_status' => 'waiting_payment',
                            'status' => 'unpaid',
                        ]);
                    }),
                Actions\Action::make('process')
                    ->label('Process')
                    ->icon('heroicon-o-arrow-path')
                    ->color('purple')
                    ->visible(fn (Order $record) => $record->status === 'paid')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(\App\Services\OrderService::class)->processOrder($record);
                    }),
                Actions\Action::make('ship')
                    ->label('Ship')
                    ->icon('heroicon-o-truck')
                    ->color('orange')
                    ->visible(fn (Order $record) => $record->status === 'processing' && !$record->isDealerPickup())
                    ->form([
                        Forms\Components\TextInput::make('courier')
                            ->label('Courier')
                            ->required()
                            ->default(fn (Order $record) => strtoupper($record->shipping_snapshot['courier'] ?? '')),
                        Forms\Components\TextInput::make('receipt')
                            ->label('Receipt Number')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data) {
                        app(\App\Services\OrderService::class)->markAsShipped($record, $data['courier'], $data['receipt']);
                    }),

                Actions\Action::make('ready_for_pickup')
                    ->label('Siap Diambil')
                    ->icon('heroicon-o-building-storefront')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'processing' && $record->isDealerPickup())
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(\App\Services\OrderService::class)->markAsShipped($record, 'dealer_pickup', 'Siap Diambil');
                    }),
                Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-flag')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'shipped')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(\App\Services\OrderService::class)->markAsCompleted($record);
                    }),
                Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record) => in_array($record->status, ['unpaid', 'paid', 'processing']))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(\App\Services\OrderService::class)->cancelOrder($record);
                    }),
                Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Order')
                    ->modalDescription('Are you sure you want to delete this order? This action cannot be undone and will also delete all related items, payments, and shipments.')
                    ->modalSubmitActionLabel('Yes, delete order'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('order_no')
                                    ->label('Order No')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'unpaid' => 'warning',
                                        'paid' => 'info',
                                        'processing' => 'purple',
                                        'shipped' => 'orange',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'unpaid' => 'Unpaid',
                                        'paid' => 'Paid',
                                        'processing' => 'Processing',
                                        'shipped' => 'Shipped',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Order Date')
                                    ->dateTime('d M Y, H:i'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Customer Name'),
                                Infolists\Components\TextEntry::make('user.email')
                                    ->label('Customer Email'),
                                Infolists\Components\TextEntry::make('payment_method')
                                    ->label('Payment Method')
                                    ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : '-'),
                            ]),
                    ]),

                Section::make('Payment Summary')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('shipping_cost')
                                    ->label('Ongkos Kirim')
                                    ->formatStateUsing(fn (Order $record) => $record->isDealerPickup() ? 'Gratis' : 'Rp '.number_format((float) $record->shipping_cost, 0, ',', '.')),
                                Infolists\Components\TextEntry::make('total')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('payment_status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'expired' => 'gray',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                        'expired' => 'Expired',
                                        default => $state,
                                    }),
                            ]),
                    ]),

                Section::make('Indent Info')
                    ->schema([
                        Infolists\Components\TextEntry::make('indent_status')
                            ->label('Indent Status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'waiting_stock' => 'warning',
                                'ready_for_delivery' => 'info',
                                'waiting_payment' => 'orange',
                                'paid_full' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => $state ? \App\Models\Order::indentStatusLabelStatic($state) : '-'),
                        Infolists\Components\TextEntry::make('dp_amount')
                            ->label('DP (50%)')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('remaining_amount')
                            ->label('Remaining')
                            ->money('IDR'),
                    ])
                    ->visible(fn ($record) => $record?->is_indent ?? false),

                Section::make('Alamat Pengiriman')
                    ->schema([
                        Infolists\Components\TextEntry::make('address_snapshot.recipient_name')
                            ->label('Penerima'),
                        Infolists\Components\TextEntry::make('address_snapshot.phone')
                            ->label('Telepon'),
                        Infolists\Components\TextEntry::make('full_address')
                            ->label('Alamat')
                            ->getStateUsing(fn (Order $record) => collect([
                                $record->address_snapshot['address_line1'] ?? '',
                                $record->address_snapshot['address_line2'] ?? '',
                                $record->address_snapshot['city'] ?? '',
                                $record->address_snapshot['province'] ?? '',
                                $record->address_snapshot['postal_code'] ?? '',
                            ])->filter()->implode(', '))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Order $record) => !$record->isDealerPickup()),
                Section::make('Info Kurir')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('courier_name')
                                    ->label('Kurir')
                                    ->getStateUsing(fn (Order $record) => collect([
                                        strtoupper($record->shipping_snapshot['courier'] ?? ''),
                                        strtoupper($record->shipping_snapshot['service'] ?? ''),
                                    ])->filter()->implode(' - ') ?: '-')
                                    ->visible(fn (Order $record) => !empty($record->shipping_snapshot)),
                                Infolists\Components\TextEntry::make('shipping_receipt')
                                    ->label('No Resi')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('shipped_at')
                                    ->label('Waktu Kirim')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->visible(fn (Order $record) => !$record->isDealerPickup()),

                Section::make('Informasi Pengambilan')
                    ->schema([
                        Infolists\Components\TextEntry::make('shipping_type')
                            ->label('Metode')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(fn () => 'Ambil di Dealer'),
                        Infolists\Components\TextEntry::make('shipping_cost')
                            ->label('Biaya Kirim')
                            ->formatStateUsing(fn () => 'Gratis'),
                    ])
                    ->visible(fn (Order $record) => $record->isDealerPickup()),

                Section::make('Order Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Product'),
                                        Infolists\Components\TextEntry::make('variant_name')
                                            ->label('Variant')
                                            ->placeholder('-'),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Qty'),
                                        Infolists\Components\TextEntry::make('line_total')
                                            ->label('Total')
                                            ->money('IDR'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}

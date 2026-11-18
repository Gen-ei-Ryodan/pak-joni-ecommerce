<?php

namespace App\Filament\Resources;

use App\Filament\Exports\OrderExport;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['creditApplication', 'adminVerifier']);
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

                Tables\Columns\TextColumn::make('purchase_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Order::PURCHASE_TYPE_CREDIT => 'purple',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state === Order::PURCHASE_TYPE_CREDIT ? 'Kredit' : 'Tunai')
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

                Tables\Columns\IconColumn::make('is_admin_verified')
                    ->label('Verif')
                    ->boolean()
                    ->sortable(),

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
                    ->formatStateUsing(fn (?string $state): string => $state ? Order::indentStatusLabelStatic($state) : '-')
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

                Tables\Filters\SelectFilter::make('purchase_type')
                    ->label('Tipe Pembelian')
                    ->options([
                        Order::PURCHASE_TYPE_CASH => 'Tunai',
                        Order::PURCHASE_TYPE_CREDIT => 'Kredit',
                    ]),
            ])
            ->headerActions([
                Actions\Action::make('export_orders')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $export = new OrderExport;
                        $fileName = $export->export();

                        return redirect()->to(Storage::disk('public')->url('exports/'.$fileName));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()->label('Edit Faktur & SPK'),
                Actions\Action::make('verify_order')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Order $record) => ! $record->is_admin_verified)
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Data Faktur & SPK')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi dokumen pesanan ini dan merilis izin cetak ke customer?')
                    ->action(function (Order $record) {
                        $record->update([
                            'is_admin_verified' => true,
                            'admin_verified_at' => now(),
                            'admin_verified_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Dokumen Berhasil Diverifikasi')
                            ->body("Order {$record->order_no} berhasil diverifikasi.")
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('print_motor_invoice')
                    ->label('Cetak Faktur Motor')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->hasVehicle())
                    ->url(fn (Order $record): string => route('admin.orders.motor-invoice', ['order' => $record]))
                    ->openUrlInNewTab(),
                Actions\Action::make('print_spk')
                    ->label('Cetak SPK')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(fn (Order $record) => $record->hasVehicle())
                    ->url(fn (Order $record): string => route('admin.orders.spk', ['order' => $record]))
                    ->openUrlInNewTab(),
                Actions\Action::make('print_parts_invoice')
                    ->label('Cetak Faktur Sukucadang')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->visible(fn (Order $record) => $record->hasParts())
                    ->url(fn (Order $record): string => route('admin.orders.parts-invoice', ['order' => $record]))
                    ->openUrlInNewTab(),
                Actions\Action::make('print_receipt')
                    ->label('Cetak Kwitansi')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'paid' || $record->payment_status === 'paid' || (bool) $record->paid_at)
                    ->url(fn (Order $record): string => route('admin.orders.receipt', ['order' => $record]))
                    ->openUrlInNewTab(),
                Actions\Action::make('credit_detail')
                    ->label('Detail Kredit')
                    ->icon('heroicon-o-credit-card')
                    ->color('purple')
                    ->visible(fn (Order $record) => $record->isCredit() && $record->creditApplication !== null)
                    ->url(fn (Order $record) => \App\Filament\Resources\CreditApplicationResource::getUrl('view', ['record' => $record->creditApplication]))
                    ->openUrlInNewTab(),
                Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'unpaid' && ! $record->isCredit())
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(OrderService::class)->markAsPaid($record);
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
                        app(OrderService::class)->processOrder($record);
                    }),
                Actions\Action::make('ship')
                    ->label('Ship')
                    ->icon('heroicon-o-truck')
                    ->color('orange')
                    ->visible(fn (Order $record) => $record->status === 'processing' && ! $record->isDealerPickup())
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
                        app(OrderService::class)->markAsShipped($record, $data['courier'], $data['receipt']);
                    }),

                Actions\Action::make('ready_for_pickup')
                    ->label('Siap Diambil')
                    ->icon('heroicon-o-building-storefront')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'processing' && $record->isDealerPickup())
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(OrderService::class)->markAsShipped($record, 'dealer_pickup', 'Siap Diambil');
                    }),
                Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-flag')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'shipped')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(OrderService::class)->markAsCompleted($record);
                    }),
                Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record) => in_array($record->status, ['unpaid', 'paid', 'processing']))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(OrderService::class)->cancelOrder($record);
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

    public static function form(Schema $schema): Schema
    {
        $autoFill = fn (?Order $record, string $key, $default = null) => $record?->motorInvoiceData()[$key] ?? $default;
        $autoSpk = fn (?Order $record, string $key, $default = null) => $record?->spkData()[$key] ?? $default;

        return $schema->schema([
            Section::make('Verifikasi Dokumen Faktur & SPK')
                ->description('Gerbang verifikasi admin untuk merilis izin cetak dokumen kepada customer.')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Toggle::make('is_admin_verified')
                        ->label('Verifikasi Data Faktur & SPK (Rilis Cetak ke Customer)')
                        ->helperText('Aktifkan setelah nomor rangka dan nomor mesin telah diisi dengan benar. Customer hanya bisa mencetak Faktur dan SPK setelah diverifikasi.')
                        ->default(false),
                    Forms\Components\Toggle::make('needs_tax_invoice')
                        ->label('Customer Membutuhkan Faktur Pajak')
                        ->disabled(),
                ]),

            Section::make('Faktur Pembelian Sepeda Motor')
                ->description('Data faktur pembelian sepeda motor. Kolom yang masih kosong otomatis terisi dari data order.')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('invoice_no')
                        ->label('Nomor Faktur')
                        ->default(fn (?Order $record) => $autoFill($record, 'invoice_no', Order::generateMotorInvoiceNumber()))
                        ->placeholder(fn (?Order $record) => $autoFill($record, 'invoice_no', Order::generateMotorInvoiceNumber()))
                        ->unique(ignoreRecord: true),

                    Forms\Components\DatePicker::make('invoice_date')
                        ->label('Tanggal Faktur')
                        ->default(fn (?Order $record) => $autoFill($record, 'invoice_date', now()->toDateString())),

                    Forms\Components\TextInput::make('chassis_no')
                        ->label('Nomor Rangka (Chassis No)')
                        ->placeholder('Contoh: MH1JM8118NK123456'),

                    Forms\Components\TextInput::make('engine_no')
                        ->label('Nomor Mesin (Engine No)')
                        ->placeholder('Contoh: JM81E111111'),

                    Forms\Components\TextInput::make('buyer_nik')
                        ->label('NIK Pembeli')
                        ->maxLength(32),

                    Forms\Components\TextInput::make('buyer_name')
                        ->label('Nama Pembeli')
                        ->default(fn (?Order $record) => $autoFill($record, 'buyer_name')),

                    Forms\Components\Textarea::make('buyer_address')
                        ->label('Alamat Pembeli')
                        ->rows(3)
                        ->default(fn (?Order $record) => $autoFill($record, 'buyer_address')),

                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(4)
                        ->default(fn (?Order $record) => $autoFill($record, 'notes', Order::DEFAULT_MOTOR_INVOICE_NOTES)),

                    Forms\Components\TextInput::make('signatory_name')
                        ->label('Penandatangan')
                        ->default(fn (?Order $record) => $autoFill($record, 'signatory_name', 'JONI')),
                ])->columns(2),

            Section::make('Surat Pesanan Kendaraan (SPK)')
                ->description('Data Surat Pesanan Kendaraan (SPK), data pemesan/STNK, skema pembayaran, dan kelengkapan berkas fisik.')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\TextInput::make('spk_no')
                        ->label('Nomor SPK')
                        ->default(fn (?Order $record) => $autoSpk($record, 'spk_no', $record?->order_no)),

                    Forms\Components\DatePicker::make('spk_date')
                        ->label('Tanggal SPK')
                        ->default(fn (?Order $record) => $autoSpk($record, 'spk_date', now()->toDateString())),

                    Forms\Components\DatePicker::make('spk_order_date')
                        ->label('Tanggal Pemesanan')
                        ->default(fn (?Order $record) => $autoSpk($record, 'spk_order_date', now()->toDateString())),

                    Forms\Components\FileUpload::make('ktp_image_path')
                        ->label('Foto KTP Customer')
                        ->image()
                        ->disk('public')
                        ->directory('ktp')
                        ->columnSpanFull()
                        ->helperText('Foto KTP asli yang diunggah oleh customer pada saat checkout SPK.'),

                    Forms\Components\TextInput::make('stnk_name')
                        ->label('Nama di STNK / BPKB')
                        ->helperText('Nama pemilik kendaraan yang tercantum pada STNK/BPKB.')
                        ->default(fn (?Order $record) => $autoSpk($record, 'stnk_name')),

                    Forms\Components\Textarea::make('stnk_address')
                        ->label('Alamat di STNK / BPKB')
                        ->rows(2)
                        ->default(fn (?Order $record) => $autoSpk($record, 'stnk_address')),

                    Forms\Components\TextInput::make('motor_color')
                        ->label('Warna Kendaraan')
                        ->default(fn (?Order $record) => $autoSpk($record, 'motor_color', '-')),

                    Forms\Components\TextInput::make('manufacturing_year')
                        ->label('Tahun Rakitan')
                        ->default(fn (?Order $record) => $autoSpk($record, 'manufacturing_year', date('Y'))),

                    Forms\Components\Select::make('spk_payment_type')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai (Cash)',
                            'credit' => 'Kredit (Leasing)',
                        ])
                        ->default(fn (?Order $record) => $autoSpk($record, 'payment_type', 'cash')),

                    Forms\Components\TextInput::make('leasing_provider')
                        ->label('Nama Leasing / Pembiayaan')
                        ->placeholder('Contoh: Pegadaian, Adira, FIF')
                        ->default(fn (?Order $record) => $autoSpk($record, 'leasing_provider')),

                    Forms\Components\TextInput::make('spk_dp_amount')
                        ->label('Nominal DP (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(fn (?Order $record) => $autoSpk($record, 'dp_amount', 0)),

                    Forms\Components\TextInput::make('credit_tenor')
                        ->label('Tenor Kredit')
                        ->placeholder('Contoh: 12 Bulan, 24 Bulan, 36 Bulan')
                        ->default(fn (?Order $record) => $autoSpk($record, 'credit_tenor')),

                    Forms\Components\TextInput::make('monthly_installment')
                        ->label('Angsuran per Bulan (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(fn (?Order $record) => $autoSpk($record, 'monthly_installment')),

                    Forms\Components\TextInput::make('booking_fee')
                        ->label('Uang Tanda Jadi / Booking Fee (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(fn (?Order $record) => $autoSpk($record, 'booking_fee', 500000)),

                    Forms\Components\CheckboxList::make('spk_checklist_docs')
                        ->label('Checklist Berkas Persyaratan')
                        ->options([
                            'ktp_suami' => 'KTP Suami',
                            'ktp_istri' => 'KTP Istri',
                            'kartu_keluarga' => 'Kartu Keluarga',
                            'slip_gaji' => 'Slip Gaji',
                            'rekening_listrik' => 'Rekening Listrik',
                            'bukti_pbb' => 'Bukti PBB',
                            'rekening_tabungan_3_bulan' => 'Rekening Tabungan 3 Bulan',
                        ])
                        ->columns(4)
                        ->columnSpanFull()
                        ->default(fn (?Order $record) => $record?->spk_checklist_docs ?? ['ktp_suami', 'kartu_keluarga', 'slip_gaji', 'rekening_listrik', 'bukti_pbb']),

                    Forms\Components\Textarea::make('spk_notes')
                        ->label('Catatan SPK')
                        ->rows(3)
                        ->columnSpanFull()
                        ->default(fn (?Order $record) => $autoSpk($record, 'spk_notes')),
                ])->columns(2),
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

                        Grid::make(3)
                            ->schema([
                                Infolists\Components\IconEntry::make('is_admin_verified')
                                    ->label('Verifikasi Admin')
                                    ->boolean(),
                                Infolists\Components\TextEntry::make('admin_verified_at')
                                    ->label('Waktu Verifikasi')
                                    ->dateTime('d M Y, H:i')
                                    ->placeholder('Belum diverifikasi'),
                                Infolists\Components\TextEntry::make('adminVerifier.name')
                                    ->label('Diverifikasi Oleh')
                                    ->placeholder('-'),
                                Infolists\Components\IconEntry::make('needs_tax_invoice')
                                    ->label('Faktur Pajak')
                                    ->boolean(),
                            ]),
                        Infolists\Components\ImageEntry::make('ktp_image_path')
                            ->label('Foto KTP Customer')
                            ->disk('public')
                            ->visible(fn ($record) => ! empty($record?->ktp_image_path)),
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

                Section::make('Pembelian Kredit')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('purchase_type')
                                    ->label('Tipe')
                                    ->badge()
                                    ->color(fn (?string $state): string => $state === Order::PURCHASE_TYPE_CREDIT ? 'purple' : 'info')
                                    ->formatStateUsing(fn (?string $state): string => $state === Order::PURCHASE_TYPE_CREDIT ? 'Kredit' : 'Tunai'),
                                Infolists\Components\TextEntry::make('creditApplication.application_no')
                                    ->label('No. Pengajuan')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('creditApplication.leasing_name')
                                    ->label('Leasing')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('creditApplication.stage')
                                    ->label('Tahap')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'spk' => 'gray',
                                        'survey' => 'info',
                                        'survey_approved' => 'purple',
                                        'unit_preparation' => 'warning',
                                        'payment' => 'orange',
                                        'unit_pickup' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => $state ? \App\Models\CreditApplication::stageLabelStatic($state) : '-')
                                    ->placeholder('-'),
                            ]),
                        Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('creditApplication.dp_amount')
                                    ->label('Nominal DP (PO)')
                                    ->money('IDR')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('creditApplication.po_number')
                                    ->label('No. PO Leasing')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('credit_dp_paid')
                                    ->label('DP Tercatat')
                                    ->state(fn (Order $record): string => $record->creditApplication?->dpPayment()
                                        ? 'Rp '.number_format((float) $record->creditApplication->dpPayment()->amount, 0, ',', '.')
                                        : 'Belum ada'),
                                Infolists\Components\TextEntry::make('credit_pelunasan_paid')
                                    ->label('Pelunasan Tercatat')
                                    ->state(fn (Order $record): string => $record->creditApplication?->pelunasanPayment()
                                        ? 'Rp '.number_format((float) $record->creditApplication->pelunasanPayment()->amount, 0, ',', '.')
                                        : 'Belum ada'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record?->isCredit() ?? false),

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
                            ->formatStateUsing(fn (?string $state): string => $state ? Order::indentStatusLabelStatic($state) : '-'),
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
                    ->visible(fn (Order $record) => ! $record->isDealerPickup()),
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
                                    ->visible(fn (Order $record) => ! empty($record->shipping_snapshot)),
                                Infolists\Components\TextEntry::make('shipping_receipt')
                                    ->label('No Resi')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('shipped_at')
                                    ->label('Waktu Kirim')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->visible(fn (Order $record) => ! $record->isDealerPickup()),

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
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

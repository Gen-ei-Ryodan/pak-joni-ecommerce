<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
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
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Manajemen User';

    protected static ?string $modelLabel = 'User / Client';

    protected static ?string $pluralModelLabel = 'Manajemen User & Client';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('orders')
            ->with(['orders.items', 'addresses']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telepon / WA')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'buyer' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'buyer' => 'Customer / Buyer',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Total Pesanan')
                    ->counts('orders')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Terakhir Login')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('Belum pernah login')
                    ->sortable(),

                Tables\Columns\TextColumn::make('login_count')
                    ->label('Jml Login')
                    ->suffix('x')
                    ->placeholder('0x')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'buyer' => 'Customer / Buyer',
                        'admin' => 'Admin',
                    ]),

                Tables\Filters\Filter::make('has_logged_in')
                    ->label('Pernah Login')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('last_login_at')),

                Tables\Filters\Filter::make('never_logged_in')
                    ->label('Belum Pernah Login')
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_login_at')),

                Tables\Filters\Filter::make('has_orders')
                    ->label('Pernah Order')
                    ->query(fn (Builder $query): Builder => $query->has('orders')),
            ])
            ->actions([
                Actions\ViewAction::make()->label('Detail & Riwayat'),
                Actions\EditAction::make()->label('Edit'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Akun & Kontak')->schema([
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('No. Telepon / WhatsApp')
                        ->tel()
                        ->maxLength(30),

                    Forms\Components\TextInput::make('ktp_no')
                        ->label('No. KTP / NIK')
                        ->maxLength(30),

                    Forms\Components\Select::make('role')
                        ->label('Role')
                        ->options([
                            'buyer' => 'Customer / Buyer',
                            'admin' => 'Admin',
                        ])
                        ->default('buyer')
                        ->required(),

                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(255)
                        ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password.' : ''),
                ]),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Profil Client / User')
                ->schema([
                    Grid::make(3)->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nama Lengkap')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('phone')
                            ->label('No. HP / WhatsApp')
                            ->placeholder('-'),
                    ]),

                    Grid::make(3)->schema([
                        Infolists\Components\TextEntry::make('ktp_no')
                            ->label('No. KTP / NIK')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('role')
                            ->label('Role')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'danger',
                                'buyer' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'admin' => 'Admin',
                                'buyer' => 'Customer / Buyer',
                                default => ucfirst($state),
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Registrasi')
                            ->dateTime('d M Y, H:i'),
                    ]),
                ]),

            Section::make('Aktivitas Login')
                ->schema([
                    Grid::make(3)->schema([
                        Infolists\Components\TextEntry::make('last_login_at')
                            ->label('Waktu Login Terakhir')
                            ->dateTime('d M Y, H:i:s')
                            ->placeholder('Belum pernah login'),

                        Infolists\Components\TextEntry::make('last_login_ip')
                            ->label('IP Address Login')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('login_count')
                            ->label('Total Frekuensi Login')
                            ->suffix(' kali login')
                            ->placeholder('0 kali login'),
                    ]),
                ]),

            Section::make('Riwayat Pembelian & Pesanan (History)')
                ->description('Daftar seluruh transaksi yang pernah dilakukan oleh client ini.')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('orders')
                        ->label('')
                        ->placeholder('Client ini belum memiliki riwayat transaksi/pembelian.')
                        ->schema([
                            Grid::make(4)->schema([
                                Infolists\Components\TextEntry::make('order_no')
                                    ->label('No. Order')
                                    ->copyable()
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal Pesan')
                                    ->dateTime('d M Y, H:i'),

                                Infolists\Components\TextEntry::make('purchase_type')
                                    ->label('Tipe Transaksi')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'vehicle' => 'Motor',
                                        'part' => 'Sparepart',
                                        'cash' => 'Cash',
                                        'credit' => 'Kredit (Leasing)',
                                        default => ucfirst($state ?? '-'),
                                    }),

                                Infolists\Components\TextEntry::make('total')
                                    ->label('Total Pembayaran')
                                    ->money('IDR')
                                    ->weight('bold'),
                            ]),

                            Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status Pesanan')
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
                                        'processing' => 'Diproses',
                                        'shipped' => 'Dikirim',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('payment_status')
                                    ->label('Status Pembayaran')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'paid', 'settlement', 'success' => 'success',
                                        'unpaid', 'pending' => 'warning',
                                        'failed', 'expired' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                                Infolists\Components\TextEntry::make('payment_method')
                                    ->label('Metode Bayar')
                                    ->placeholder('-')
                                    ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : '-'),
                            ]),

                            Grid::make(1)->schema([
                                Infolists\Components\TextEntry::make('items_summary')
                                    ->label('Produk / Item yang Dibeli')
                                    ->state(fn ($record) => $record->itemsSummary())
                                    ->placeholder('-'),
                            ]),
                        ]),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Voucher';
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Kode Voucher')->schema([
                TextInput::make('code')
                    ->required()
                    ->maxLength(32)
                    ->unique(ignoreRecord: true),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),

            Section::make('Potongan Harga')->schema([
                Forms\Components\Select::make('discount_type')
                    ->label('Tipe Diskon')
                    ->options(['fixed' => 'Fixed (Rp)', 'percent' => 'Persentase (%)'])
                    ->native(false)
                    ->required(),

                TextInput::make('discount_value')
                    ->label('Nilai Diskon')
                    ->numeric()
                    ->prefix('Rp')
                    ->rule('required_if:discount_type,fixed')
                    ->required(),

                TextInput::make('min_spend')
                    ->label('Minimal Belanja')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),

                TextInput::make('max_discount')
                    ->label('Max Diskon')
                    ->numeric()
                    ->prefix('Rp')
                    ->nullable(),

                TextInput::make('quota')
                    ->label('Kuota Penggunaan')
                    ->integer()
                    ->default(null)
                    ->nullable()
                    ->rule('nullable|integer'),

                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date(),

                DatePicker::make('end_date')
                    ->label('Tanggal Akhir')
                    ->date(),
            ])->columns(2),

            Section::make('Status')->schema([
                Toggle::make('is_active')->label('Aktif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Tipe')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => match ($state) { 'fixed' => 'Fixed', 'percent' => 'Persen', default => '-' }),

                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Nilai')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_spend')
                    ->label('Min Belanja')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_discount')
                    ->label('Max Diskon')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quota')
                    ->label('Kuota')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Terpakai')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('Aktif')
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options(['active' => 'Aktif', 'inactive' => 'Tidak Aktif']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
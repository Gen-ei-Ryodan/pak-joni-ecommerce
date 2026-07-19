<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages;
use App\Models\ItemPriceList;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PriceListResource extends Resource
{
    protected static ?string $model = ItemPriceList::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Daftar Harga (PDF)';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 20;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pdf_path')
                    ->label('File PDF')
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : '-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('item_id')
                    ->label('Item')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\FileUpload::make('pdf_path')
                    ->label('File PDF')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('items/price-lists')
                    ->maxSize(10240)
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}

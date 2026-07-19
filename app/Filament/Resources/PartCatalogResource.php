<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartCatalogResource\Pages;
use App\Models\ItemPartCatalog;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PartCatalogResource extends Resource
{
    protected static ?string $model = ItemPartCatalog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Katalog Parts (PDF)';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 21;

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
                    ->directory('items/part-catalogs')
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
            'index' => Pages\ListPartCatalogs::route('/'),
            'create' => Pages\CreatePartCatalog::route('/create'),
            'edit' => Pages\EditPartCatalog::route('/{record}/edit'),
        ];
    }
}

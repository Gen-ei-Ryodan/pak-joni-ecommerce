<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartCatalogResource\Pages;
use App\Models\PartCatalog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class PartCatalogResource extends Resource
{
    protected static ?string $model = PartCatalog::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Part Katalog';
    protected static string|UnitEnum|null $navigationGroup = 'Sparepart';
    protected static ?int $navigationSort = 13;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('motor.name')->label('Motor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pdf_path')->label('PDF')->limit(30),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('motor_id')->label('Motor')->relationship('motor', 'name')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\FileUpload::make('pdf_path')->label('PDF File')->disk('public')->directory('part-catalogs')->acceptedFileTypes(['application/pdf'])->maxSize(10240)->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
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

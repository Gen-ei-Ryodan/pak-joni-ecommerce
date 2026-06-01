<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductHighlightResource\Pages;
use App\Models\ProductHighlight;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class ProductHighlightResource extends Resource
{
    protected static ?string $model = ProductHighlight::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    protected static string|UnitEnum|null $navigationGroup = 'Homepage';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('motor.name')->label('Motor')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('motor_id')->label('Motor')->relationship('motor', 'name')->required()->searchable()->preload()
                ->helperText('Hanya 1 produk highlight yang aktif. Produk baru akan menggantikan yang lama.'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductHighlights::route('/'),
            'create' => Pages\CreateProductHighlight::route('/create'),
            'edit' => Pages\EditProductHighlight::route('/{record}/edit'),
        ];
    }
}

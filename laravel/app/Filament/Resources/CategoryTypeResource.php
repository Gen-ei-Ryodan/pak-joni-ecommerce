<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryTypeResource\Pages;
use App\Models\CategoryType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class CategoryTypeResource extends Resource
{
    protected static ?string $model = CategoryType::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationLabel = 'Category Types';
    protected static string|UnitEnum|null $navigationGroup = 'KATEGORI';
    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('sort_order')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Tipe')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->maxLength(500),
                Forms\Components\TextInput::make('icon')->maxLength(100)->helperText('Nama icon Heroicons (contoh: heroicon-o-cube)'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryTypes::route('/'),
            'create' => Pages\CreateCategoryType::route('/create'),
            'edit' => Pages\EditCategoryType::route('/{record}/edit'),
        ];
    }
}

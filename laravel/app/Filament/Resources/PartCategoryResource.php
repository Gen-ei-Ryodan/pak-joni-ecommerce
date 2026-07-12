<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartCategoryResource\Pages;
use App\Models\CategoryType;
use App\Models\PartCategory;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PartCategoryResource extends Resource
{
    protected static ?string $model = PartCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori Parts';

    protected static string|UnitEnum|null $navigationGroup = 'Parts';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationItems(): array
    {
        $items = [];
        $types = CategoryType::query()->where('is_active', true)->orderBy('sort_order')->get();

        foreach ($types as $type) {
            $items[] = NavigationItem::make('part-cat-' . $type->id)
                ->label('Kategori Parts ' . $type->name)
                ->group($type->name)
                ->icon('heroicon-o-tag')
                ->sort(10)
                ->url(static::getUrl('byType', ['categoryType' => $type->id]));
        }

        return $items;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('type.name')->label('Tipe')->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parts_count')
                    ->label('Parts')
                    ->counts('parts'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_type_id')
                    ->label('Tipe')
                    ->relationship('type', 'name')
                    ->preload(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
                Forms\Components\Select::make('category_type_id')
                    ->label('Tipe Kategori')
                    ->relationship('type', 'name')
                    ->searchable()->preload()->required(),
                Forms\Components\TextInput::make('group')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartCategories::route('/'),
            'byType' => Pages\ListPartCategories::route('/type/{categoryType}'),
            'create' => Pages\CreatePartCategory::route('/create'),
            'edit' => Pages\EditPartCategory::route('/{record}/edit'),
        ];
    }
}

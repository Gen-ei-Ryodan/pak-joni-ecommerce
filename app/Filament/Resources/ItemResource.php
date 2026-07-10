<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\CategoryType;
use App\Models\Item;
use Filament\Forms;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Item';
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        $type = CategoryType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        if ($type) {
            return static::getUrl('byType', ['categoryType' => $type->id], $isAbsolute, $panel, $tenant);
        }

        return static::getUrl('byType', ['categoryType' => 0], $isAbsolute, $panel, $tenant);
    }

    public static function getNavigationItems(): array
    {
        $items = [];
        $types = CategoryType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($types as $type) {
            if ($type->slug === 'motor') {
                $url = \App\Filament\Resources\MotorResource::getUrl('index');
                $icon = 'heroicon-o-lifebuoy';
            } elseif ($type->slug === 'sparepart') {
                $url = \App\Filament\Resources\PartResource::getUrl('index');
                $icon = 'heroicon-o-cog-6-tooth';
            } else {
                $url = static::getUrl('byType', ['categoryType' => $type->id]);
                $icon = 'heroicon-o-cube';
            }

            $items[] = NavigationItem::make('catalog-' . $type->id)
                ->label($type->name)
                ->group(static::getNavigationGroup())
                ->icon($icon)
                ->sort($type->sort_order)
                ->url($url);
        }

        return $items;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')->label('Gambar')->square()->size(40)
                    ->getStateUsing(fn ($r) => $r?->thumbnail_path ? Storage::disk('public')->url($r->thumbnail_path) : null),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->label('Brand')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->sortable(),
                Tables\Columns\TextColumn::make('price')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($s) => $s === 'active' ? 'success' : 'danger'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand_id')->label('Brand')
                    ->relationship('brand', 'name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('category_id')->label('Kategori')
                    ->relationship('category', 'name')->searchable()->preload(),
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
            Section::make('Informasi Item')->schema([
                Forms\Components\Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()->preload(),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->maxLength(5000),
                Forms\Components\FileUpload::make('thumbnail_path')->label('Gambar')
                    ->image()->disk('public')->directory('items')->maxSize(3072),
            ])->columnSpan(2),

            Section::make('Harga & Stok')->schema([
                Forms\Components\TextInput::make('price')->label('Harga')
                    ->numeric()->prefix('Rp'),
                Forms\Components\TextInput::make('stock')->label('Stok')
                    ->numeric()->default(0),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])->default('active'),
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ])->columnSpan(1),
        ])->columns(3);
    }

    public static function getPages(): array
    {
        return [
            'byType' => Pages\ListItemsByType::route('/type/{categoryType}'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}

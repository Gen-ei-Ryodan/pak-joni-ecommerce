<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banners';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->size(80),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hero' => 'warning',
                        'promo' => 'success',
                        'launching' => 'info',
                        'kegiatan' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('link_url')
                    ->label('Link')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([])
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
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->options([
                        'hero' => 'Hero Banner',
                        'promo' => 'Promo',
                        'launching' => 'Launching Produk',
                        'kegiatan' => 'Kegiatan',
                    ])
                    ->default('hero')
                    ->required(),

                Forms\Components\TextInput::make('subtitle')
                    ->maxLength(255),

                Forms\Components\TextInput::make('link_url')
                    ->label('Link URL')
                    ->maxLength(255),

                Forms\Components\TextInput::make('button_text')
                    ->maxLength(50),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Image')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('banners')
                    ->preserveFilenames()
                    ->maxSize(5120),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}

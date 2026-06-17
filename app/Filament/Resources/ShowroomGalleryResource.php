<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShowroomGalleryResource\Pages;
use App\Models\ShowroomGallery;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ShowroomGalleryResource extends Resource
{
    protected static ?string $model = ShowroomGallery::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Showroom';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 11;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->square()
                    ->size(60)
                    ->getStateUsing(fn($r) => $r?->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($r->image_path) : null),
                Tables\Columns\TextColumn::make('caption')->limit(40)->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\FileUpload::make('image_path')
                ->label('Image')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('showroom')
                ->maxSize(5120)
                ->required(),
            Forms\Components\TextInput::make('caption')->maxLength(255),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShowroomGalleries::route('/'),
            'create' => Pages\CreateShowroomGallery::route('/create'),
            'edit' => Pages\EditShowroomGallery::route('/{record}/edit'),
        ];
    }
}

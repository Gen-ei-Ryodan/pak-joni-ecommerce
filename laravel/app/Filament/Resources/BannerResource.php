<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use League\Flysystem\UnableToCheckFileExistence;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Hero';

    protected static string|UnitEnum|null $navigationGroup = 'Banners';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'banners/hero';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'hero');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Preview')
                    ->size(140)
                    ->getStateUsing(fn (Banner $record): ?string => $record->image_path ? image_url($record->image_path) : null)
                    ->checkFileExistence(false),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
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
                Forms\Components\FileUpload::make('image_path')
                    ->label('Banner Image')
                    ->image()
                    ->disk('public')
                    ->directory('banners')
                    ->maxSize(5120)
                    ->getUploadedFileUsing(function (BaseFileUpload $component, string $file, $storedFileNames): ?array {
                        $storage = $component->getDisk();
                        $shouldFetchFileInformation = $component->shouldFetchFileInformation();

                        if ($shouldFetchFileInformation) {
                            try {
                                if (! $storage->exists($file)) {
                                    return null;
                                }
                            } catch (UnableToCheckFileExistence $exception) {
                                return null;
                            }
                        }

                        return [
                            'name' => basename($file),
                            'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
                            'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
                            'url' => image_url($file),
                        ];
                    })
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

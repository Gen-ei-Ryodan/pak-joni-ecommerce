<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroVideoResource\Pages;
use App\Models\HeroVideo;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToCheckFileExistence;
use UnitEnum;

class HeroVideoResource extends Resource
{
    protected static ?string $model = HeroVideo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationLabel = 'Hero Videos';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 6;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable(),

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
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('video_path')
                    ->label('Video File')
                    ->disk('public')
                    ->directory('hero-videos')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'])
                    ->maxSize(102400)
                    ->uploadProgressIndicatorPosition('inline')
                    ->helperText('Format: mp4, webm, ogg, mov. Maks 100MB.')
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
                            'url' => \App\Helpers\image_url($file),
                        ];
                    }),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroVideos::route('/'),
            'create' => Pages\CreateHeroVideo::route('/create'),
            'edit' => Pages\EditHeroVideo::route('/{record}/edit'),
        ];
    }
}

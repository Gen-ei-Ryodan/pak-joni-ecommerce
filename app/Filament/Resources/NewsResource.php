<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 6;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('publish_date', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')->label('Thumb')->square()->size(40)
                    ->getStateUsing(fn ($r) => $r?->thumbnail_path ? Storage::disk('public')->url($r->thumbnail_path) : null),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->sortable(),
                Tables\Columns\TextColumn::make('external_url')->label('URL External')->limit(30)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('publish_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => $record->slug ? route('buyer.news.show', $record->slug) : '#')
                    ->openUrlInNewTab(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255)
                ->live(onBlur: true)->afterStateUpdated(fn ($s, callable $set) => $set('slug', Str::slug($s))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('author')->maxLength(100),
            Forms\Components\TextInput::make('category')->maxLength(100),
            Forms\Components\DateTimePicker::make('publish_date')->default(now()),
            Forms\Components\FileUpload::make('thumbnail_path')->label('Thumbnail')->image()->disk('public')->directory('news')->maxSize(3072),
            Forms\Components\RichEditor::make('content')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('external_url')->label('URL External')->url()->maxLength(255)->placeholder('https://example.com'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}

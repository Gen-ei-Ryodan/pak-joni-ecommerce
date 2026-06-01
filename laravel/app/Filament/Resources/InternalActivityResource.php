<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternalActivityResource\Pages;
use App\Models\InternalActivity;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class InternalActivityResource extends Resource
{
    protected static ?string $model = InternalActivity::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Keg. Internal';
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('publish_date', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')->label('Thumb')->square()->size(40)
                    ->getStateUsing(fn ($r) => $r?->thumbnail_path ? url($r->thumbnail_path) : null),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('publish_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255)
                ->live(onBlur: true)->afterStateUpdated(fn ($s, callable $set) => $set('slug', Str::slug($s))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\DateTimePicker::make('publish_date')->default(now()),
            Forms\Components\FileUpload::make('thumbnail_path')->label('Thumbnail')->image()->disk('public')->directory('internal')->maxSize(3072),
            Forms\Components\RichEditor::make('content')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternalActivities::route('/'),
            'create' => Pages\CreateInternalActivity::route('/create'),
            'edit' => Pages\EditInternalActivity::route('/{record}/edit'),
        ];
    }
}

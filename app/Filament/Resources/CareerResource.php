<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CareerResource\Pages;
use App\Models\Career;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

use BackedEnum;
use UnitEnum;

class CareerResource extends Resource
{
    protected static ?string $model = Career::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Karir';
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 9;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('publish_date', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')->label('Image')->square()->size(40)
                    ->getStateUsing(fn ($r) => $r?->thumbnail_path ? Storage::disk('public')->url($r->thumbnail_path) : null),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('location')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn($s) => $s === 'active' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('publish_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('buyer.careers.show', $record))
                    ->openUrlInNewTab(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\TextInput::make('location')->maxLength(255),
            Forms\Components\Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])->default('active'),
            Forms\Components\DateTimePicker::make('publish_date')->default(now()),
            Forms\Components\FileUpload::make('thumbnail_path')->label('Image')->image()->disk('public')->directory('careers')->maxSize(3072),
            Forms\Components\RichEditor::make('description')->columnSpanFull(),
            Forms\Components\RichEditor::make('requirements')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCareers::route('/'),
            'create' => Pages\CreateCareer::route('/create'),
            'edit' => Pages\EditCareer::route('/{record}/edit'),
        ];
    }
}

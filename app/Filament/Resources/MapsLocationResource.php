<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MapsLocationResource\Pages;
use App\Models\MapsLocation;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MapsLocationResource extends Resource
{
    protected static ?string $model = MapsLocation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Maps Location';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'main' => 'success',
                        'workshop' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'main' => 'Main Location',
                        'workshop' => 'Workshop',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('address')->limit(40),
                Tables\Columns\TextColumn::make('latitude'),
                Tables\Columns\TextColumn::make('longitude'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Select::make('type')
                ->options([
                    'main' => 'Main Location',
                    'workshop' => 'Workshop / Bengkel',
                ])
                ->required()
                ->default('main'),
            Forms\Components\Textarea::make('address')->required()->maxLength(500),
            Forms\Components\TextInput::make('latitude')
                ->required()
                ->numeric()
                ->step(0.0000001)
                ->placeholder('-6.2088'),
            Forms\Components\TextInput::make('longitude')
                ->required()
                ->numeric()
                ->step(0.0000001)
                ->placeholder('106.8456'),
            Forms\Components\TextInput::make('phone')->maxLength(50)->tel(),
            Forms\Components\TextInput::make('whatsapp')->maxLength(50),
            Forms\Components\TextInput::make('email')->maxLength(255)->email(),
            Forms\Components\Textarea::make('description')->maxLength(500),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMapsLocations::route('/'),
            'create' => Pages\CreateMapsLocation::route('/create'),
            'edit' => Pages\EditMapsLocation::route('/{record}/edit'),
        ];
    }
}

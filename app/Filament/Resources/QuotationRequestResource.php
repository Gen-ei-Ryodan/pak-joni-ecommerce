<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationRequestResource\Pages;
use App\Models\QuotationRequest;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class QuotationRequestResource extends Resource
{
    protected static ?string $model = QuotationRequest::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected static string|UnitEnum|null $navigationGroup = 'Inbox';
    protected static ?int $navigationSort = 15;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('message')->limit(60)->searchable(),
                Tables\Columns\IconColumn::make('is_read')->boolean()->label('Read'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Received'),
            ])
            ->actions([
                Actions\Action::make('mark_read')
                    ->label('Mark Read')
                    ->icon('heroicon-o-check')
                    ->action(fn ($record) => $record->update(['is_read' => true]))
                    ->hidden(fn ($record) => $record->is_read),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotationRequests::route('/'),
        ];
    }

    public static function canCreate(): bool { return false; }
}

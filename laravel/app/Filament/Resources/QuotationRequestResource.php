<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationRequestResource\Pages;
use App\Models\QuotationRequest;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->label('Email'),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->label('Telepon'),

                Tables\Columns\TextColumn::make('message')
                    ->limit(80)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->message)
                    ->label('Pesan'),

                Tables\Columns\IconColumn::make('is_read')
                    ->boolean()
                    ->label('Dibaca'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Diterima'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Dibaca')
                    ->falseLabel('Belum Dibaca'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\Action::make('mark_read')
                    ->label('Tandai Dibaca')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['is_read' => true]))
                    ->hidden(fn ($record) => $record->is_read),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('mark_read_bulk')
                        ->label('Tandai Dibaca')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_read' => true])),
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Quotation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nama'),
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Telepon')
                                    ->copyable(),
                                Infolists\Components\IconEntry::make('is_read')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueLabel('Sudah Dibaca')
                                    ->falseLabel('Belum Dibaca'),
                            ]),
                    ]),

                Section::make('Pesan')
                    ->schema([
                        Infolists\Components\TextEntry::make('message')
                            ->label('')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Tambahan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Diterima')
                                    ->dateTime('d M Y H:i'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Terakhir Diupdate')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotationRequests::route('/'),
            'view' => Pages\ViewQuotationRequest::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MotorResource\Pages;
use App\Models\Motor;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class MotorResource extends Resource
{
    protected static ?string $model = Motor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lifebuoy';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('Thumb')
                    ->square()
                    ->size(40)
                    ->getStateUsing(fn ($record) => $record?->thumbnail_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->thumbnail_path) : null),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('colors_count')
                    ->label('Varian')
                    ->counts('colors'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('parts_count')
                    ->label('Parts')
                    ->counts('parts'),

                Tables\Columns\TextColumn::make('stock_updated_at')
                    ->label('Last Update Stock')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                Section::make('Data Utama')
                    ->schema([
                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name', fn ($query, $get) => $get('brand_id')
                                ? $query->where('brand_id', $get('brand_id'))
                                : $query
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(2099),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active'),

                        Forms\Components\Select::make('stock_status')
                            ->label('Status Stok')
                            ->options([
                                'ready' => 'Ready Stock',
                                'indent' => 'Indent',
                            ])
                            ->default('ready')
                            ->required(),

                        Forms\Components\FileUpload::make('thumbnail_path')
                            ->label('Thumbnail')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('150')
                            ->disk('public')
                            ->directory('motors/thumbnails')
                            ->maxSize(5120),

                        Forms\Components\Textarea::make('short_description')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull()
                            ->disableToolbarButtons(['link', 'blockquote', 'codeBlock', 'bulletList', 'orderedList', 'table', 'attachFiles']),
                    ])
                    ->columns(2),

                Section::make('Varian Warna')
                    ->schema([
                        Forms\Components\Repeater::make('colors')
                            ->relationship('colors')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Warna')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\ColorPicker::make('color_code')
                                    ->label('Kode Warna'),
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Gambar Warna')
                                    ->image()
                                    ->imagePreviewHeight('100')
                                    ->disk('public')
                                    ->directory('motors/colors')
                                    ->maxSize(5120),
                                Forms\Components\TextInput::make('weight')
                                    ->label('Weight (gram)')
                                    ->integer()
                                    ->minValue(0)
                                    ->default(100),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->hidden(),
                            ])
                            ->columns(2)
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Tambah Varian Warna'),
                    ])
                    ->collapsible(),

                Section::make('Spesifikasi Produk')
                    ->schema([
                        Forms\Components\Repeater::make('specifications')
                            ->relationship('specifications')
                            ->schema([
                                Forms\Components\Select::make('group')
                                    ->label('Grup')
                                    ->options([
                                        'Mesin dan Performa' => 'Mesin dan Performa',
                                        'Dimensi dan Berat' => 'Dimensi dan Berat',
                                        'Sasis' => 'Sasis',
                                        'Fitur' => 'Fitur',
                                        'Umum' => 'Umum',
                                        'Mesin' => 'Mesin',
                                        'Baterai' => 'Baterai',
                                        'Performa' => 'Performa',
                                        'Lainnya' => 'Lainnya',
                                    ])
                                    ->default('Umum')
                                    ->required(),
                                Forms\Components\TextInput::make('key')
                                    ->label('Nama Spesifikasi')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Motor Type, Battery, Range'),
                                Forms\Components\TextInput::make('value')
                                    ->label('Nilai')
                                    ->required()
                                    ->maxLength(500)
                                    ->placeholder('Contoh: BLDC Hub Motor, 72V 32Ah'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->hidden(),
                            ])
                            ->columns(3)
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Tambah Spesifikasi'),
                    ])
                    ->collapsible(),

                Section::make('Galeri Gambar')
                    ->schema([
                        Forms\Components\FileUpload::make('gallery')
                            ->label('')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('motors/gallery')
                            ->maxSize(5120)
                            ->multiple()
                            ->reorderable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ])
                    ->collapsible(),

                Section::make('Frame 360° Produk')
                    ->description('Upload banyak frame gambar yang akan diputar otomatis seperti 360° (24/36/48 frame).')
                    ->schema([
                        Forms\Components\Repeater::make('images360')
                            ->relationship('images360')
                            ->schema([
                                Forms\Components\FileUpload::make('path')
                                    ->label('Frame Image')
                                    ->image()
                                    ->imagePreviewHeight('120')
                                    ->disk('public')
                                    ->directory('motors/360frames')
                                    ->maxSize(2048)
                                    ->required(),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->hidden(),
                            ])
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Tambah Frame'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMotors::route('/'),
            'create' => Pages\CreateMotor::route('/create'),
            'edit' => Pages\EditMotor::route('/{record}/edit'),
        ];
    }
}

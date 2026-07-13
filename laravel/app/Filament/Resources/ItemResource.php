<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\CategoryType;
use App\Models\Item;
use Filament\Actions;
use Filament\Forms;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Item';
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        $type = CategoryType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        if ($type) {
            return static::getUrl('byType', ['categoryType' => $type->id], $isAbsolute, $panel, $tenant);
        }

        return static::getUrl('byType', ['categoryType' => 0], $isAbsolute, $panel, $tenant);
    }

    public static function getNavigationItems(): array
    {
        $items = [];
        $types = CategoryType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($types as $type) {
            $items[] = NavigationItem::make('catalog-' . $type->id)
                ->label($type->name)
                ->group(static::getNavigationGroup())
                ->icon('heroicon-o-cube')
                ->sort($type->sort_order)
                ->url(static::getUrl('byType', ['categoryType' => $type->id]));
        }

        return $items;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')->label('Gambar')->square()->size(40)
                    ->getStateUsing(fn ($r) => $r?->thumbnail_path ? Storage::disk('public')->url($r->thumbnail_path) : null),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->label('Brand')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->sortable(),
                Tables\Columns\TextColumn::make('year')->label('Tahun')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('stock_status')->label('Stok')->badge()
                    ->color(fn ($s) => match ($s) { 'ready' => 'success', 'indent' => 'warning', default => 'gray' })
                    ->formatStateUsing(fn ($s) => match ($s) { 'ready' => 'Ready', 'indent' => 'Indent', default => $s }),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($s) => $s === 'active' ? 'success' : 'danger'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand_id')->label('Brand')
                    ->relationship('brand', 'name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('category_id')->label('Kategori')
                    ->relationship('category', 'name')->searchable()->preload(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Data Utama')->schema([
                Forms\Components\Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()->preload(),

                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()->preload(),

                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('year')->label('Tahun')->numeric()->minValue(1900)->maxValue(2099),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])->default('active'),
                Forms\Components\Select::make('stock_status')
                    ->label('Status Stok')
                    ->options(['ready' => 'Ready Stock', 'indent' => 'Indent'])->default('ready')->required(),

                Forms\Components\FileUpload::make('thumbnail_path')->label('Thumbnail')
                    ->image()->imageEditor()->disk('public')->directory('items/thumbnails')->maxSize(5120),

                Forms\Components\Textarea::make('short_description')->maxLength(500)->columnSpanFull(),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull()
                    ->disableToolbarButtons(['link', 'blockquote', 'codeBlock', 'bulletList', 'orderedList', 'table', 'attachFiles']),
            ])->columns(2),

            Section::make('Harga & Stok')->schema([
                Forms\Components\TextInput::make('price')->label('Harga')
                    ->numeric()->prefix('Rp'),
                Forms\Components\TextInput::make('stock')->label('Stok')
                    ->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ])->columns(2),

            Section::make('Varian Warna')
                ->schema([
                    Forms\Components\Repeater::make('colors')
                        ->relationship('colors')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Warna')->required()->maxLength(255),
                            Forms\Components\ColorPicker::make('color_code')
                                ->label('Kode Warna'),
                            Forms\Components\FileUpload::make('image_path')
                                ->label('Gambar Warna')->image()->imagePreviewHeight('100')
                                ->disk('public')->directory('items/colors')->maxSize(5120),
                            Forms\Components\TextInput::make('weight')
                                ->label('Weight (gram)')->integer()->minValue(0)->default(100),
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()->default(0)->hidden(),
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
                                ->default('Umum')->required(),
                            Forms\Components\TextInput::make('key')
                                ->label('Nama Spesifikasi')->required()->maxLength(255)
                                ->placeholder('Contoh: Motor Type, Battery, Range'),
                            Forms\Components\TextInput::make('value')
                                ->label('Nilai')->required()->maxLength(500)
                                ->placeholder('Contoh: BLDC Hub Motor, 72V 32Ah'),
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()->default(0)->hidden(),
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
                        ->image()->imageEditor()->disk('public')
                        ->directory('items/gallery')->maxSize(5120)
                        ->multiple()->reorderable()
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
                                ->label('Frame Image')->image()->imagePreviewHeight('120')
                                ->disk('public')->directory('items/360frames')->maxSize(2048)->required(),
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()->default(0)->hidden(),
                        ])
                        ->orderColumn('sort_order')
                        ->defaultItems(0)
                        ->collapsible()
                        ->addActionLabel('Tambah Frame'),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Daftar Harga (PDF)')
                ->schema([
                    Forms\Components\Repeater::make('priceLists')
                        ->relationship('priceLists')
                        ->schema([
                            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
                            Forms\Components\FileUpload::make('pdf_path')
                                ->label('File PDF')->acceptedFileTypes(['application/pdf'])
                                ->disk('public')->directory('items/price-lists')->maxSize(10240)->required(),
                            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->hidden(),
                        ])
                        ->columns(2)
                        ->orderColumn('sort_order')
                        ->defaultItems(0)
                        ->collapsible()
                        ->addActionLabel('Tambah Daftar Harga'),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Katalog Parts (PDF)')
                ->schema([
                    Forms\Components\Repeater::make('partCatalogs')
                        ->relationship('partCatalogs')
                        ->schema([
                            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
                            Forms\Components\FileUpload::make('pdf_path')
                                ->label('File PDF')->acceptedFileTypes(['application/pdf'])
                                ->disk('public')->directory('items/part-catalogs')->maxSize(10240)->required(),
                            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->hidden(),
                        ])
                        ->columns(2)
                        ->orderColumn('sort_order')
                        ->defaultItems(0)
                        ->collapsible()
                        ->addActionLabel('Tambah Katalog Parts'),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'byType' => Pages\ListItemsByType::route('/type/{categoryType}'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}

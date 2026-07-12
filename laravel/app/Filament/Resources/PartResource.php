<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartResource\Pages;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Part360Image;
use App\Models\PartImage;
use App\Models\PartVariant;
use App\Services\ImageService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use UnitEnum;

class PartResource extends Resource
{
    protected static ?string $model = Part::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Parts';

    protected static string|UnitEnum|null $navigationGroup = 'Parts';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationItems(): array
    {
        $items = [];
        $types = CategoryType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($types as $type) {
            $items[] = NavigationItem::make('parts-' . $type->id)
                ->label('Parts ' . $type->name)
                ->group($type->name)
                ->icon('heroicon-o-cog-6-tooth')
                ->sort(20)
                ->url(static::getUrl('byType', ['categoryType' => $type->id]));
        }

        return $items;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('Thumb')
                    ->square()
                    ->size(40)
                    ->getStateUsing(fn ($record) => $record?->thumbnail_path ? Storage::disk('public')->url($record->thumbnail_path) : null),

                Tables\Columns\TextColumn::make('type.name')->label('Tipe')->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Stock')
                    ->numeric()
                    ->getStateUsing(fn ($record) => $record->totalStock()),

                Tables\Columns\TextColumn::make('variants_count')
                    ->label('Variants')
                    ->counts('variants'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Stok')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ready' => 'success',
                        'indent' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ready' => 'Ready',
                        'indent' => 'Indent',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('stock_updated_at')
                    ->label('Last Update Stock')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_type_id')
                    ->label('Tipe')
                    ->relationship('type', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('part_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->headerActions([
                Actions\Action::make('import_stock')
                    ->label('Import Stock Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Pilih file Excel (.xlsx)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->required()
                            ->preserveFilenames(),
                    ])
                    ->action(function (array $data) {
                        $file = $data['file'];
                        $import = new \App\Filament\Imports\PartVariantStockImport();
                        $result = $import->import($file->getRealPath());

                        $msg = "Updated: {$result['updated']} variants.";
                        if ($result['skipped'] > 0) {
                            $msg .= " Skipped: {$result['skipped']}.";
                        }
                        if (!empty($result['errors'])) {
                            $msg .= ' Errors: ' . implode('; ', array_slice($result['errors'], 0, 5));
                        }
                        \Filament\Notifications\Notification::make()
                            ->title('Stock Import')
                            ->body($msg)
                            ->success()
                            ->send();
                    }),
            ])
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
                Section::make('Basic Information')
                    ->schema([

                        Forms\Components\Select::make('category_type_id')
                            ->label('Tipe Kategori')
                            ->relationship('type', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('part_category_id', null)),

                        Forms\Components\Select::make('part_category_id')
                            ->label('Category')
                            ->options(fn ($get) => PartCategory::query()
                                ->when($get('category_type_id'), fn ($q, $typeId) => $q->where('category_type_id', $typeId))
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($cat) => [$cat->id => $cat->group . ' - ' . $cat->name])
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required()
                            ->default('active'),

                        Forms\Components\Select::make('stock_status')
                            ->label('Status Stok')
                            ->options([
                                'ready' => 'Ready Stock',
                                'indent' => 'Indent',
                            ])
                            ->required()
                            ->default('ready'),

                        Forms\Components\TextInput::make('base_price')
                            ->label('Base Price')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Textarea::make('short_description')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull()
                            ->disableToolbarButtons(['link', 'blockquote', 'codeBlock', 'bulletList', 'orderedList', 'table', 'attachFiles']),

                        Forms\Components\Textarea::make('specification')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Thumbnail')
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->label('Thumbnail Image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('parts/thumbnails')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ]),

                Section::make('Gallery Images')
                    ->schema([
                        Forms\Components\FileUpload::make('gallery')
                            ->label('')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('parts/gallery')
                            ->maxSize(5120)
                            ->multiple()
                            ->reorderable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ]),

                Section::make('Compatible Products')
                    ->schema(function (?Part $record) {
                        $fields = [];

                        $types = CategoryType::query()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->get();

                        foreach ($types as $ct) {
                            $options = Item::query()
                                ->where('category_type_id', $ct->id)
                                ->where('is_active', true)
                                ->with('brand')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($i) => [$i->id => ($i->brand ? $i->brand->name . ' - ' : '') . $i->name]);

                            $currentIds = $record
                                ? $record->items()->where('category_type_id', $ct->id)->pluck('items.id')->toArray()
                                : [];

                            $fields[] = Forms\Components\Select::make('_compatible_items_' . $ct->id)
                                ->label($ct->name)
                                ->options($options)
                                ->multiple()
                                ->searchable()
                                ->default($currentIds);
                        }

                        return $fields;
                    })
                    ->columns(2),

                Section::make('Foto 360° Produk')
                    ->description('Upload foto produk dari berbagai sudut secara berurutan (searah jarum jam) agar fitur rotasi 360° dapat berjalan dengan baik. Minimal 4 foto.')
                    ->schema([
                        Forms\Components\Repeater::make('images360')
                            ->relationship('images360')
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Frame Image')
                                    ->image()
                                    ->imagePreviewHeight('120')
                                    ->disk('public')
                                    ->directory('parts/360frames')
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->required(),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->hidden(),
                            ])
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Tambah Foto 360°'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Variants')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('variants')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->required()
                                    ->maxLength(64)
                                    ->distinct(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->required(),
                                Forms\Components\TextInput::make('stock')
                                    ->integer()
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('weight')
                                    ->label('Weight (gram)')
                                    ->integer()
                                    ->minValue(0)
                                    ->default(100),
                                Forms\Components\Toggle::make('is_default')
                                    ->label('Default'),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Add Variant'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParts::route('/'),
            'byType' => Pages\ListParts::route('/type/{categoryType}'),
            'create' => Pages\CreatePart::route('/create'),
            'edit' => Pages\EditPart::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreAddressResource\Pages;
use App\Models\StoreAddress;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnitEnum;

class StoreAddressResource extends Resource
{
    protected static ?string $model = StoreAddress::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Store Address';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address_line1')
                    ->label('Address')
                    ->limit(40),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('province')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Postal Code')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                // --- Region options helpers ---
                Section::make('Informasi Alamat Toko')
                    ->description('Alamat ini digunakan sebagai asal pengiriman (origin) untuk kalkulasi ongkir Biteship.')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('Nama Alamat')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Gudang Utama Surabaya')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('address_line1')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(2)
                            ->placeholder('Jl. Contoh No. 123, RT/RW ...')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('province')
                            ->label('Provinsi')
                            ->searchable()
                            ->required()
                            ->options(fn () => static::loadProvinceOptions())
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('city', null))
                            ->columnSpan(1),

                        Forms\Components\Select::make('city')
                            ->label('Kota / Kabupaten')
                            ->searchable()
                            ->required()
                            ->options(fn (callable $get) => static::loadRegencyOptions($get('province')))
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('district', null))
                            ->columnSpan(1),

                        Forms\Components\Select::make('district')
                            ->label('Kecamatan')
                            ->searchable()
                            ->required()
                            ->options(fn (callable $get) => static::loadDistrictOptions($get('city')))
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->required()
                            ->maxLength(16)
                            ->placeholder('60111')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('(021) 555-1234')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('info@jomoto.co.id')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_default')
                            ->label('Jadikan alamat default')
                            ->helperText('Hanya satu alamat yang bisa menjadi default. Jika diaktifkan, alamat default sebelumnya akan otomatis dinonaktifkan.'),
                    ])
                    ->columnSpan(2),
            ])
            ->columns(4);
    }

    // ───────────────────────────── helpers ─────────────────────────────

    /**
     * Load province list from cached API.
     * Returns ['code' => 'name', ...] but we store 'name' as the value.
     */
    private static function loadProvinceOptions(): array
    {
        return Cache::remember('filament:provinces', now()->addDays(30), function () {
            $response = Http::get('https://wilayah.id/api/provinces.json');
            return collect($response->json('data', []))
                ->pluck('name', 'name')
                ->toArray();
        });
    }

    /**
     * Load regency/city list filtered by province name.
     */
    private static function loadRegencyOptions(?string $provinceName): array
    {
        if (blank($provinceName)) {
            return [];
        }

        return Cache::remember("filament:regencies:{$provinceName}", now()->addDays(30), function () use ($provinceName) {
            $code = static::getProvinceCode($provinceName);
            if (! $code) {
                return [];
            }

            $response = Http::get("https://wilayah.id/api/regencies/{$code}.json");
            return collect($response->json('data', []))
                ->pluck('name', 'name')
                ->toArray();
        });
    }

    /**
     * Load district list filtered by regency/city name.
     */
    private static function loadDistrictOptions(?string $regencyName): array
    {
        if (blank($regencyName)) {
            return [];
        }

        return Cache::remember("filament:districts:{$regencyName}", now()->addDays(30), function () use ($regencyName) {
            $code = static::getRegencyCode($regencyName);
            if (! $code) {
                return [];
            }

            $response = Http::get("https://wilayah.id/api/districts/{$code}.json");
            return collect($response->json('data', []))
                ->pluck('name', 'name')
                ->toArray();
        });
    }

    private static function getProvinceCode(string $name): ?string
    {
        $response = Http::get('https://wilayah.id/api/provinces.json');
        return collect($response->json('data', []))->firstWhere('name', $name)['code'] ?? null;
    }

    private static function getRegencyCode(string $name): ?string
    {
        // Look through all provinces to find the regency
        $provinces = collect(Http::get('https://wilayah.id/api/provinces.json')->json('data', []));
        foreach ($provinces as $province) {
            $cacheKey = "filament:regencies_data:{$province['code']}";
            $regencies = Cache::remember($cacheKey, now()->addDays(30), function () use ($province) {
                $response = Http::get("https://wilayah.id/api/regencies/{$province['code']}.json");
                return $response->json('data', []);
            });
            $found = collect($regencies)->firstWhere('name', $name);
            if ($found) {
                return $found['code'];
            }
        }
        return null;
    }

    public static function getPages(): array
    {
        return [
            'index' => StoreAddressResource\Pages\ListStoreAddresses::route('/'),
            'create' => StoreAddressResource\Pages\CreateStoreAddress::route('/create'),
            'edit' => StoreAddressResource\Pages\EditStoreAddress::route('/{record}/edit'),
        ];
    }
}

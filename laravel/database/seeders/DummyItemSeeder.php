<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemColor;
use App\Models\ItemImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyItemSeeder extends Seeder
{
    private const ALL_IMAGES = [
        'images/seeder/1.jpeg', 'images/seeder/2.jpeg', 'images/seeder/3.jpeg',
        'images/seeder/4.jpeg', 'images/seeder/5.jpeg', 'images/seeder/6.jpeg',
        'images/seeder/part1.jpeg', 'images/seeder/part2.jpeg', 'images/seeder/part3.jpeg',
    ];

    private function pic(int $id): string
    {
        return self::ALL_IMAGES[$id % count(self::ALL_IMAGES)];
    }

    public function run(): void
    {
        $this->createCategoryTypes();
        $this->createBrands();
        $this->createCategories();
        $this->createItems();
    }

    private function createCategoryTypes(): void
    {
        $types = [
            ['Motor', 'motor', 'Kategori produk motor', 'heroicon-o-lifebuoy'],
            ['Sparepart', 'sparepart', 'Kategori produk sparepart', 'heroicon-o-cog-6-tooth'],
            ['Mobil', 'mobil', 'Kategori produk mobil', 'heroicon-o-truck'],
            ['ATV', 'atv', 'Kategori produk ATV', 'heroicon-o-rocket-launch'],
        ];
        foreach ($types as $i => [$name, $slug, $desc, $icon]) {
            CategoryType::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $desc, 'icon' => $icon, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
        $this->command->info('✓ CategoryTypes: ' . CategoryType::count());
    }

    private function createBrands(): void
    {
        foreach ([
            ['WMOTO', 'wmoto'], ['SM SPORT', 'sm-sport'],
            ['CF MOTO', 'cf-moto'], ['ZONTES', 'zontes'],
            ['KOVE', 'kove'], ['ZEEHO', 'zeeho'],
        ] as $i => [$name, $slug]) {
            Brand::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => "Brand {$name}", 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
        $this->command->info('✓ Brands: ' . Brand::count());
    }

    private function createCategories(): void
    {
        $motorType = CategoryType::where('slug', 'motor')->first();
        $atvType = CategoryType::where('slug', 'atv')->first();
        $names = [
            'Cruiser', 'Matic', 'Moped', 'Classic', 'Sport', 'Sport Racing',
            'Naked Bike', 'Touring', 'Adventure', 'Trail', 'Rally',
            'Multi Touring', 'Maxi Scooter', 'Papio', 'EV', 'ATV',
            'Sport ATV', 'TR-G', 'Utility', 'Letbe Series',
        ];
        foreach ($names as $i => $name) {
            $typeId = (in_array($name, ['ATV', 'Sport ATV']) && $atvType) ? $atvType->id : $motorType->id;
            if (!$typeId) continue;
            Category::updateOrCreate(
                ['category_type_id' => $typeId, 'slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
        $this->command->info('✓ Categories: ' . Category::count());
    }

    private function createItems(): void
    {
        $motorType = CategoryType::where('slug', 'motor')->first();
        $atvType = CategoryType::where('slug', 'atv')->first();
        $brandMap = Brand::pluck('id', 'slug')->toArray();
        $catMap = Category::pluck('id', 'name')->toArray();

        $items = [
            ['wmoto', 'Cruiser', 'MORBIUS', '249 CC', 66900000, ['HITAM']],
            ['wmoto', 'Letbe Series', 'LETBE SERIES NEON', '125 CC', 19800000, ['MAGIC GREY', 'ELIZABETH WHITE', 'FREEDOM YELLOW', 'MAPPLE LEAF RED', 'WATERFALL BLUE']],
            ['wmoto', 'Letbe Series', 'LETBE SERIES ISLAND', '160 CC', 26800000, ['GRAY', 'BLACK', 'YELLOW']],
            ['wmoto', 'Letbe Series', 'FLYGON 250', '231 CC', 43800000, ['ARMOR GREEN', 'AURORA SILVER']],
            ['wmoto', 'Matic', 'SWIFTBEE 125CC', '125 CC', 22500000, ['WHITE', 'BLUE']],
            ['wmoto', 'Matic', 'GRETA', '150 CC', 22900000, ['RED', 'CEMENT GREY', 'SHINY WHITE', 'BLACK']],
            ['wmoto', 'Matic', 'VELORA 150', '149.6 CC', 27800000, ['WHITE DEW', 'MINT GREEN', 'MYSTIC BLUE']],
            ['wmoto', 'Moped', 'PORTER 125', '119.9 CC', 23500000, ['SUN FLOWER', 'POWDER BLUE', 'BLACK']],
            ['sm-sport', 'Cruiser', 'V16 PLUS', '249 CC', 61900000, ['CEMENT GREY']],
            ['sm-sport', 'EV', 'E-CLASSIC', '1500 W', 35400000, ['YELLOW']],
            ['sm-sport', 'Trail', 'GY 150 ARIES', '149.4 CC', 26950000, ['WHITE']],
            ['cf-moto', 'ATV', 'CFORCE 110', '110', null, ['ROCKET RED', 'BAJA BLUE']],
            ['cf-moto', 'ATV', 'CFORCE C4', '400', null, ['CYPRESS GREEN', 'GEDANITE GREY']],
            ['cf-moto', 'ATV', 'CFORCE 625', '580', null, ['HUNTER GREEN', 'TRUE TIMBER CAMO', 'NEBULA BLACK']],
            ['cf-moto', 'Classic', 'CFMOTO 250 CLC', '250 CC', 66200000, ['TEAL GREEN', 'NEBULA BLACK', 'BORDEAUX RED']],
            ['cf-moto', 'Classic', 'CFMOTO 450 CLC', '450 CC', 141000000, ['RUBY RED', 'NEBULA WHITE', 'NEBULA BLACK', 'GALAXY GREY']],
            ['cf-moto', 'Classic', 'CFMOTO CLC 450 BOBBER', '450 CC', 149500000, ['IVORY WHITE', 'NEBULA BLACK']],
            ['cf-moto', 'Multi Touring', 'CFMOTO 250 DUAL LITE', '249 CC', 48800000, ['TUNDRA GREY', 'POLAR WHITE', 'LIME GREEN']],
            ['cf-moto', 'Multi Touring', 'CFMOTO 450 MT', '450 CC', 164000000, ['ZEPHYR BLUE LOW FENDER', 'NEBULA BLACK LOW FENDER', 'ZEPHYR BLUE HIGH FENDER', 'NEBULA BLACK HIGH FENDER']],
            ['cf-moto', 'Multi Touring', 'CFMOTO 800 MTX', '800 CC', 277500000, ['ZEPHYR BLUE', 'NEBULA BLACK']],
            ['cf-moto', 'Multi Touring', 'CFMOTO 800 MT SPORT', '799 CC', 286300000, ['NEBULA BLACK', 'TWILIGHT BLUE']],
            ['cf-moto', 'Multi Touring', 'CFMOTO 800 MT EXPLORE', '800 CC', 328300000, ['GEM BLACK', 'STARRY WHITE']],
            ['cf-moto', 'Papio', 'PAPIO RACER', '126 CC', 39500000, ['NEBULA WHITE', 'DESERT KHAKI', 'CHAMPION BLUE']],
            ['cf-moto', 'Papio', 'PAPIO X0-1', '125 CC', 35800000, ['FIERY RED', 'NEBULA WHITE', 'MOSS GREEN']],
            ['cf-moto', 'Papio', 'PAPIO X0-2', '125 CC', 38900000, ['GALAXY GREY', 'NEBULA BLACK']],
            ['cf-moto', 'Sport', 'ZFORCE 800', '800', null, ['MAGMA RED', 'NABULA BLACK']],
            ['cf-moto', 'Sport', 'ZFORCE 950 2 SEAT', '', null, ['MAGMA RED', 'NABULA BLACK']],
            ['cf-moto', 'Sport', 'CF1000SU-D', '963', null, ['NEBULA BLACK', 'LAVA ORANGE', 'DESERT TAN']],
            ['cf-moto', 'Sport Racing', 'CFMOTO 250 SR LITE', '250 CC', 48500000, ['NEBULA BLACK', 'BLUE RIGHT']],
            ['cf-moto', 'Sport Racing', 'CFMOTO 450 SR', '450 CC', 151400000, ['NEBULA BLACK', 'NEBULA WHITE']],
            ['cf-moto', 'Sport Racing', 'CFMOTO 500 SR - VOOM', '500 CC', 194000000, ['ZEPHYR BLUE', 'NEBULA WHITE']],
            ['cf-moto', 'TR-G', 'CFMOTO 1250TR-G', '12800 CC', null, ['NEBULA WHITE', 'TWILIGHT BLUE']],
            ['cf-moto', 'Utility', 'UFORCE 1000', '963', null, ['TWILIGHT BLUE', 'DESERT TAN', 'NEBULA BLACK']],
            ['zontes', 'Cruiser', 'ZT-155 C2', '155 CC', 42800000, ['']],
            ['zontes', 'Maxi Scooter', '250 E', '249.8 CC', 58000000, ['MATTE BLACK']],
            ['zontes', 'Maxi Scooter', '268 K', '367.6 CC', 138000000, ['SPECIAL BLACK', 'MECH GREY']],
            ['zontes', 'Maxi Scooter', '368 E', '368 CC', 141000000, ['BLUE ACCENT', 'BLACK']],
            ['zontes', 'Maxi Scooter', '368 G', '368 CC', 156500000, ['WHITE BLUE', 'BLACK', 'GREEN']],
            ['kove', 'Adventure', '625X PRO', '581 CC', 219500000, ['BLUE', 'BLACK']],
            ['kove', 'Adventure', '625X MAX', '581 CC', 235000000, ['BLUE', 'BLACK']],
            ['kove', 'Rally', '450 RALLY RE', '449 CC', 188000000, ['GREEN', 'RED']],
            ['zeeho', 'EV', 'ZEEHO AE4 NON ABS', '74V27AH', 28800000, ['HELIOS ORANGE', 'VELOCITY GRAY']],
            ['zeeho', 'EV', 'ZEEHO AE4 ABS', '74V27AH', 31500000, ['HELIOS ORANGE', 'VELOCITY GRAY']],
            ['zeeho', 'EV', 'ZEEHO AE6', '69V27AH', 37500000, ['VIBRANT WHITE', 'SLATE GREY', 'LIQUID BLACK']],
        ];

        $counter = 0;
        foreach ($items as [$brandSlug, $catName, $name, $spek, $price, $colors]) {
            $catTypeId = $motorType->id;
            if (in_array($catName, ['ATV', 'Sport ATV']) && $atvType) {
                $catTypeId = $atvType->id;
            }

            $item = Item::create([
                'category_type_id' => $catTypeId,
                'brand_id' => $brandMap[$brandSlug] ?? null,
                'category_id' => $catMap[$catName] ?? null,
                'name' => $name,
                'slug' => Str::slug($name),
                'price' => $price,
                'description' => '<p>' . $name . ' — ' . $spek . '</p>',
                'short_description' => $name . ' | ' . $spek,
                'thumbnail_path' => $this->pic($counter + 50),
                'status' => 'active',
                'is_active' => true,
                'stock_status' => $price ? 'ready' : 'indent',
            ]);

            ItemImage::create(['item_id' => $item->id, 'path' => $this->pic($counter + 50), 'sort_order' => 0]);

            foreach ($colors as $ci => $colorName) {
                if (empty(trim($colorName))) continue;
                ItemColor::create([
                    'item_id' => $item->id, 'name' => $colorName,
                    'image_path' => $this->pic($counter + 100 + $ci),
                    'weight' => 100000, 'sort_order' => $ci,
                ]);
            }
            $counter++;
        }
        $this->command->info("✓ Items: {$counter} items, " . ItemColor::count() . " color variants");
    }
}

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
    private array $brandIds = [];
    private array $categoryIds = [];

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
        $path = base_path('dummy.txt');
        if (!file_exists($path)) {
            $this->command->warn('dummy.txt not found, skipping DummyItemSeeder');
            return;
        }

        $this->createCategoryTypes();
        $this->createBrands();
        $this->createCategories();
        $this->createItemsFromDummy();
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
                ['name' => $name, 'description' => $desc, 'icon' => $icon,
                 'sort_order' => $i + 1, 'is_active' => true]
            );
        }

        $this->command->info('✓ CategoryTypes: ' . CategoryType::count());
    }

    private function createBrands(): void
    {
        $brands = [
            ['WMOTO', 'wmoto'], ['SM SPORT', 'sm-sport'],
            ['CF MOTO', 'cf-moto'], ['ZONTES', 'zontes'],
            ['KOVE', 'kove'], ['ZEEHO', 'zeeho'],
        ];

        foreach ($brands as $i => [$name, $slug]) {
            $brand = Brand::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => "Brand {$name}", 'sort_order' => $i + 1, 'is_active' => true]
            );
            $this->brandIds[$slug] = $brand->id;
        }

        $this->command->info('✓ Brands: ' . Brand::count());
    }

    private function createCategories(): void
    {
        $names = [
            'Cruiser', 'Matic', 'Moped', 'Classic', 'Sport', 'Sport Racing',
            'Naked Bike', 'Touring', 'Adventure', 'Trail', 'Rally',
            'Multi Touring', 'Maxi Scooter', 'Papio', 'EV', 'ATV',
            'Sport ATV', 'TR-G', 'Utility', 'Letbe Series',
        ];

        $motorType = CategoryType::where('slug', 'motor')->first();
        $atvType = CategoryType::where('slug', 'atv')->first();

        foreach ($names as $i => $name) {
            $typeId = (in_array($name, ['ATV', 'Sport ATV']) && $atvType) ? $atvType->id : $motorType->id;
            if (!$typeId) continue;

            $cat = Category::updateOrCreate(
                ['category_type_id' => $typeId, 'slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i + 1, 'is_active' => true]
            );
            $this->categoryIds[$name] = $cat->id;
        }

        $this->command->info('✓ Categories: ' . Category::count());
    }

    private function createItemsFromDummy(): void
    {
        $motorType = CategoryType::where('slug', 'motor')->first();
        $atvType = CategoryType::where('slug', 'atv')->first();
        if (!$motorType) return;

        $lines = file(base_path('dummy.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        array_shift($lines);

        $grouped = [];
        foreach ($lines as $line) {
            $cols = explode("\t", $line);
            if (count($cols) < 6) continue;

            [$brandName, $jenis, $nama, $spek, $harga, $warna] = $cols;
            $brandId = $this->brandIds[Str::slug($brandName)] ?? null;
            $categoryId = $this->categoryIds[$jenis] ?? null;
            $price = strtolower(trim($harga)) === 'hubungi kami' ? null : (int) filter_var($harga, FILTER_SANITIZE_NUMBER_INT);
            $itemTypeId = ($atvType && in_array($jenis, ['ATV', 'Sport ATV'])) ? $atvType->id : $motorType->id;

            $key = $itemTypeId . '|' . Str::slug($nama);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category_type_id' => $itemTypeId, 'brand_id' => $brandId, 'category_id' => $categoryId,
                    'name' => trim($nama), 'slug' => Str::slug(trim($nama)),
                    'price' => $price,
                    'description' => '<p>' . trim($nama) . ' — ' . trim($spek) . '</p>',
                    'short_description' => $brandName . ' ' . trim($nama) . ' | ' . trim($spek),
                    'status' => 'active', 'is_active' => true,
                    'stock_status' => $price ? 'ready' : 'indent', 'colors' => [],
                ];
            }
            $grouped[$key]['colors'][] = trim($warna);
        }

        $counter = 0;
        foreach ($grouped as $data) {
            $colors = $data['colors'];
            unset($data['colors']);

            $data['thumbnail_path'] = $this->pic($counter + 50);
            $item = Item::create($data);

            ItemImage::create(['item_id' => $item->id, 'path' => $this->pic($counter + 50), 'sort_order' => 0]);

            foreach ($colors as $ci => $colorName) {
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

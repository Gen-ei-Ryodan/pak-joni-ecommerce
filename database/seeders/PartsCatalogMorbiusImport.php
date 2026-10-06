<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemColor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PartsCatalogMorbiusImport extends Seeder
{
    /**
     * Import data parts catalog MORBIUS (items/catalogs/parts-catalog-morbius.pdf).
     *
     * Setiap baris part menjadi Item sparepart dengan:
     *  - part_number = kode parts number dari PDF
     *  - catalog_pdf_path = 'items/catalogs/parts-catalog-morbius.pdf'
     *  - brand ikut motor MORBIUS
     *  - kategori dibuat dari grup (F/E group)
     *  - kompatibel motor morbius
     *  - stok 0
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/parts_catalog_morbius.json');
        if (! File::exists($jsonPath)) {
            $this->command?->error('File parts_catalog_morbius.json tidak ditemukan di storage/app.');
            return;
        }

        $rows = json_decode((string) File::get($jsonPath), true);
        if (! is_array($rows)) {
            $this->command?->error('Gagal decode JSON.');
            return;
        }

        $type = CategoryType::where('slug', 'sparepart')->first();
        $motor = Item::where('slug', 'morbius')->first();
        if (! $type || ! $motor) {
            $this->command?->error('Category type sparepart / motor MORBIUS tidak ditemukan.');
            return;
        }

        $created = 0;
        $skipped = 0;
        $byCode = [];

        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $group = trim((string) ($row['group'] ?? 'UNTAGGED'));

            if ($code === '' || $name === '' || isset($byCode[$code])) {
                $skipped++;
                continue;
            }
            $byCode[$code] = true;

            $category = Category::firstOrCreate(
                [
                    'category_type_id' => $type->id,
                    'slug' => Str::slug($group),
                ],
                [
                    'name' => $group,
                    'description' => $group,
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );

            $item = Item::updateOrCreate(
                [
                    'category_type_id' => $type->id,
                    'slug' => 'morbius-' . Str::slug($code),
                ],
                [
                    'name' => $name,
                    'brand_id' => $motor->brand_id,
                    'category_id' => $category->id,
                    'price' => 0,
                    'thumbnail_path' => null,
                    'short_description' => 'Sparepart MORBIUS - ' . $group,
                    'description' => $row['spec'] ?? null,
                    'status' => 'active',
                    'is_active' => true,
                    'stock_status' => 'ready',
                    'part_number' => $code,
                    'catalog_pdf_path' => 'items/catalogs/parts-catalog-morbius.pdf',
                ]
            );

            $item->compatibleMotors()->syncWithoutDetaching([$motor->id]);

            ItemColor::firstOrCreate(
                ['item_id' => $item->id, 'name' => 'Standard'],
                [
                    'color_code' => '#666666',
                    'stock' => 0,
                    'is_active' => true,
                    'sort_order' => 0,
                    'weight' => 100,
                ]
            );

            $created++;
        }

        $this->command?->info("Created/updated: {$created} sparepart items, skipped: {$skipped}.");
    }
}

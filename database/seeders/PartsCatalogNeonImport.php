<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemColor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PartsCatalogNeonImport extends Seeder
{
    /**
     * Import data parts catalog NEON (items/catalogs/parts-catalog-neon.pdf).
     *
     * Setiap baris part menjadi Item bertipe sparepart dengan:
     *  - part_number = kode part dari PDF
     *  - catalog_pdf_path = 'items/catalogs/parts-catalog-neon.pdf'
     *  - brand ikut motor NEON (letbe-series-neon)
     *  - kategori dibuat dari grup (F/E group)
     *  - kompatibel motor NEON (letbe-series-neon)
     *  - stok 0 (variant Standard / color "#666666")
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/parts_catalog_neon.json');
        if (! File::exists($jsonPath)) {
            $this->command?->error("File parts_catalog_neon.json tidak ditemukan di storage/app.");
            return;
        }

        $rows = json_decode((string) File::get($jsonPath), true);
        if (! is_array($rows)) {
            $this->command?->error('Gagal decode JSON.');
            return;
        }

        $type = CategoryType::where('slug', 'sparepart')->first();
        $motor = Item::where('slug', 'letbe-series-neon')->first();
        if (! $type || ! $motor) {
            $this->command?->error('Category type sparepart / motor NEON tidak ditemukan.');
            return;
        }

        $created = 0;
        $skippedDuplicate = 0;
        $byCode = [];

        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $group = trim((string) ($row['group'] ?? 'UNTAGGED'));

            if ($code === '' || $name === '' || isset($byCode[$code])) {
                $skippedDuplicate++;
                continue;
            }
            $byCode[$code] = true;

            // Kategori dari grup.
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
                    'slug' => 'neon-' . Str::slug($code),
                ],
                [
                    'name' => $name,
                    'brand_id' => $motor->brand_id,
                    'category_id' => $category->id,
                    'price' => 0,
                    'thumbnail_path' => null,
                    'short_description' => 'Sparepart NEON - ' . $group,
                    'description' => null,
                    'status' => 'active',
                    'is_active' => true,
                    'stock_status' => 'ready',
                    'part_number' => $code,
                    'catalog_pdf_path' => 'items/catalogs/parts-catalog-neon.pdf',
                ]
            );

            $item->compatibleMotors()->syncWithoutDetaching([$motor->id]);

            // Qty 0 untuk semua part.
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

        $this->command?->info("Created/updated: {$created} sparepart items, skipped duplicates: {$skippedDuplicate}.");
    }
}

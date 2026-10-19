<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Item;
use App\Models\ItemPartCatalog;
use App\Models\ItemPartCatalogImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PartCatalogGallerySeeder extends Seeder
{
    /**
     * Import katalog part "image gallery" per merk + model (halaman /pilih/sparepart).
     *
     * Sumber gambar: storage/app/public/items/part-catalogs/pages/{slug}/*.jpg
     * (hasil split PDF parts-catalog-{slug}.pdf menjadi gambar per halaman).
     *
     * Pola: KOSONGKAN dulu katalog + gambar milik merk yang dikelola seeder ini,
     * lalu INSERT ulang. Jadi aman dijalankan berulang tanpa data dobel.
     * Hanya menyentuh data katalog part — tidak menyentuh item/merk/order lain.
     * Kalau sebuah merk tidak punya gambar sama sekali, data lama merk itu DIBIARKAN.
     *
     * Dipanggil oleh DatabaseSeeder, jadi `php artisan db:seed` sudah cukup.
     *
     * Struktur manifest: [brand_slug => [ [slug, name, source_folder, motor_slug|null], ... ]]
     */
    private const CATALOGS = [
        'wmoto' => [
            ['neon',          'Neon',            'WMOTO/NEON',          'letbe-series-neon'],
            ['greta',         'Greta',           'WMOTO/GRETA',         'greta'],
            ['velora',        'Velora 150',      'WMOTO/VELORA',        'velora-150'],
            ['porter125',     'Porter 125',      'WMOTO/PORTER',        'porter-125'],
            ['morbius',       'Morbius',         'WMOTO/MORBIUS',       'morbius'],
            ['swiftbee',      'Swiftbee',        'WMOTO/SWIFTBEE',      'swiftbee-125cc'],
            ['letbe-island',  'Letbe Island',    'WMOTO/LETBE ISLAND',  'letbe-series-island'],
        ],
        'sm-sport' => [
            ['e-classic',     'E-Classic',       'SM SPORT/ECLASSIC',   'e-classic'],
            ['gy-aries',      'GY 150 Aries',    'SM SPORT/GY ARIES',   'gy-150-aries'],
            ['v16-plus',      'V16 Plus',        'SM SPORT/V16 PLUS',   'v16-plus'],
        ],
        'cf-moto' => [
            ['250-clc',       '250 CLC',         'CF MOTO/250 CLC',         'cfmoto-250-clc'],
            ['250-dual-lite', '250 Dual Lite',   'CF MOTO/250 DUAL LITE',   'cfmoto-250-dual-lite'],
            ['250sr',         '250SR',           'CF MOTO/250SR',           'cfmoto-250-sr-lite'],
            ['450mt',         '450 MT',          'CF MOTO/450 MT',          'cfmoto-450-mt'],
            ['500sr-voom',    '500SR Voom',      'CF MOTO/500SR VOOM',      'cfmoto-500-sr-voom'],
            ['800-mtx',       '800 MTX',         'CF MOTO/800 MTX',         'cfmoto-800-mtx'],
            ['800-mt-explore','800 MT Explore',  'CF MOTO/800 MT EXPLORE',  'cfmoto-800-mt-explore'],
            ['papio-racer',   'Papio Racer',     'CF MOTO/PAPIO RACER',     'papio-racer'],
            ['papio-xo-1',    'Papio XO 1',      'CF MOTO/PAPIO XO 1',      'papio-x0-1'],
            ['papio-xo-2',    'Papio XO 2',      'CF MOTO/PAPIO XO2',       'papio-x0-2'],
        ],
        'zeeho' => [
            ['zeeho-ae4-abs',    'AE4 ABS',      'ZEEHO/AE4',        'zeeho-ae4-abs'],
            ['zeeho-ae6',        'AE6',          'ZEEHO/AE6',        'zeeho-ae6'],
            ['zeeho-ae8',        'AE8',          'ZEEHO/AE8',        null],
            ['zeeho-city-sport', 'City Sport',   'ZEEHO/CITY SPORT', null],
        ],
        'zontes' => [
            ['368e', '368 E', 'ZONTES/368E', '368-e'],
            ['368g', '368 G', 'ZONTES/368G', '368-g'],
            ['368k', '368 K', 'ZONTES/368K', null],
        ],
    ];

    public function run(): void
    {
        $pagesRoot = storage_path('app/public/items/part-catalogs/pages');
        $pdfRoot = storage_path('app/public/items/catalogs');

        if (! File::isDirectory($pagesRoot)) {
            $this->command?->warn("Folder gambar tidak ditemukan: {$pagesRoot}. Seeder dilewati (data lama dibiarkan).");

            return;
        }

        $brands = Brand::whereIn('slug', array_keys(self::CATALOGS))->get()->keyBy('slug');

        if ($brands->isEmpty()) {
            $this->command?->warn('Tidak ada merk katalog yang cocok — seeder dilewati.');

            return;
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($brands, $pagesRoot, $pdfRoot, &$created, &$skipped) {
            foreach (self::CATALOGS as $brandSlug => $catalogs) {
                $brand = $brands->get($brandSlug);
                if (! $brand) {
                    $this->command?->warn("Brand '{$brandSlug}' tidak ditemukan — dilewati.");
                    continue;
                }

                // Kumpulkan model yang benar-benar punya gambar.
                $entries = [];
                foreach ($catalogs as $index => [$slug, $name, $folder, $motorSlug]) {
                    $dir = $pagesRoot.'/'.$slug;
                    $files = File::isDirectory($dir)
                        ? collect(File::files($dir))
                            ->filter(fn ($file) => strtolower($file->getExtension()) === 'jpg')
                            ->sortBy(fn ($file) => $file->getFilename())
                            ->values()
                        : collect();

                    if ($files->isEmpty()) {
                        $this->command?->warn("Gambar untuk '{$folder}' tidak ditemukan di {$dir} — dilewati.");
                        $skipped++;
                        continue;
                    }

                    $entries[] = [$slug, $name, $motorSlug, $files, $index];
                }

                if (empty($entries)) {
                    $this->command?->warn("Tidak ada gambar untuk brand '{$brandSlug}' — data lama dibiarkan.");
                    continue;
                }

                // 1. Kosongkan dulu katalog + gambar milik brand ini (biar tidak dobel).
                ItemPartCatalogImage::whereHas('catalog', fn ($q) => $q->where('brand_id', $brand->id))->delete();
                ItemPartCatalog::where('brand_id', $brand->id)->delete();

                // 2. Insert ulang dari manifest.
                foreach ($entries as [$slug, $name, $motorSlug, $files, $index]) {
                    // Kolom pdf_path NOT NULL — kalau file PDF tidak ada, pakai string kosong
                    // supaya tombol "Download PDF" otomatis disembunyikan (bukan error).
                    $pdfPath = File::exists($pdfRoot.'/parts-catalog-'.$slug.'.pdf')
                        ? 'items/catalogs/parts-catalog-'.$slug.'.pdf'
                        : '';

                    $itemId = $motorSlug ? Item::where('slug', $motorSlug)->value('id') : null;

                    $catalog = ItemPartCatalog::create([
                        'brand_id' => $brand->id,
                        'item_id' => $itemId,
                        'name' => $name,
                        'slug' => $slug,
                        'pdf_path' => $pdfPath,
                        'image_path' => 'items/part-catalogs/pages/'.$slug.'/'.$files->first()->getFilename(),
                        'is_active' => true,
                        'sort_order' => $index,
                    ]);

                    foreach ($files as $i => $file) {
                        $catalog->images()->create([
                            'path' => 'items/part-catalogs/pages/'.$slug.'/'.$file->getFilename(),
                            'sort_order' => $i,
                        ]);
                    }

                    $created++;
                }
            }
        });

        $this->command?->info("✓ Katalog part: {$created} model diimpor, {$skipped} dilewati.");
    }
}

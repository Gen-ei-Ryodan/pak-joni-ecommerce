<?php

use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemColor;
use App\Models\Part;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Gabungkan data Part lama (model Part) ke bentuk Item sparepart.
     *
     * - Copy semua parts (category sparepart) ke items dengan kategori, brand, thumbnail, deskripsi.
     * - Variant Part -> ItemColor (nama, stok, berat, harga default via varian default).
     * - Relasi motor kompatibel (Part::items) -> pivot item_motor_compatibility (Item::compatibleMotors).
     * - PartCategory -> Category (bertipe sparepart, bila belum ada).
     *
     * Non-destructive: data di tabel parts TIDAK dihapus.
     */
    public function up(): void
    {
        $sparepartType = CategoryType::where('slug', 'sparepart')->first();
        if (! $sparepartType) {
            return;
        }

        DB::transaction(function () use ($sparepartType) {
            foreach (Part::where('category_type_id', $sparepartType->id)
                ->with(['category', 'variants', 'defaultVariant', 'items.brand', 'items.type'])
                ->orderBy('id')
                ->get() as $part) {

                // 1. Category (sparepart) dari PartCategory yang sama.
                $categoryId = null;
                if ($part->category) {
                    $pc = $part->category;
                    $cat = Category::firstOrNew([
                        'category_type_id' => $sparepartType->id,
                        'slug' => $pc->slug,
                    ]);
                    if (! $cat->exists) {
                        $cat->name = $pc->name;
                        $cat->description = $pc->name;
                        $cat->sort_order = (int) ($pc->sort_order ?? 0);
                        $cat->is_active = true;
                        $cat->save();
                    }
                    $categoryId = $cat->id;
                }

                // 2. Brand diambil dari item motor yang compatible (semua harus 1 brand yang sama; ambil pertama).
                $brand = $part->items->first()?->brand;

                // 3. Cari/buat Item sparepart baru.
                $item = Item::firstOrNew([
                    'category_type_id' => $sparepartType->id,
                    'slug' => $part->slug,
                ]);

                if (! $item->exists) {
                    $item->fill([
                        'name' => $part->name,
                        'brand_id' => $brand?->id,
                        'category_id' => $categoryId,
                        'price' => $part->defaultVariant?->price ?? $part->base_price ?? 0,
                        'thumbnail_path' => $part->thumbnail_path,
                        'short_description' => $part->short_description,
                        'description' => $part->description,
                        'status' => $part->status ?? 'active',
                        'is_active' => ($part->status ?? 'active') === 'active',
                        'stock_status' => $part->stock_status ?? 'ready',
                        'sort_order' => 0,
                    ]);
                    $item->save();
                } else {
                    // Update info jika kosong di Item yang sudah ada.
                    $item->fill([
                        'price' => $item->price ?: ($part->defaultVariant?->price ?? $part->base_price ?? 0),
                        'thumbnail_path' => $item->thumbnail_path ?: $part->thumbnail_path,
                        'stock_status' => $item->stock_status ?: ($part->stock_status ?? 'ready'),
                    ])->save();
                }

                // 4. Relasi motor kompatibel (item motor dari pivot item_part lama).
                $motorIds = $part->items
                    ->filter(fn ($i) => $i->type && in_array($i->type->slug, ['motor', 'mobil', 'atv'], true))
                    ->pluck('id');
                if ($motorIds->isNotEmpty()) {
                    $item->compatibleMotors()->syncWithoutDetaching($motorIds);
                }

                // 5. Variant part -> item colors (stok).
                foreach ($part->variants as $variant) {
                    ItemColor::firstOrCreate(
                        ['item_id' => $item->id, 'name' => $variant->name],
                        [
                            'color_code' => '#666666',
                            'stock' => (int) $variant->stock,
                            'is_active' => true,
                            'sort_order' => 0,
                            'weight' => (int) ($variant->weight ?? 100),
                        ]
                    );
                }
            }
        });
    }

    public function down(): void
    {
        // Data copy migration: biarkan data yang sudah disalin. Penggabungan part tidak menghapus rows.
    }
};

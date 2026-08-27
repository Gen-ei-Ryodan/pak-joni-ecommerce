<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\Part;
use App\Models\PartCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sparepartType = CategoryType::where('slug', 'sparepart')->first();
        if (!$sparepartType) {
            return;
        }

        DB::transaction(function () use ($sparepartType) {
            // Clean any existing sparepart items/categories/colors first
            $itemIds = Item::where('category_type_id', $sparepartType->id)->pluck('id');
            \App\Models\ItemColor::whereIn('item_id', $itemIds)->delete();
            Item::where('category_type_id', $sparepartType->id)->delete();
            Category::where('category_type_id', $sparepartType->id)->delete();

            // 1. Copy PartCategory -> Category
            $partCategories = PartCategory::where('category_type_id', $sparepartType->id)
                ->orderBy('sort_order')
                ->get();

            $categoryMap = []; // part_category_id -> category_id

            foreach ($partCategories as $pc) {
                $category = Category::create([
                    'category_type_id' => $sparepartType->id,
                    'name' => $pc->name,
                    'slug' => $pc->slug,
                    'description' => $pc->name,
                    'sort_order' => $pc->sort_order,
                    'is_active' => true,
                ]);
                $categoryMap[$pc->id] = $category->id;
            }

            // 2. Copy Part -> Item
            $parts = Part::where('category_type_id', $sparepartType->id)
                ->where('status', 'active')
                ->with(['category', 'defaultVariant', 'items.brand'])
                ->get();

            foreach ($parts as $part) {
                $brand = $part->items->first()?->brand;
                if (!$brand) continue;

                $categoryId = $part->part_category_id ? ($categoryMap[$part->part_category_id] ?? null) : null;

                $item = Item::create([
                    'category_type_id' => $sparepartType->id,
                    'brand_id' => $brand->id,
                    'category_id' => $categoryId,
                    'name' => $part->name,
                    'slug' => $part->slug,
                    'short_description' => $part->short_description,
                    'description' => $part->description,
                    'thumbnail_path' => $part->thumbnail_path,
                    'price' => $part->defaultVariant?->price ?? $part->base_price ?? 0,
                    'status' => 'active',
                    'is_active' => true,
                    'stock_status' => $part->stock_status,
                    'sort_order' => 0,
                    'year' => null,
                ]);

                // Copy variants as colors (using color as variant name)
                $variants = $part->variants;
                if ($variants->count()) {
                    foreach ($variants as $variant) {
                        \App\Models\ItemColor::create([
                            'item_id' => $item->id,
                            'name' => $variant->name,
                            'color_code' => '#666666',
                            'stock' => $variant->stock,
                            'is_active' => true,
                            'sort_order' => 0,
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        $sparepartType = CategoryType::where('slug', 'sparepart')->first();
        if (!$sparepartType) return;

        DB::transaction(function () use ($sparepartType) {
            $itemIds = Item::where('category_type_id', $sparepartType->id)->pluck('id');
            \App\Models\ItemColor::whereIn('item_id', $itemIds)->delete();
            Item::where('category_type_id', $sparepartType->id)->delete();
            Category::where('category_type_id', $sparepartType->id)->delete();
        });
    }
};
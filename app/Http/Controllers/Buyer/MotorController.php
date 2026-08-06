<?php

namespace App\Http\Controllers\Buyer;

use App\Models\CategoryType;
use App\Models\Item;
use Illuminate\Http\Request;

class MotorController
{
    public function index(Request $request)
    {
        $type = CategoryType::where('slug', 'motor')->where('is_active', true)->first();

        if (!$type) {
            abort(404, 'Category type not found');
        }

        $query = Item::with(['brand', 'category', 'colors'])
            ->where('category_type_id', $type->id)
            ->where('status', 'active')
            ->where('is_active', true);

        $q = $request->q;
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                  ->orWhere('short_description', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('sort_order')->orderBy('name')->paginate(12);

        return view('buyer.motors.index', compact('items', 'q'));
    }

    public function show($categoryType, $slug, Request $request)
    {
        $item = Item::with([
            'brand', 'category', 'type', 'images', 'colors',
            'specifications', 'images360', 'parts' => function ($q) {
                $q->where('status', 'active');
            }, 'parts.category', 'priceLists', 'partCatalogs',
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->where('is_active', true)
            ->firstOrFail();

        // Redirect if URL category type doesn't match item's actual type
        if ($item->type->slug !== $categoryType) {
            return redirect()->route('buyer.motors.show', [
                'categoryType' => $item->type->slug,
                'slug' => $item->slug,
            ]);
        }

        $tab = $request->tab === 'parts' ? 'parts' : 'detail';

        // Filter parts by group
        $partGroup = $request->part_group;
        $partsQuery = $item->parts();

        $groups = $item->parts()
            ->with('category')
            ->get()
            ->groupBy(fn ($p) => $p->category->group ?? 'Lainnya')
            ->map->count();

        if ($partGroup && $partGroup !== 'all') {
            $partsQuery->whereHas('category', fn ($q) => $q->where('group', $partGroup));
        }

        $parts = $partsQuery->with(['category', 'defaultVariant'])->paginate(12);

        // Related items
        $relatedItems = Item::with(['brand', 'type', 'colors'])
            ->where('category_type_id', $item->category_type_id)
            ->where('id', '!=', $item->id)
            ->where('status', 'active')
            ->where('is_active', true)
            ->where(function ($q) use ($item) {
                $q->where('brand_id', $item->brand_id)
                  ->orWhere('category_id', $item->category_id);
            })
            ->limit(4)
            ->get();

        $specsGrouped = $item->specifications->groupBy('group');

        $partsGrouped = $groups;
        $selectedPartGroup = $partGroup;
        $partGroups = $groups->keys();

        return view('buyer.motors.show', compact('item', 'tab', 'parts', 'partsGrouped', 'selectedPartGroup', 'partGroups', 'relatedItems', 'specsGrouped'));
    }
}

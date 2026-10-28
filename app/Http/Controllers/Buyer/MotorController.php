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

        if ($item->type->slug === 'sparepart') {
            $item->load(['compatibleMotors.brand', 'compatibleMotors.type']);
        }

        $tab = $request->tab === 'parts' && $item->type->slug !== 'sparepart' ? 'parts' : 'detail';

        $partSearch = trim((string) ($request->q ?? $request->search ?? ''));

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

        // Sparepart yang kompatibel dengan motor ini (untuk tab sparepart).
        $compatibleSpareparts = $item->type->slug === 'sparepart'
            ? collect()
            : $item->compatibleSpareparts()
                ->where('status', 'active')
                ->where('is_active', true)
                ->when($partSearch !== '', function ($query) use ($partSearch) {
                    $query->where(function ($q2) use ($partSearch) {
                        $q2->where('name', 'like', "%{$partSearch}%")
                           ->orWhere('part_number', 'like', "%{$partSearch}%")
                           ->orWhere('short_description', 'like', "%{$partSearch}%");
                    });
                })
                ->with(['brand', 'category', 'colors'])
                ->get();

        // AJAX request untuk tab parts: kembalikan partial tabel saja.
        if ($request->ajax() && $tab === 'parts') {
            return response()->json([
                'html' => view('buyer.motors.partials.parts-table', compact('item', 'compatibleSpareparts'))->render(),
                'count' => $compatibleSpareparts->count(),
            ]);
        }

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

        return view('buyer.motors.show', array_merge(compact('item', 'tab', 'parts', 'partsGrouped', 'selectedPartGroup', 'partGroups', 'relatedItems', 'specsGrouped', 'compatibleSpareparts'), ['q' => $partSearch, 'partSearch' => $partSearch]));
    }
}

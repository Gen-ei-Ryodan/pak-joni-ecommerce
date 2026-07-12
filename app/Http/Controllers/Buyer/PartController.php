<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Part;
use App\Models\PartCategory;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $category = $request->query('category');
        $brand = $request->query('brand');
        $group = $request->query('group');

        $parts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', '%'.$q.'%')
                        ->orWhere('sku', 'like', '%'.$q.'%')
                        ->orWhere('short_description', 'like', '%'.$q.'%');
                });
            })
            ->when($category, fn ($query) => $query->whereHas('category', fn ($q2) => $q2->where('slug', $category)))
            ->when($group, fn ($query) => $query->whereHas('category', fn ($q2) => $q2->where('group', $group)))
            ->when($brand, fn ($query) => $query->whereHas('items.brand', fn ($q2) => $q2->where('slug', $brand)))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = PartCategory::query()->orderBy('group')->orderBy('sort_order')->orderBy('name')->get();
        $brands = Brand::whereHas('items.parts')->where('is_active', true)->orderBy('sort_order')->get();
        $groups = PartCategory::query()->select('group')->distinct()->orderBy('group')->pluck('group');

        $selectedCategoryName = '';
        if ($category) {
            $cat = PartCategory::where('slug', $category)->first();
            $selectedCategoryName = $cat ? ($cat->group.' — '.$cat->name) : '';
        }

        return view('buyer.parts.index', compact('parts', 'categories', 'brands', 'groups', 'q', 'category', 'brand', 'group', 'selectedCategoryName'));
    }

    public function show(Part $part)
    {
        $part->load(['images', 'variants', 'category', 'specifications', 'items.brand', 'items.type']);

        $specGroups = $part->specifications->groupBy('group');

        $allCompatibles = $part->allCompatibles();

        $relatedParts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->where('id', '!=', $part->id)
            ->where('part_category_id', $part->part_category_id)
            ->take(4)
            ->get();

        return view('buyer.parts.show', compact('part', 'specGroups', 'relatedParts', 'allCompatibles'));
    }
}


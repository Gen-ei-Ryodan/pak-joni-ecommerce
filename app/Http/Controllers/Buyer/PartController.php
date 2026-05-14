<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\PartCategory;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $category = $request->query('category');

        $parts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', '%'.$q.'%')
                        ->orWhere('sku', 'like', '%'.$q.'%');
                });
            })
            ->when($category, fn ($query) => $query->whereHas('category', fn ($q2) => $q2->where('slug', $category)))
            ->orderByDesc('id')
            ->paginate(4)
            ->withQueryString();

        $categories = PartCategory::query()->orderBy('group')->orderBy('sort_order')->orderBy('name')->get();

        $selectedCategoryName = '';
        if ($category) {
            $cat = PartCategory::where('slug', $category)->first();
            $selectedCategoryName = $cat ? ($cat->group.' — '.$cat->name) : '';
        }

        return view('buyer.parts.index', compact('parts', 'categories', 'q', 'category', 'selectedCategoryName'));
    }

    public function show(Part $part)
    {
        $part->load(['images', 'variants', 'category', 'motors']);

        return view('buyer.parts.show', compact('part'));
    }
}


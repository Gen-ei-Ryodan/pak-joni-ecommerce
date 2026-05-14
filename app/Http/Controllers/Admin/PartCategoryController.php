<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PartCategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $categories = PartCategory::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.part-categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('admin.part-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => ['required', 'in:part,refitting,wearing'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:part_categories,slug'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        PartCategory::create([
            'group' => $validated['group'],
            'name' => $validated['name'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.part-categories.index')->with('status', 'Category berhasil dibuat.');
    }

    public function edit(PartCategory $partCategory)
    {
        return view('admin.part-categories.edit', ['category' => $partCategory]);
    }

    public function update(Request $request, PartCategory $partCategory)
    {
        $validated = $request->validate([
            'group' => ['required', 'in:part,refitting,wearing'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:part_categories,slug,'.$partCategory->id],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $partCategory->fill([
            'group' => $validated['group'],
            'name' => $validated['name'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
        ])->save();

        return redirect()->route('admin.part-categories.edit', $partCategory)->with('status', 'Category berhasil diupdate.');
    }

    public function destroy(PartCategory $partCategory)
    {
        $partCategory->delete();

        return redirect()->route('admin.part-categories.index')->with('status', 'Category berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Motor;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartImage;
use App\Models\PartVariant;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');

        $parts = Part::query()
            ->with(['category'])
            ->withSum('variants', 'stock')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', '%'.$q.'%')
                        ->orWhere('sku', 'like', '%'.$q.'%');
                });
            })
            ->when($categoryId, fn ($query) => $query->where('part_category_id', $categoryId))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $categories = PartCategory::query()->orderBy('group')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.parts.index', compact('parts', 'categories', 'q', 'categoryId'));
    }

    public function create()
    {
        $categories = PartCategory::query()->orderBy('group')->orderBy('sort_order')->orderBy('name')->get();
        $motors = Motor::query()->orderBy('name')->get();

        return view('admin.parts.create', compact('categories', 'motors'));
    }

    public function store(Request $request, ImageService $image)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:parts,sku'],
            'name' => ['required', 'string', 'max:255'],
            'part_category_id' => ['required', 'exists:part_categories,id'],
            'status' => ['required', 'in:active,inactive'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'specification' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'motor_ids' => ['nullable', 'array'],
            'motor_ids.*' => ['integer', 'exists:motors,id'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.sku' => ['required', 'string', 'max:64', 'distinct', 'unique:part_variants,sku'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        $variants = $this->normalizeVariants(collect($validated['variants']));

        return DB::transaction(function () use ($request, $image, $validated, $slug, $variants) {
            $part = Part::create([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'slug' => $slug,
                'part_category_id' => (int) $validated['part_category_id'],
                'status' => $validated['status'],
                'base_price' => $validated['base_price'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'specification' => $validated['specification'] ?? null,
                'thumbnail_path' => null,
            ]);

            if ($request->hasFile('thumbnail')) {
                $part->thumbnail_path = $image->storeAsWebp($request->file('thumbnail'), 'parts/thumbnails');
                $part->save();
            }

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $idx => $file) {
                    $path = $image->storeAsWebp($file, 'parts/gallery');
                    PartImage::create([
                        'part_id' => $part->id,
                        'path' => $path,
                        'sort_order' => $idx,
                    ]);
                }
            }

            foreach ($variants as $v) {
                PartVariant::create([
                    'part_id' => $part->id,
                    'sku' => $v['sku'],
                    'name' => $v['name'],
                    'price' => $v['price'],
                    'stock' => $v['stock'],
                    'is_default' => (bool) $v['is_default'],
                ]);
            }

            $part->motors()->sync($validated['motor_ids'] ?? []);

            return redirect()->route('admin.parts.index')->with('status', 'Part berhasil dibuat.');
        });
    }

    public function edit(Part $part)
    {
        $part->load(['images', 'variants', 'motors', 'category']);

        $categories = PartCategory::query()->orderBy('group')->orderBy('sort_order')->orderBy('name')->get();
        $motors = Motor::query()->orderBy('name')->get();

        return view('admin.parts.edit', compact('part', 'categories', 'motors'));
    }

    public function update(Request $request, Part $part, ImageService $image)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:parts,sku,'.$part->id],
            'name' => ['required', 'string', 'max:255'],
            'part_category_id' => ['required', 'exists:part_categories,id'],
            'status' => ['required', 'in:active,inactive'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'specification' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],
            'motor_ids' => ['nullable', 'array'],
            'motor_ids.*' => ['integer', 'exists:motors,id'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:64', 'distinct'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        $variants = $this->normalizeVariants(collect($validated['variants']));

        return DB::transaction(function () use ($request, $part, $image, $validated, $slug, $variants) {
            $part->fill([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'slug' => $slug,
                'part_category_id' => (int) $validated['part_category_id'],
                'status' => $validated['status'],
                'base_price' => $validated['base_price'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'specification' => $validated['specification'] ?? null,
            ])->save();

            if ($request->hasFile('thumbnail')) {
                $this->deletePublicPath($part->thumbnail_path);
                $part->thumbnail_path = $image->storeAsWebp($request->file('thumbnail'), 'parts/thumbnails');
                $part->save();
            }

            $deleteIds = collect($validated['delete_images'] ?? [])->map(fn ($v) => (int) $v)->values();
            if ($deleteIds->isNotEmpty()) {
                $images = $part->images()->whereIn('id', $deleteIds)->get();
                foreach ($images as $img) {
                    $this->deletePublicPath($img->path);
                    $img->delete();
                }
            }

            if ($request->hasFile('gallery')) {
                $start = (int) ($part->images()->max('sort_order') ?? 0);
                foreach ($request->file('gallery') as $i => $file) {
                    $path = $image->storeAsWebp($file, 'parts/gallery');
                    PartImage::create([
                        'part_id' => $part->id,
                        'path' => $path,
                        'sort_order' => $start + $i + 1,
                    ]);
                }
            }

            $existing = $part->variants()->get()->keyBy('id');
            $keepIds = [];

            foreach ($variants as $v) {
                $variantId = $v['id'] ? (int) $v['id'] : null;

                $variant = $variantId && $existing->has($variantId)
                    ? $existing->get($variantId)
                    : new PartVariant(['part_id' => $part->id]);

                $skuRuleIgnore = $variant->exists ? ','.$variant->id : '';
                $request->validate([
                    'variants.*.sku' => ['unique:part_variants,sku'.$skuRuleIgnore],
                ]);

                $variant->fill([
                    'sku' => $v['sku'],
                    'name' => $v['name'],
                    'price' => $v['price'],
                    'stock' => $v['stock'],
                    'is_default' => (bool) $v['is_default'],
                ])->save();

                $keepIds[] = $variant->id;
            }

            $part->variants()->whereNotIn('id', $keepIds)->delete();

            $part->motors()->sync($validated['motor_ids'] ?? []);

            return redirect()->route('admin.parts.edit', $part)->with('status', 'Part berhasil diupdate.');
        });
    }

    public function destroy(Part $part)
    {
        $part->load(['images']);

        $this->deletePublicPath($part->thumbnail_path);
        foreach ($part->images as $img) {
            $this->deletePublicPath($img->path);
        }

        $part->delete();

        return redirect()->route('admin.parts.index')->with('status', 'Part berhasil dihapus.');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $variants
     * @return array<int, array{id:?int,sku:string,name:string,price:string,stock:int,is_default:bool}>
     */
    private function normalizeVariants(Collection $variants): array
    {
        $normalized = $variants
            ->map(function (array $v) {
                return [
                    'id' => isset($v['id']) && $v['id'] !== '' ? (int) $v['id'] : null,
                    'sku' => trim((string) $v['sku']),
                    'name' => trim((string) $v['name']),
                    'price' => (string) $v['price'],
                    'stock' => (int) $v['stock'],
                    'is_default' => (bool) ($v['is_default'] ?? false),
                ];
            })
            ->values()
            ->all();

        $hasDefault = collect($normalized)->contains(fn ($v) => (bool) $v['is_default']);
        if (! $hasDefault && count($normalized) > 0) {
            $normalized[0]['is_default'] = true;
        }

        $normalized = collect($normalized)->map(function (array $v) use ($hasDefault) {
            return $v;
        })->all();

        if (collect($normalized)->where('is_default', true)->count() > 1) {
            $first = true;
            $normalized = array_map(function (array $v) use (&$first) {
                if ($v['is_default'] && $first) {
                    $first = false;
                    return $v;
                }
                $v['is_default'] = false;
                return $v;
            }, $normalized);
        }

        return $normalized;
    }

    private function deletePublicPath(?string $publicPath): void
    {
        if (! $publicPath) {
            return;
        }

        $path = Str::replaceStart('storage/', '', $publicPath);
        Storage::disk('public')->delete($path);
    }
}

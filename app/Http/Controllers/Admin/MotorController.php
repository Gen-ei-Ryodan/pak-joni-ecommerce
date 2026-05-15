<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Motor;
use App\Models\MotorImage;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MotorController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $motors = Motor::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.motors.index', compact('motors', 'q'));
    }

    public function create()
    {
        return view('admin.motors.create');
    }

    public function store(Request $request, ImageService $image)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'status' => ['required', 'in:published,draft'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        return DB::transaction(function () use ($request, $image, $validated) {
            $motor = Motor::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'year' => $validated['year'] ?? null,
                'status' => $validated['status'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'thumbnail_path' => null,
            ]);

            if ($request->hasFile('thumbnail')) {
                $motor->thumbnail_path = $image->storeAsWebp($request->file('thumbnail'), 'motors/thumbnails');
                $motor->save();
            }

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $idx => $file) {
                    $path = $image->storeAsWebp($file, 'motors/gallery');
                    MotorImage::create([
                        'motor_id' => $motor->id,
                        'path' => $path,
                        'sort_order' => $idx,
                    ]);
                }
            }

            return redirect()->route('admin.motors.index')->with('status', 'Motor berhasil dibuat.');
        });
    }

    public function edit(Motor $motor)
    {
        $motor->load('images');

        return view('admin.motors.edit', compact('motor'));
    }

    public function update(Request $request, Motor $motor, ImageService $image)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'status' => ['required', 'in:published,draft'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],
        ]);
        $slug = Str::slug($validated['name']);

        return DB::transaction(function () use ($request, $motor, $image, $validated, $slug) {
            $motor->fill([
                'name' => $validated['name'],
                'slug' => $slug,
                'year' => $validated['year'] ?? null,
                'status' => $validated['status'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
            ])->save();

            if ($request->hasFile('thumbnail')) {
                $this->deletePublicPath($motor->thumbnail_path);
                $motor->thumbnail_path = $image->storeAsWebp($request->file('thumbnail'), 'motors/thumbnails');
                $motor->save();
            }

            $deleteIds = collect($validated['delete_images'] ?? [])->map(fn ($v) => (int) $v)->values();
            if ($deleteIds->isNotEmpty()) {
                $images = $motor->images()->whereIn('id', $deleteIds)->get();
                foreach ($images as $img) {
                    $this->deletePublicPath($img->path);
                    $img->delete();
                }
            }

            if ($request->hasFile('gallery')) {
                $start = (int) ($motor->images()->max('sort_order') ?? 0);
                foreach ($request->file('gallery') as $i => $file) {
                    $path = $image->storeAsWebp($file, 'motors/gallery');
                    MotorImage::create([
                        'motor_id' => $motor->id,
                        'path' => $path,
                        'sort_order' => $start + $i + 1,
                    ]);
                }
            }

            return redirect()->route('admin.motors.edit', $motor)->with('status', 'Motor berhasil diupdate.');
        });
    }

    public function destroy(Motor $motor)
    {
        $motor->load('images');

        $this->deletePublicPath($motor->thumbnail_path);
        foreach ($motor->images as $img) {
            $this->deletePublicPath($img->path);
        }

        $motor->delete();

        return redirect()->route('admin.motors.index')->with('status', 'Motor berhasil dihapus.');
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

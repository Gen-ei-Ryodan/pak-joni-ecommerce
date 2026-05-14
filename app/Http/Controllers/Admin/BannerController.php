<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::query()->orderBy('sort_order')->orderByDesc('id')->paginate(20);

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request, ImageService $image)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $image->storeAsWebp($request->file('image'), 'banners');

        Banner::create([
            'title' => $validated['title'],
            'link_url' => $validated['link_url'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'image_path' => $path,
        ]);

        return redirect()->route('admin.banners.index')->with('status', 'Banner berhasil dibuat.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner, ImageService $image)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $banner->fill([
            'title' => $validated['title'],
            'link_url' => $validated['link_url'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        if ($request->hasFile('image')) {
            $this->deletePublicPath($banner->image_path);
            $banner->image_path = $image->storeAsWebp($request->file('image'), 'banners');
        }

        $banner->save();

        return redirect()->route('admin.banners.edit', $banner)->with('status', 'Banner berhasil diupdate.');
    }

    public function destroy(Banner $banner)
    {
        $this->deletePublicPath($banner->image_path);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner berhasil dihapus.');
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

<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class MforceApiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.mforce.base_url', 'https://mforce.co.id/api');
    }

    public function getBrands(): array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500, throw: false)->get("{$this->baseUrl}/brands");

            if ($response->successful()) {
                return $response->json('data', []);
            }

            Log::warning('MForce brands error', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        } catch (ConnectionException $e) {
            Log::error('MForce connection error: ' . $e->getMessage());
            return [];
        }
    }

    public function getBrandBySlug(string $slug): ?array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500, throw: false)->get("{$this->baseUrl}/brands/slug/{$slug}");

            if ($response->successful()) {
                $data = $response->json('data');
                return is_array($data) ? $data : null;
            }

            Log::warning('MForce brand detail error', ['slug' => $slug, 'status' => $response->status()]);
            return null;
        } catch (ConnectionException $e) {
            Log::error('MForce connection error: ' . $e->getMessage());
            return null;
        }
    }

    public function getBrandProducts(string $brandSlug, array $params = []): array
    {
        try {
            $query = array_filter([
                'per_page' => $params['per_page'] ?? 12,
                'page' => $params['page'] ?? 1,
                'category' => $params['category'] ?? null,
                'series' => $params['series'] ?? null,
                'vehicle_class' => $params['vehicle_class'] ?? null,
                'sort' => $params['sort'] ?? null,
            ]);

            $response = Http::timeout(15)
                ->retry(2, 500, throw: false)
                ->get("{$this->baseUrl}/brands/{$brandSlug}/products", $query);

            if ($response->successful()) {
                return $response->json('data', []);
            }

            Log::warning('MForce products error', ['brand' => $brandSlug, 'status' => $response->status()]);
            return ['items' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1];
        } catch (ConnectionException $e) {
            Log::error('MForce connection error: ' . $e->getMessage());
            return ['items' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1];
        }
    }

    public function getAllProducts(array $params = []): LengthAwarePaginator
    {
        $brands = $this->getBrands();
        $perPage = $params['per_page'] ?? 12;
        $pageName = $params['pageName'] ?? 'page';
        $page = $params['page'] ?? request()->input($pageName, 1);
        $allItems = [];
        $brandMap = [];

        foreach ($brands as $brand) {
            $brandMap[$brand['slug']] = $brand;
            $result = $this->getBrandProducts($brand['slug'], ['per_page' => 50]);
            $items = $result['items'] ?? [];

            foreach ($items as &$item) {
                $item['_brand'] = $brand;
            }
            unset($item);

            $allItems = array_merge($allItems, $items);
        }

        if (isset($params['search'])) {
            $q = strtolower($params['search']);
            $allItems = array_filter($allItems, fn($i) => str_contains(strtolower($i['name'] ?? ''), $q));
            $allItems = array_values($allItems);
        }

        if (isset($params['brand'])) {
            $allItems = array_filter($allItems, fn($i) => ($i['_brand']['slug'] ?? '') === $params['brand']);
            $allItems = array_values($allItems);
        }

        if (isset($params['category'])) {
            $allItems = array_filter($allItems, fn($i) => strtolower($i['category'] ?? '') === strtolower($params['category']));
            $allItems = array_values($allItems);
        }

        $total = count($allItems);
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($allItems, $offset, $perPage);

        $mapped = array_map(fn($item) => $this->toItemObject($item, $item['_brand'] ?? null), $sliced);

        return new LengthAwarePaginator(
            $mapped,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query(), 'pageName' => $pageName]
        );
    }

    public function getProductsByBrand(string $brandSlug, array $params = []): LengthAwarePaginator
    {
        $brand = $this->getBrandBySlug($brandSlug);
        $result = $this->getBrandProducts($brandSlug, $params);
        $items = $result['items'] ?? [];
        $total = $result['total'] ?? count($items);
        $currentPage = $result['current_page'] ?? ($params['page'] ?? 1);
        $perPage = $params['per_page'] ?? 12;

        $mapped = array_map(fn($item) => $this->toItemObject($item, $brand), $items);

        return new LengthAwarePaginator(
            $mapped,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function getMotorDetail(int $id): ?array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500, throw: false)->get("{$this->baseUrl}/motors/{$id}");

            if ($response->successful()) {
                $data = $response->json('data');
                return is_array($data) ? $data : null;
            }

            Log::warning('MForce motor detail error', ['id' => $id, 'status' => $response->status()]);
            return null;
        } catch (ConnectionException $e) {
            Log::error('MForce connection error: ' . $e->getMessage());
            return null;
        }
    }

    public function findProductBySlug(string $slug): ?stdClass
    {
        $brands = $this->getBrands();

        foreach ($brands as $brand) {
            $result = $this->getBrandProducts($brand['slug'], ['per_page' => 50]);
            $items = $result['items'] ?? [];

            foreach ($items as $item) {
                if (($item['slug'] ?? '') === $slug) {
                    $detail = $this->getMotorDetail($item['id']);
                    return $this->toItemObject($item, $brand, $detail);
                }
            }
        }

        return null;
    }

    public function findProductById(int $id): ?stdClass
    {
        $brands = $this->getBrands();

        foreach ($brands as $brand) {
            $result = $this->getBrandProducts($brand['slug'], ['per_page' => 50]);
            $items = $result['items'] ?? [];

            foreach ($items as $item) {
                if (($item['id'] ?? 0) === $id) {
                    return $this->toItemObject($item, $brand);
                }
            }
        }

        return null;
    }

    public function getRelatedProducts(?string $category = null, ?string $brandSlug = null, int $excludeId = 0, int $limit = 4): Collection
    {
        $items = collect();

        if ($brandSlug) {
            $result = $this->getBrandProducts($brandSlug, ['per_page' => $limit + 1]);
            $apiItems = $result['items'] ?? [];

            foreach ($apiItems as $apiItem) {
                if (($apiItem['id'] ?? 0) !== $excludeId) {
                    $brand = $this->getBrandBySlug($brandSlug);
                    $items->push($this->toItemObject($apiItem, $brand));
                }
                if ($items->count() >= $limit) break;
            }
        }

        if ($items->count() < $limit && $category) {
            $brands = $this->getBrands();
            foreach ($brands as $brand) {
                $result = $this->getBrandProducts($brand['slug'], ['per_page' => 10, 'category' => $category]);
                $apiItems = $result['items'] ?? [];

                foreach ($apiItems as $apiItem) {
                    if (($apiItem['id'] ?? 0) !== $excludeId && !$items->contains(fn($i) => $i->slug === ($apiItem['slug'] ?? ''))) {
                        $items->push($this->toItemObject($apiItem, $brand));
                    }
                    if ($items->count() >= $limit) break 2;
                }
            }
        }

        return $items;
    }

    private function toItemObject(array $product, ?array $brandData = null, ?array $detail = null): stdClass
    {
        $item = new stdClass();
        $item->id = $product['id'] ?? 0;
        $item->slug = $product['slug'] ?? '';
        $item->name = $product['name'] ?? '';
        $item->price = $product['price'] ?? 0;
        $item->discount = $product['discount'] ?? null;
        $item->otr = $product['otr'] ?? null;
        $item->thumbnail_path = $product['cover_url'] ?? $product['cover_path'] ?? null;
        $item->short_description = $product['short_description'] ?? ($detail['short_description'] ?? null);
        $item->description = $detail['long_description'] ?? $product['description'] ?? null;
        $item->video_url = $detail['video_url'] ?? null;
        $item->stock_status = 'ready';
        $item->category_name = $product['category'] ?? '';
        $item->series = $product['series'] ?? '';
        $item->sort_order = $product['sort_number'] ?? 0;

        $brand = new stdClass();
        if ($brandData) {
            $brand->id = $brandData['id'] ?? 0;
            $brand->name = $brandData['name'] ?? '';
            $brand->slug = $brandData['slug'] ?? '';
            $brand->logo_path = $brandData['logo_url'] ?? null;
        } else {
            $brand->name = $detail['brand_name'] ?? '';
            $brand->slug = $detail['brand_slug'] ?? '';
            $brand->logo_path = null;
        }
        $item->brand = $brand;

        $type = new stdClass();
        $type->id = 1;
        $type->slug = 'motor';
        $type->name = 'Motor';
        $item->type = $type;

        $detailVariants = $detail['variants'] ?? null;
        $item->colors = $this->mapVariants(
            $detailVariants ?: ($product['variants'] ?? []),
            isDetail: $detailVariants !== null
        );

        $item->specifications = $this->mapSpecs($product['specs'] ?? ($detail['specs'] ?? []));

        $item->images = $this->mapGallery($detail);
        if ($item->images->isEmpty() && $item->thumbnail_path) {
            $img = new stdClass();
            $img->id = 0;
            $img->path = $item->thumbnail_path;
            $img->sort_order = 0;
            $item->images = collect([$img]);
        }

        $item->images360 = $this->mapViewer360($detailVariants ?: ($product['variants'] ?? []));

        $item->parts = collect();
        $item->priceLists = collect();
        $item->partCatalogs = collect();

        return $item;
    }

    private function mapVariants(array $variants, bool $isDetail = false): Collection
    {
        return collect(array_map(function ($v) use ($isDetail) {
            $color = new stdClass();
            $color->id = $v['id'] ?? 0;
            $color->name = $v['name'] ?? '';
            $color->color_code = $v['color'] ?? null;
            $color->image_path = $isDetail
                ? ($v['image_path'] ?? null)
                : (isset($v['image_path']) ? "https://mforce.co.id/storage/{$v['image_path']}" : null);
            $color->sort_number = $v['sort_number'] ?? 0;
            $color->price = 0;
            $color->weight = 0;
            return $color;
        }, $variants));
    }

    private function mapGallery(?array $detail): Collection
    {
        $images = collect();

        if ($detail && isset($detail['gallery']) && is_array($detail['gallery'])) {
            foreach ($detail['gallery'] as $g) {
                $img = new stdClass();
                $img->id = $g['id'] ?? 0;
                $img->path = $g['url'] ?? '';
                $img->sort_order = $g['sort_number'] ?? 0;
                $images->push($img);
            }
        }

        return $images;
    }

    private function mapViewer360(array $variants): Collection
    {
        $images360 = collect();

        foreach ($variants as $v) {
            if (isset($v['viewer360']) && is_array($v['viewer360'])) {
                foreach ($v['viewer360'] as $v360) {
                    $img = new stdClass();
                    $img->id = $v360['id'] ?? 0;
                    $img->path = $v360['url'] ?? '';
                    $img->sort_order = $images360->count();
                    $images360->push($img);
                }
                if ($images360->isNotEmpty()) break;
            }
        }

        return $images360;
    }

    private function mapSpecs(array $specs): Collection
    {
        $result = collect();
        $groupMap = [
            'engine' => 'Engine',
            'chassis' => 'Chassis',
            'dimensions' => 'Dimension',
        ];

        foreach ($specs as $groupKey => $groupSpecs) {
            $groupName = $groupMap[$groupKey] ?? ucfirst($groupKey);

            if (is_array($groupSpecs)) {
                foreach ($groupSpecs as $key => $value) {
                    if ($value === null || $value === '' || $key === 'colors') continue;

                    $labels = [
                        'engine_type' => 'Engine Type',
                        'power' => 'Power',
                        'torque' => 'Torque',
                        'capacity' => 'Capacity',
                        'bore_stroke' => 'Bore Stroke',
                        'compression' => 'Compression',
                        'clutch' => 'Clutch',
                        'lxwxh' => 'Panjang x Lebar x Tinggi',
                        'weight' => 'Weight',
                        'wheelbase' => 'Wheelbase',
                        'fuel_capacity' => 'Fuel Capacity',
                        'ground_clearance' => 'Ground Clearance',
                        'front_susp' => 'Front Suspension',
                        'rear_susp' => 'Rear Suspension',
                        'front_brake' => 'Front Brake',
                        'rear_brake' => 'Rear Brake',
                        'front_tire' => 'Front Tire',
                        'rear_tire' => 'Rear Tire',
                        'abs_cbs' => 'ABS/CBS',
                        'tcs' => 'TCS',
                        'wheel' => 'Wheel',
                    ];

                    $spec = new stdClass();
                    $spec->id = $result->count() + 1;
                    $spec->key = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
                    $spec->value = is_string($value) ? $value : (string) $value;
                    $spec->group = $groupName;
                    $spec->sort_order = $result->count();
                    $result->push($spec);
                }
            }
        }

        return $result;
    }
}

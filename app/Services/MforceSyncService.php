<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemColor;
use App\Models\ItemImage;
use App\Models\ItemSpecification;
use App\Models\Item360Image;
use App\Models\MforceSyncLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MforceSyncService
{
    private const LOCK_KEY = 'mforce-sync-lock';
    private const LOCK_TTL = 300; // 5 menit

    public function __construct(
        private MforceApiService $api,
    ) {}

    public function syncAll(string $trigger = 'cli', bool $dryRun = false): array
    {
        return $this->runWithLock('all', null, $trigger, $dryRun, function () use ($dryRun) {
            $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'archived' => 0, 'errors' => 0];

            $brands = $this->api->getBrands();
            $motorType = CategoryType::firstOrCreate(
                ['slug' => 'motor'],
                ['name' => 'Motor', 'is_active' => true],
            );

            $allActiveMforceIds = [];

            foreach ($brands as $apiBrand) {
                if (empty($apiBrand['slug'])) {
                    continue;
                }

                $brand = $this->upsertBrand($apiBrand);
                $result = $this->api->getBrandProducts($apiBrand['slug'], ['per_page' => 50]);
                $apiProducts = $result['items'] ?? [];

                foreach ($apiProducts as $apiProduct) {
                    if (empty($apiProduct['id'])) {
                        continue;
                    }
                    $allActiveMforceIds[] = $apiProduct['id'];

                    $detail = $this->api->getMotorDetail($apiProduct['id']);
                    $outcome = $this->syncProductAtomic($apiProduct, $detail, $motorType, $brand, $dryRun);
                    $stats[$outcome]++;
                }
            }

            $stats['archived'] += $this->archiveStaleItems($motorType, $allActiveMforceIds, $dryRun);

            if (!$dryRun) {
                $this->deduplicateItems($motorType);
                $this->deduplicateBrands();
            }

            return $stats;
        });
    }

    public function syncBrand(string $brandSlug, string $trigger = 'cli', bool $dryRun = false): array
    {
        return $this->runWithLock('single_brand', $brandSlug, $trigger, $dryRun, function () use ($brandSlug, $dryRun) {
            $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'archived' => 0, 'errors' => 0];

            $apiBrand = $this->api->getBrandBySlug($brandSlug);
            if (!$apiBrand) {
                Log::warning("Brand slug not found in API: {$brandSlug}");
                $stats['errors']++;
                return $stats;
            }

            $motorType = CategoryType::firstOrCreate(
                ['slug' => 'motor'],
                ['name' => 'Motor', 'is_active' => true],
            );

            $brand = $this->upsertBrand($apiBrand);
            $result = $this->api->getBrandProducts($brandSlug, ['per_page' => 50]);
            $apiProducts = $result['items'] ?? [];

            foreach ($apiProducts as $apiProduct) {
                if (empty($apiProduct['id'])) {
                    continue;
                }

                $detail = $this->api->getMotorDetail($apiProduct['id']);
                $outcome = $this->syncProductAtomic($apiProduct, $detail, $motorType, $brand, $dryRun);
                $stats[$outcome]++;
            }

            return $stats;
        });
    }

    private function runWithLock(string $syncType, ?string $brandSlug, string $trigger, bool $dryRun, callable $callback): array
    {
        if ($dryRun) {
            return $callback();
        }

        $lock = Cache::lock(self::LOCK_KEY, self::LOCK_TTL);

        if (!$lock->get()) {
            Log::warning('MForce sync skipped — another sync is already running.');

            MforceSyncLog::create([
                'sync_type' => $syncType,
                'brand_slug' => $brandSlug,
                'trigger' => $trigger,
                'started_at' => now(),
                'finished_at' => now(),
                'duration_ms' => 0,
                'errors' => 1,
                'error_details' => 'Skipped: another sync is already running.',
                'status' => 'failed',
            ]);

            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'archived' => 0, 'errors' => 1];
        }

        $log = MforceSyncLog::create([
            'sync_type' => $syncType,
            'brand_slug' => $brandSlug,
            'trigger' => $trigger,
            'started_at' => now(),
            'status' => 'running',
        ]);

        $start = microtime(true);

        try {
            $stats = $callback();

            $log->update([
                'finished_at' => now(),
                'duration_ms' => (int) (round(microtime(true) - $start, 3) * 1000),
                'created' => $stats['created'],
                'updated' => $stats['updated'],
                'skipped' => $stats['skipped'],
                'archived' => $stats['archived'],
                'errors' => $stats['errors'],
                'status' => $stats['errors'] > 0 ? 'failed' : 'success',
            ]);

            Log::info('MForce sync complete', array_merge($stats, ['sync_log_id' => $log->id]));

            return $stats;
        } catch (\Throwable $e) {
            $log->update([
                'finished_at' => now(),
                'duration_ms' => (int) (round(microtime(true) - $start, 3) * 1000),
                'errors' => ($stats['errors'] ?? 0) + 1,
                'error_details' => $e->getMessage(),
                'status' => 'failed',
            ]);

            Log::error("MForce sync failed: {$e->getMessage()}", ['sync_log_id' => $log->id]);

            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function syncProductAtomic(
        array $apiProduct,
        ?array $detail,
        CategoryType $motorType,
        Brand $brand,
        bool $dryRun,
    ): string {
        if ($dryRun) {
            return $this->syncProduct($apiProduct, $detail, $motorType, $brand, $dryRun);
        }

        try {
            return DB::transaction(fn () => $this->syncProduct($apiProduct, $detail, $motorType, $brand, $dryRun));
        } catch (\Throwable $e) {
            Log::error("Sync product #{$apiProduct['id']} failed: {$e->getMessage()}");
            return 'errors';
        }
    }

    private function syncProduct(
        array $apiProduct,
        ?array $detail,
        CategoryType $motorType,
        Brand $brand,
        bool $dryRun = false,
    ): string {
        $category = $this->upsertCategory($apiProduct['category'] ?? 'Uncategorized', $motorType);

        $existingItem = Item::where('mforce_id', $apiProduct['id'])->first()
            ?? Item::where('slug', $apiProduct['slug'])->first();

        $baseData = [
            'category_type_id' => $motorType->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => $apiProduct['name'] ?? '',
            'slug' => $apiProduct['slug'] ?? '',
            'thumbnail_path' => $apiProduct['cover_url'] ?? ($detail['cover_path'] ?? null),
            'description' => $detail['long_description'] ?? null,
            'short_description' => $detail['short_description'] ?? null,
            'year' => null,
            'status' => 'active',
            'is_active' => true,
            'stock_status' => 'ready',
        ];

        if ($existingItem) {
            $baseData['mforce_id'] = $apiProduct['id'];

            $changed = false;
            foreach (['mforce_id', 'name', 'slug', 'description', 'short_description', 'thumbnail_path', 'year'] as $field) {
                if ($existingItem->{$field} !== $baseData[$field]) {
                    $changed = true;
                    break;
                }
            }

            if ($changed && !$dryRun) {
                $existingItem->update($baseData);
            }
            $item = $existingItem;
            $status = $changed ? 'updated' : 'skipped';
        } else {
            if (!$dryRun) {
                $item = Item::create(array_merge($baseData, [
                    'price' => null,
                    'mforce_id' => $apiProduct['id'],
                ]));
            }
            $status = 'created';
        }

        if (!$dryRun && isset($item)) {
            $this->upsertColors($item, $detail['variants'] ?? $apiProduct['variants'] ?? []);
            $this->upsertImages($item, $detail['gallery'] ?? []);
            $this->upsertSpecifications($item, $detail['specs'] ?? $apiProduct['specs'] ?? []);
            $this->upsert360Images($item, $detail['variants'] ?? []);
        }

        return $status;
    }

    private function upsertBrand(array $apiBrand): Brand
    {
        return Brand::updateOrCreate(
            ['slug' => $apiBrand['slug']],
            [
                'name' => $apiBrand['name'] ?? '',
                'logo_path' => $apiBrand['logo_url'] ?? null,
                'description' => $apiBrand['description'] ?? null,
                'is_active' => true,
            ],
        );
    }

    private function upsertCategory(string $categoryName, CategoryType $type): Category
    {
        return Category::firstOrCreate(
            ['category_type_id' => $type->id, 'slug' => Str::slug($categoryName)],
            ['name' => $categoryName, 'is_active' => true],
        );
    }

    private function upsertColors(Item $item, array $apiVariants): void
    {
        $activeIds = [];

        foreach ($apiVariants as $v) {
            if (empty($v['id'])) {
                continue;
            }
            $activeIds[] = $v['id'];

            ItemColor::updateOrCreate(
                ['item_id' => $item->id, 'mforce_id' => $v['id']],
                [
                    'name' => $v['name'] ?? '',
                    'color_code' => $v['color'] ?? null,
                    'image_path' => $v['image_path'] ?? null,
                    'sort_order' => $v['sort_number'] ?? 0,
                    'is_active' => true,
                ],
            );
        }

        if (!empty($activeIds)) {
            ItemColor::where('item_id', $item->id)
                ->whereNotNull('mforce_id')
                ->whereNotIn('mforce_id', $activeIds)
                ->update(['is_active' => false]);
        }
    }

    private function upsertImages(Item $item, array $apiGallery): void
    {
        $activeIds = [];

        foreach ($apiGallery as $g) {
            if (empty($g['id'])) {
                continue;
            }
            $activeIds[] = $g['id'];

            ItemImage::updateOrCreate(
                ['item_id' => $item->id, 'mforce_id' => $g['id']],
                [
                    'path' => $g['url'] ?? '',
                    'sort_order' => $g['sort_number'] ?? 0,
                    'is_active' => true,
                ],
            );
        }

        if (!empty($activeIds)) {
            ItemImage::where('item_id', $item->id)
                ->whereNotNull('mforce_id')
                ->whereNotIn('mforce_id', $activeIds)
                ->update(['is_active' => false]);
        }
    }

    private function upsertSpecifications(Item $item, array $apiSpecs): void
    {
        $activeIds = [];
        $sort = 0;
        $groupLabels = [
            'engine' => 'Engine',
            'chassis' => 'Chassis',
            'dimensions' => 'Dimension',
        ];
        $specLabels = [
            'engine_type' => 'Engine Type', 'power' => 'Power', 'torque' => 'Torque',
            'capacity' => 'Capacity', 'bore_stroke' => 'Bore Stroke', 'compression' => 'Compression',
            'clutch' => 'Clutch',
            'lxwxh' => 'Panjang x Lebar x Tinggi', 'weight' => 'Weight',
            'wheelbase' => 'Wheelbase', 'fuel_capacity' => 'Fuel Capacity',
            'ground_clearance' => 'Ground Clearance',
            'front_susp' => 'Front Suspension', 'rear_susp' => 'Rear Suspension',
            'front_brake' => 'Front Brake', 'rear_brake' => 'Rear Brake',
            'front_tire' => 'Front Tire', 'rear_tire' => 'Rear Tire',
            'abs_cbs' => 'ABS/CBS', 'tcs' => 'TCS', 'wheel' => 'Wheel',
        ];

        foreach ($apiSpecs as $groupKey => $groupSpecs) {
            if (!is_array($groupSpecs)) {
                continue;
            }
            $groupName = $groupLabels[$groupKey] ?? ucfirst($groupKey);

            foreach ($groupSpecs as $key => $value) {
                if ($value === null || $value === '' || $key === 'colors') {
                    continue;
                }
                $mforceId = crc32($groupKey . '_' . $key);
                $activeIds[] = $mforceId;

                ItemSpecification::updateOrCreate(
                    ['item_id' => $item->id, 'mforce_id' => $mforceId],
                    [
                        'group' => $groupName,
                        'key' => $specLabels[$key] ?? ucwords(str_replace('_', ' ', $key)),
                        'value' => is_string($value) ? $value : (string) $value,
                        'sort_order' => $sort,
                        'is_active' => true,
                    ],
                );
                $sort++;
            }
        }

        if (!empty($activeIds)) {
            ItemSpecification::where('item_id', $item->id)
                ->whereNotNull('mforce_id')
                ->whereNotIn('mforce_id', $activeIds)
                ->update(['is_active' => false]);
        }
    }

    private function upsert360Images(Item $item, array $apiVariants): void
    {
        $activeIds = [];
        $sort = 0;

        foreach ($apiVariants as $v) {
            $frames = $v['viewer360'] ?? [];
            foreach ($frames as $frame) {
                if (empty($frame['id'])) {
                    continue;
                }
                $activeIds[] = $frame['id'];

                Item360Image::updateOrCreate(
                    ['item_id' => $item->id, 'mforce_id' => $frame['id']],
                    [
                        'path' => $frame['url'] ?? '',
                        'sort_order' => $sort,
                        'is_active' => true,
                    ],
                );
                $sort++;
            }
        }

        if (!empty($activeIds)) {
            Item360Image::where('item_id', $item->id)
                ->whereNotNull('mforce_id')
                ->whereNotIn('mforce_id', $activeIds)
                ->update(['is_active' => false]);
        }
    }

    private function archiveStaleItems(CategoryType $motorType, array $activeMforceIds, bool $dryRun): int
    {
        $count = 0;

        $stale = Item::where('category_type_id', $motorType->id)
            ->whereNotNull('mforce_id')
            ->whereNotIn('mforce_id', $activeMforceIds)
            ->where('status', 'active')
            ->get();

        foreach ($stale as $item) {
            if (!$dryRun) {
                $item->update(['status' => 'inactive', 'is_active' => false]);
            }
            Log::info("MForce sync: archived item #{$item->id} ({$item->name})");
            $count++;
        }

        return $count;
    }

    private function deduplicateItems(CategoryType $motorType): void
    {
        $slugs = Item::where('category_type_id', $motorType->id)
            ->whereNotNull('mforce_id')
            ->pluck('slug');

        if ($slugs->isEmpty()) {
            return;
        }

        $deleted = Item::where('category_type_id', $motorType->id)
            ->whereNull('mforce_id')
            ->whereIn('slug', $slugs)
            ->delete();

        if ($deleted > 0) {
            Log::info("MForce sync: removed {$deleted} duplicate items (no mforce_id)");
        }
    }

    private function deduplicateBrands(): void
    {
        $dupes = Brand::selectRaw('LOWER(REPLACE(name, " ", "")) as clean_name, COUNT(*) as cnt')
            ->groupBy('clean_name')
            ->having('cnt', '>', 1)
            ->pluck('clean_name');

        foreach ($dupes as $cleanName) {
            $brands = Brand::whereRaw('LOWER(REPLACE(name, " ", "")) = ?', [$cleanName])
                ->orderByDesc('id')
                ->get();

            $keeper = $brands->first();
            $stale = $brands->slice(1);

            foreach ($stale as $b) {
                Item::where('brand_id', $b->id)->update(['brand_id' => $keeper->id]);
                $b->delete();
            }

            Log::info("MForce sync: merged duplicate brand '{$cleanName}' — kept #{$keeper->id}");
        }
    }
}

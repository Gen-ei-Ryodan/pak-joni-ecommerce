<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Career;
use App\Models\CategoryType;
use App\Models\CompanyProfile;
use App\Models\CsrArticle;
use App\Models\Dealer;
use App\Models\Event;
use App\Models\InternalActivity;
use App\Models\Item;
use App\Models\ItemPriceList;
use App\Models\ItemPartCatalog;
use App\Models\News;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\HeroVideo;
use App\Models\ShowroomGallery;
use App\Models\WhyChooseUs;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        $heroBanners = Banner::query()->where('is_active', true)->where('type', 'hero')->orderBy('sort_order')->get();
        $promoBanners = Banner::query()->where('is_active', true)->where('type', 'promo')->orderBy('sort_order')->get();
        $launchingBanners = Banner::query()->where('is_active', true)->where('type', 'launching')->orderBy('sort_order')->get();
        $kegiatanBanners = Banner::query()->where('is_active', true)->where('type', 'kegiatan')->orderBy('sort_order')->get();

        $latestNews = News::query()->where('is_active', true)->orderByDesc('publish_date')->take(4)->get();

        $brands = Brand::query()->where('is_active', true)->orderBy('sort_order')->get();

        $whyChooseUs = WhyChooseUs::query()->where('is_active', true)->orderBy('sort_order')->get();

        $latestEvents = Event::query()->where('is_active', true)->orderByDesc('event_date')->take(3)->get();

        // Ambil items dari semua category types (8 terbaru)
        $items = Item::query()
            ->with(['brand', 'category', 'type'])
            ->where('status', 'active')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $parts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $heroVideo = HeroVideo::query()->where('is_active', true)->first();

        return view('buyer.home', compact(
            'heroBanners', 'promoBanners', 'launchingBanners', 'kegiatanBanners',
            'latestNews', 'brands', 'whyChooseUs', 'latestEvents',
            'items', 'parts', 'heroVideo'
        ));
    }

    public function about()
    {
        $profile = CompanyProfile::pluck('value', 'key');
        return view('buyer.about', compact('profile'));
    }

    public function products(Request $request)
    {
        $brands = Brand::query()->where('is_active', true)->orderBy('sort_order')->get();

        $selectedBrand = $request->query('brand');
        $selectedCategory = $request->query('category');
        $productType = $request->query('type'); // null = show all, 'motor' or 'sparepart'

        // Get motor category type
        $motorType = CategoryType::where('slug', 'motor')->where('is_active', true)->first();

        // Get categories for motor type
        $categories = collect();
        if ($motorType) {
            $categories = \App\Models\Category::query()
                ->where('category_type_id', $motorType->id)
                ->where('is_active', true)
                ->when($selectedBrand, fn($q) => $q->whereHas('items.brand', fn($b) => $b->where('slug', $selectedBrand)))
                ->orderBy('sort_order')
                ->get();
        }

        $sparepartGroups = PartCategory::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $selectedSparepartGroup = $request->query('part_group');

        // Always fetch items when type is null or 'motor'
        $items = collect();
        if ($productType === null || $productType === 'motor') {
            $query = Item::query()
                ->with(['brand', 'category', 'images', 'colors', 'type'])
                ->where('status', 'active')
                ->where('is_active', true);

            if ($motorType && $productType === 'motor') {
                $query->where('category_type_id', $motorType->id);
            }

            $query->when($selectedBrand, fn($q) => $q->whereHas('brand', fn($b) => $b->where('slug', $selectedBrand)))
                ->when($selectedCategory, fn($q) => $q->whereHas('category', fn($c) => $c->where('slug', $selectedCategory)));

            $items = $query->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();
        }

        // Always fetch parts when type is null or 'sparepart'
        $parts = collect();
        if ($productType === null || $productType === 'sparepart') {
            $parts = Part::query()
                ->with(['category', 'defaultVariant', 'variants'])
                ->where('status', 'active')
                ->when($selectedBrand, fn($q) => $q->whereHas('items.brand', fn($b) => $b->where('slug', $selectedBrand)))
                ->when($selectedSparepartGroup, fn($q) => $q->whereHas('category', fn($c) => $c->where('group', $selectedSparepartGroup)))
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();
        }

        return view('buyer.products', compact(
            'brands', 'categories', 'sparepartGroups',
            'items', 'parts',
            'selectedBrand', 'selectedCategory', 'productType', 'selectedSparepartGroup'
        ));
    }

    public function dealer(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $province = $request->query('province');
        $city = $request->query('city');

        $provinces = Dealer::query()->select('province')->distinct()->orderBy('province')->pluck('province');
        $cities = Dealer::query()->select('city')->distinct()->orderBy('city')->pluck('city');

        $dealers = Dealer::query()
            ->where('is_active', true)
            ->when($q !== '', fn($query) => $query->where(function($qry) use ($q) {
                $qry->where('name', 'like', '%'.$q.'%')
                    ->orWhere('address', 'like', '%'.$q.'%')
                    ->orWhere('city', 'like', '%'.$q.'%');
            }))
            ->when($province, fn($query) => $query->where('province', $province))
            ->when($city, fn($query) => $query->where('city', $city))
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.dealer', compact('dealers', 'q', 'province', 'city', 'provinces', 'cities'));
    }

    public function priceList(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $priceLists = ItemPriceList::query()
            ->with('item')
            ->when($q !== '', fn($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.spareparts.price-list', compact('priceLists', 'q'));
    }

    public function partCatalog(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $catalogs = ItemPartCatalog::query()
            ->with('item')
            ->when($q !== '', fn($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.spareparts.part-catalog', compact('catalogs', 'q'));
    }

    public function categoryBrand($categoryType, $brand, Request $request)
    {
        $type = CategoryType::where('slug', $categoryType)->where('is_active', true)->firstOrFail();

        // Special handling for 'sparepart' — show full parts catalog with filters
        if ($type->slug === 'sparepart') {
            return $this->sparepartCatalog($request, $brand);
        }

        $brandModel = null;
        if ($brand !== 'all') {
            $brandModel = Brand::where('slug', $brand)->where('is_active', true)->firstOrFail();
        }

        // Categories within this category type and brand
        $categories = \App\Models\Category::query()
            ->where('category_type_id', $type->id)
            ->where('is_active', true)
            ->when($brandModel, fn($q) => $q->whereHas('items', fn($iq) => $iq
                ->where('brand_id', $brandModel->id)
                ->where('status', 'active')
                ->where('is_active', true)
            ))
            ->orderBy('sort_order')
            ->get();

        $selectedCategory = $request->query('category');

        // Items
        $items = Item::query()
            ->with(['brand', 'category', 'images', 'colors', 'type'])
            ->where('category_type_id', $type->id)
            ->where('status', 'active')
            ->where('is_active', true)
            ->when($brandModel, fn($q) => $q->where('brand_id', $brandModel->id))
            ->when($selectedCategory, fn($q) => $q->whereHas('category', fn($c) => $c->where('slug', $selectedCategory)))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        // Spareparts compatible with items from this brand (regardless of part's own category_type)
        $parts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->whereHas('items', fn($iq) => $iq
                ->where('category_type_id', $type->id)
                ->where('status', 'active')
                ->where('is_active', true)
                ->when($brandModel, fn($q) => $q->where('brand_id', $brandModel->id))
            )
            ->orderByDesc('id')
            ->paginate(12, ['*'], 'part_page')
            ->withQueryString();

        $allBrands = Brand::whereHas('items', fn($q) => $q->where('category_type_id', $type->id)->where('status', 'active')->where('is_active', true))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('buyer.category-brand', compact(
            'type', 'brandModel', 'allBrands',
            'categories', 'selectedCategory',
            'items', 'parts'
        ));
    }

    protected function sparepartCatalog(Request $request, string $routeBrand = 'all')
    {
        $q = trim((string) $request->query('q', ''));
        $category = $request->query('category');
        $group = $request->query('group');
        $brandSlug = $request->query('brand') ?: ($routeBrand !== 'all' ? $routeBrand : '');

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
            ->when($brandSlug !== '' && $brandSlug !== 'all', fn ($query) => $query->whereHas('items.brand', fn ($q2) => $q2->where('slug', $brandSlug)))
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

        return view('buyer.parts.index', compact(
            'parts', 'categories', 'brands', 'groups',
            'q', 'category', 'group',
            'brandSlug', 'selectedCategoryName'
        ));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type'); // null = all, 'motor', 'sparepart'
        $brand = $request->query('brand');
        $partGroup = $request->query('part_group');

        $brands = Brand::query()->where('is_active', true)->orderBy('sort_order')->get();
        $partGroups = PartCategory::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        $motorType = CategoryType::where('slug', 'motor')->where('is_active', true)->first();

        $items = collect();
        $parts = collect();

        if ($type === null || $type === 'motor') {
            $items = Item::query()
                ->with(['brand', 'category', 'type'])
                ->where('status', 'active')
                ->where('is_active', true)
                ->when($motorType && $type === 'motor', fn($q) => $q->where('category_type_id', $motorType->id))
                ->when($q !== '', fn($query) => $query->where(function($q2) use ($q) {
                    $q2->where('name', 'like', '%'.$q.'%')
                       ->orWhere('short_description', 'like', '%'.$q.'%');
                }))
                ->when($brand, fn($query) => $query->whereHas('brand', fn($b) => $b->where('slug', $brand)))
                ->orderByDesc('id')
                ->paginate(12, ['*'], 'motor_page')
                ->withQueryString();
        }

        if ($type === null || $type === 'sparepart') {
            $parts = Part::query()
                ->with(['category', 'defaultVariant'])
                ->where('status', 'active')
                ->when($q !== '', fn($query) => $query->where(function($q2) use ($q) {
                    $q2->where('name', 'like', '%'.$q.'%')
                       ->orWhere('sku', 'like', '%'.$q.'%')
                       ->orWhere('short_description', 'like', '%'.$q.'%');
                }))
                ->when($brand, fn($query) => $query->whereHas('items.brand', fn($b) => $b->where('slug', $brand)))
                ->when($partGroup, fn($query) => $query->whereHas('category', fn($c) => $c->where('group', $partGroup)))
                ->orderByDesc('id')
                ->paginate(12, ['*'], 'part_page')
                ->withQueryString();
        }

        $totalResults = 0;
        if ($type === null || $type === 'motor') $totalResults += $items->total();
        if ($type === null || $type === 'sparepart') $totalResults += $parts->total();

        return view('buyer.search', compact(
            'q', 'type', 'brand', 'partGroup',
            'brands', 'partGroups',
            'items', 'parts',
            'totalResults'
        ));
    }

    public function news(Request $request)
    {
        $news = \App\Models\News::query()
            ->where('is_active', true)
            ->orderByDesc('publish_date')
            ->paginate(9);

        return view('buyer.news.index', compact('news'));
    }

    public function newsShow(\App\Models\News $news)
    {
        $relatedNews = \App\Models\News::query()
            ->where('is_active', true)
            ->where('id', '!=', $news->id)
            ->orderByDesc('publish_date')
            ->take(3)
            ->get();

        return view('buyer.news.show', compact('news', 'relatedNews'));
    }

    public function events(Request $request)
    {
        $events = \App\Models\Event::query()
            ->where('is_active', true)
            ->orderByDesc('event_date')
            ->paginate(9);

        return view('buyer.events.index', compact('events'));
    }

    public function eventShow(\App\Models\Event $event)
    {
        $event->load('galleries');
        $relatedEvents = \App\Models\Event::query()
            ->where('is_active', true)
            ->where('id', '!=', $event->id)
            ->orderByDesc('event_date')
            ->take(3)
            ->get();

        return view('buyer.events.show', compact('event', 'relatedEvents'));
    }

    public function csr(Request $request)
    {
        $articles = \App\Models\CsrArticle::query()
            ->where('is_active', true)
            ->orderByDesc('publish_date')
            ->paginate(9);

        return view('buyer.csr.index', compact('articles'));
    }

    public function csrShow(\App\Models\CsrArticle $article)
    {
        $relatedArticles = \App\Models\CsrArticle::query()
            ->where('is_active', true)
            ->where('id', '!=', $article->id)
            ->orderByDesc('publish_date')
            ->take(3)
            ->get();

        return view('buyer.csr.show', compact('article', 'relatedArticles'));
    }

    public function careers(Request $request)
    {
        $careers = \App\Models\Career::query()
            ->where('is_active', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('publish_date')
                  ->orWhere('publish_date', '<=', now());
            })
            ->orderByDesc('publish_date')
            ->paginate(9);

        return view('buyer.careers.index', compact('careers'));
    }

    public function careerShow(\App\Models\Career $career)
    {
        return view('buyer.careers.show', compact('career'));
    }

    public function internalActivities(Request $request)
    {
        $activities = \App\Models\InternalActivity::query()
            ->where('is_active', true)
            ->orderByDesc('publish_date')
            ->paginate(9);

        return view('buyer.internal-activities.index', compact('activities'));
    }

    public function internalActivityShow(\App\Models\InternalActivity $activity)
    {
        $activity->load('galleries');
        return view('buyer.internal-activities.show', compact('activity'));
    }

    public function showroom()
    {
        $images = \App\Models\ShowroomGallery::query()
            ->orderBy('sort_order')
            ->get();

        return view('buyer.showroom', compact('images'));
    }
}

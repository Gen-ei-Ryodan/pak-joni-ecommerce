<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Career;
use App\Models\CompanyProfile;
use App\Models\CsrArticle;
use App\Models\Dealer;
use App\Models\Event;
use App\Models\InternalActivity;
use App\Models\Motor;
use App\Models\MotorCategory;
use App\Models\News;
use App\Models\Part;
use App\Models\PartCatalog;
use App\Models\PartCategory;
use App\Models\PriceList;
use App\Models\ProductHighlight;
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

        $highlight = ProductHighlight::query()
            ->with('motor')
            ->where('is_active', true)
            ->first();

        $brands = Brand::query()->where('is_active', true)->orderBy('sort_order')->get();

        $whyChooseUs = WhyChooseUs::query()->where('is_active', true)->orderBy('sort_order')->get();

        $latestEvents = Event::query()->where('is_active', true)->orderByDesc('event_date')->take(3)->get();

        $motors = Motor::query()
            ->with(['brand', 'category'])
            ->where('status', 'active')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $parts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('buyer.home', compact(
            'heroBanners', 'promoBanners', 'launchingBanners', 'kegiatanBanners',
            'latestNews', 'highlight', 'brands', 'whyChooseUs', 'latestEvents',
            'motors', 'parts'
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

        // Get categories filtered by brand
        $categories = MotorCategory::query()
            ->when($selectedBrand, fn($q) => $q->whereHas('brand', fn($b) => $b->where('slug', $selectedBrand)))
            ->orderBy('sort_order')
            ->get();

        $sparepartGroups = PartCategory::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $selectedSparepartGroup = $request->query('part_group');

        // Always fetch motors when type is null or 'motor'
        $motors = collect();
        if ($productType === null || $productType === 'motor') {
            $motors = Motor::query()
                ->with(['brand', 'category', 'images', 'colors'])
                ->where('status', 'active')
                ->when($selectedBrand, fn($q) => $q->whereHas('brand', fn($b) => $b->where('slug', $selectedBrand)))
                ->when($selectedCategory, fn($q) => $q->whereHas('category', fn($c) => $c->where('slug', $selectedCategory)))
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();
        }

        // Always fetch parts when type is null or 'sparepart'
        $parts = collect();
        if ($productType === null || $productType === 'sparepart') {
            $parts = Part::query()
                ->with(['category', 'defaultVariant', 'variants', 'motors.brand'])
                ->where('status', 'active')
                ->when($selectedBrand, fn($q) => $q->whereHas('motors', fn($mq) => $mq->whereHas('brand', fn($b) => $b->where('slug', $selectedBrand))))
                ->when($selectedCategory, fn($q) => $q->whereHas('motors', fn($mq) => $mq->whereHas('category', fn($c) => $c->where('slug', $selectedCategory))))
                ->when($selectedSparepartGroup, fn($q) => $q->whereHas('category', fn($c) => $c->where('group', $selectedSparepartGroup)))
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();
        }

        return view('buyer.products', compact(
            'brands', 'categories', 'sparepartGroups',
            'motors', 'parts',
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

    public function news(Request $request)
    {
        $news = News::query()
            ->where('is_active', true)
            ->orderByDesc('publish_date')
            ->paginate(9)
            ->withQueryString();

        return view('buyer.news.index', compact('news'));
    }

    public function newsShow(News $news)
    {
        $related = News::query()
            ->where('is_active', true)
            ->where('id', '!=', $news->id)
            ->orderByDesc('publish_date')
            ->take(4)
            ->get();

        return view('buyer.news.show', compact('news', 'related'));
    }

    public function events(Request $request)
    {
        $events = Event::query()
            ->with('galleries')
            ->where('is_active', true)
            ->orderByDesc('event_date')
            ->paginate(9)
            ->withQueryString();

        return view('buyer.events.index', compact('events'));
    }

    public function eventShow(Event $event)
    {
        $event->load('galleries');

        return view('buyer.events.show', compact('event'));
    }

    public function csr(Request $request)
    {
        $articles = CsrArticle::query()
            ->where('is_active', true)
            ->orderByDesc('publish_date')
            ->paginate(9)
            ->withQueryString();

        return view('buyer.csr.index', compact('articles'));
    }

    public function csrShow(CsrArticle $article)
    {
        return view('buyer.csr.show', compact('article'));
    }

    public function careers(Request $request)
    {
        $careers = Career::query()
            ->where('is_active', true)
            ->where('status', 'active')
            ->orderByDesc('publish_date')
            ->paginate(9)
            ->withQueryString();

        return view('buyer.careers.index', compact('careers'));
    }

    public function careerShow(Career $career)
    {
        return view('buyer.careers.show', compact('career'));
    }

    public function internalActivities(Request $request)
    {
        $activities = InternalActivity::query()
            ->where('is_active', true)
            ->orderByDesc('publish_date')
            ->paginate(9)
            ->withQueryString();

        return view('buyer.internal-activities.index', compact('activities'));
    }

    public function internalActivityShow(InternalActivity $activity)
    {
        return view('buyer.internal-activities.show', compact('activity'));
    }

    public function priceList(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $priceLists = PriceList::query()
            ->when($q !== '', fn($query) => $query->where('title', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.spareparts.price-list', compact('priceLists', 'q'));
    }

    public function partCatalog(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $catalogs = PartCatalog::query()
            ->when($q !== '', fn($query) => $query->where('title', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.spareparts.part-catalog', compact('catalogs', 'q'));
    }

    public function showroom()
    {
        $images = ShowroomGallery::query()->where('is_active', true)->orderBy('sort_order')->get();
        return view('buyer.showroom', compact('images'));
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

        $motors = collect();
        $parts = collect();

        if ($type === null || $type === 'motor') {
            $motors = Motor::query()
                ->with(['brand', 'category'])
                ->where('status', 'active')
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
                ->with(['category', 'defaultVariant', 'motors.brand'])
                ->where('status', 'active')
                ->when($q !== '', fn($query) => $query->where(function($q2) use ($q) {
                    $q2->where('name', 'like', '%'.$q.'%')
                       ->orWhere('sku', 'like', '%'.$q.'%')
                       ->orWhere('short_description', 'like', '%'.$q.'%');
                }))
                ->when($brand, fn($query) => $query->whereHas('motors', fn($mq) => $mq->whereHas('brand', fn($b) => $b->where('slug', $brand))))
                ->when($partGroup, fn($query) => $query->whereHas('category', fn($c) => $c->where('group', $partGroup)))
                ->orderByDesc('id')
                ->paginate(12, ['*'], 'part_page')
                ->withQueryString();
        }

        $totalResults = 0;
        if ($type === null || $type === 'motor') $totalResults += $motors->total();
        if ($type === null || $type === 'sparepart') $totalResults += $parts->total();

        return view('buyer.search', compact(
            'q', 'type', 'brand', 'partGroup',
            'brands', 'partGroups',
            'motors', 'parts',
            'totalResults'
        ));
    }
}

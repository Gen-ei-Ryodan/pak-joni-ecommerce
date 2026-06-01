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

        $categories = MotorCategory::query()->orderBy('sort_order')->get();

        $motors = Motor::query()
            ->with(['brand', 'category', 'images'])
            ->where('status', 'active')
            ->when($selectedBrand, fn($q) => $q->whereHas('brand', fn($b) => $b->where('slug', $selectedBrand)))
            ->when($selectedCategory, fn($q) => $q->whereHas('category', fn($c) => $c->where('slug', $selectedCategory)))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.products', compact('brands', 'categories', 'motors', 'selectedBrand', 'selectedCategory'));
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
        $activity->load('galleries');

        return view('buyer.internal-activities.show', compact('activity'));
    }

    public function priceList()
    {
        $priceLists = PriceList::query()
            ->with('motor')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('buyer.spareparts.price-list', compact('priceLists'));
    }

    public function partCatalog()
    {
        $partCatalogs = PartCatalog::query()
            ->with('motor')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('buyer.spareparts.part-catalog', compact('partCatalogs'));
    }

    public function quotationStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:2000',
        ]);

        \App\Models\QuotationRequest::create($validated);

        return back()->with('success', 'Terima kasih! Permintaan penawaran Anda telah dikirim. Tim kami akan segera menghubungi Anda.');
    }
}

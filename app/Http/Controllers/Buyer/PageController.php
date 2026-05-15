<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Motor;
use App\Models\Part;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        $banners = Banner::query()->where('is_active', true)->orderBy('sort_order')->get();

        $motors = Motor::query()
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        $parts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        return view('buyer.home', compact('banners', 'motors', 'parts'));
    }

    public function about()
    {
        return view('buyer.about');
    }

    public function products(Request $request)
    {
        $motors = Motor::query()
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        $parts = Part::query()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        return view('buyer.products', compact('motors', 'parts'));
    }
}

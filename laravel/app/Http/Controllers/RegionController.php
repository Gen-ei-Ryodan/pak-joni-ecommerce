<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RegionController extends Controller
{
    public function provinces()
    {
        return Cache::remember('regions:provinces', now()->addDays(30), function () {
            $response = Http::get('https://wilayah.id/api/provinces.json');
            return $response->json();
        });
    }

    public function regencies($provinceCode)
    {
        return Cache::remember("regions:regencies:{$provinceCode}", now()->addDays(30), function () use ($provinceCode) {
            $response = Http::get("https://wilayah.id/api/regencies/{$provinceCode}.json");
            return $response->json();
        });
    }

    public function districts($regencyCode)
    {
        return Cache::remember("regions:districts:{$regencyCode}", now()->addDays(30), function () use ($regencyCode) {
            $response = Http::get("https://wilayah.id/api/districts/{$regencyCode}.json");
            return $response->json();
        });
    }

    public function villages($districtCode)
    {
        return Cache::remember("regions:villages:{$districtCode}", now()->addDays(30), function () use ($districtCode) {
            $response = Http::get("https://wilayah.id/api/villages/{$districtCode}.json");
            return $response->json();
        });
    }
}

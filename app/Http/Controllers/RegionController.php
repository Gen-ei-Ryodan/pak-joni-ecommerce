<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
        if (! preg_match('/^\d{1,2}$/', $provinceCode)) {
            abort(404);
        }

        return Cache::remember("regions:regencies:{$provinceCode}", now()->addDays(30), function () use ($provinceCode) {
            $response = Http::get("https://wilayah.id/api/regencies/{$provinceCode}.json");

            return $response->json();
        });
    }

    public function districts($regencyCode)
    {
        if (! preg_match('/^[\d.]+$/', $regencyCode)) {
            abort(404);
        }

        return Cache::remember("regions:districts:{$regencyCode}", now()->addDays(30), function () use ($regencyCode) {
            $response = Http::get("https://wilayah.id/api/districts/{$regencyCode}.json");

            return $response->json();
        });
    }

    public function villages($districtCode)
    {
        if (! preg_match('/^[\d.]+$/', $districtCode)) {
            abort(404);
        }

        return Cache::remember("regions:villages:{$districtCode}", now()->addDays(30), function () use ($districtCode) {
            $response = Http::get("https://wilayah.id/api/villages/{$districtCode}.json");

            return $response->json();
        });
    }
}

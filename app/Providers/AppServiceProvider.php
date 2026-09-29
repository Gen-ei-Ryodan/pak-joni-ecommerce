<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.simple-dark');

        $livewireTmp = Storage::disk('local')->path('livewire-tmp');
        if (! is_dir($livewireTmp)) {
            mkdir($livewireTmp, 0755, true);
        }

        RateLimiter::for('midtrans-webhook', function ($job) {
            return Limit::perMinute(60);
        });

        RateLimiter::for('auth', function ($job) {
            return Limit::perMinute(10)->by($job->ip());
        });

        // Endpoint status pembayaran di-poll tiap 3-5 detik (12-20/menit) dan
        // berbagi bucket dengan generate QR. Limit lama 10/menit membuat request
        // ke-11 balik 429 HTML -> JS `r.json()` gagal -> alert "Gagal terhubung
        // ke server" saat klik Bayar Sekarang.
        RateLimiter::for('payment-actions', function ($job) {
            return Limit::perMinute(60)->by($job->user()?->id ?: $job->ip());
        });
    }
}

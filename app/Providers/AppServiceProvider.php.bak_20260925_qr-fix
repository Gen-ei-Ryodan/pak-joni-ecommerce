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

        RateLimiter::for('payment-actions', function ($job) {
            return Limit::perMinute(10)->by($job->user()?->id ?: $job->ip());
        });
    }
}

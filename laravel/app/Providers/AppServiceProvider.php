<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (request()->header('X-Forwarded-Proto') === 'https' || (config('app.env') === 'production' && !str_contains(request()->getHost(), 'localhost') && !str_contains(request()->getHost(), '127.0.0.1'))) {
            URL::forceScheme('https');
        }
    }
}

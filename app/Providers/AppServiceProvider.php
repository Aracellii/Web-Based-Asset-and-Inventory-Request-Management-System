<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set Carbon locale to Indonesian and timezone to WIB
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        if (Request::header('x-forwarded-proto') === 'https' || str_contains(Request::header('host'), 'ngrok-free.app')) {
            URL::forceScheme('https');
        }
    }
}

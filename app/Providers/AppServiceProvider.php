<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;

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
    Carbon::setLocale('id');
    date_default_timezone_set('Asia/Jakarta');

    // HAPUS atau comment baris ini — ini penyebab utama looping
    // URL::forceRootUrl(config('app.url'));

    // HAPUS juga Livewire custom route ini jika tidak deploy di subfolder /SIATK
    // Livewire::setUpdateRoute(function ($handle) {
    //     return Route::post('/SIATK/livewire/update', $handle);
    // });
    // Livewire::setScriptRoute(function ($handle) {
    //     return Route::get('/SIATK/livewire/livewire.js', $handle);
    // });
}
}

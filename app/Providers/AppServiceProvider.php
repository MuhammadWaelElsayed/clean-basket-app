<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

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
        Schema::defaultStringLength(191);

        Order::observe(OrderObserver::class);

//        Livewire::setScriptRoute(function ($handle) {
//            return Route::get(env('APP_FOLDER').'/livewire/livewire.js', $handle)->middleware('auth:admin');
//        });
//        Livewire::setUpdateRoute(function ($handle) {
//            return Route::post(env('APP_FOLDER').'/livewire/update', $handle)->middleware('auth:admin');
//        });
    }
}

<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\TravelRequestServiceInterface;
use App\Services\AuthService;
use App\Services\TravelRequestService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(TravelRequestServiceInterface::class, TravelRequestService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

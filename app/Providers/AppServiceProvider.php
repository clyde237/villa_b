<?php

namespace App\Providers;

use App\Services\CheckOutService;
use App\Services\LoyaltyService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Injection de dépendance automatique
        $this->app->bind(LoyaltyService::class);
        $this->app->bind(CheckOutService::class);
    }

    public function boot(): void
    {
        \Carbon\Carbon::setLocale('fr');
    }
}
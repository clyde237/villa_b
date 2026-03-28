<?php

namespace App\Providers;

use App\Services\CheckOutService;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\Blade;
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

        // Directives Blade pour RBAC
        $this->registerBladeDirectives();
    }

    /**
     * Enregistrer les directives Blade personnalisées pour RBAC
     */
    private function registerBladeDirectives(): void
    {
        // @admin ... @endadmin
        Blade::directive('admin', function () {
            return '<?php if(auth()->check() && auth()->user()->isAdmin()): ?>';
        });
        Blade::directive('endadmin', function () {
            return '<?php endif; ?>';
        });

        // @role('manager') ... @endrole
        Blade::directive('role', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole([{$expression}])): ?>";
        });
        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });

        // @hasnotRole('housekeeping') ... @endhasnotRole
        Blade::directive('hasnotRole', function ($expression) {
            return "<?php if(!auth()->check() || !auth()->user()->hasAnyRole([{$expression}])): ?>";
        });
        Blade::directive('endhasnotRole', function () {
            return '<?php endif; ?>';
        });
    }
}

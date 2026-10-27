<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\OpenIdConnect\Providers\EntraProvider;

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
        // Use Bootstrap 5 pagination views
        Paginator::useBootstrapFive();

        // Share counties with all views dynamically
        View::composer('*', function ($view) {
            if (!$view->offsetExists('counties')) {
                static $cachedCounties = null;
                if ($cachedCounties === null) {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('counties')) {
                            $cachedCounties = \App\Models\County::orderBy('name')->pluck('name')->toArray();
                        }
                    } catch (\Throwable $e) {
                    }
                    if (empty($cachedCounties)) {
                        $cachedCounties = [
                            'Fairfield', 'Hartford', 'Litchfield', 'Middlesex',
                            'New Haven', 'New London', 'Tolland', 'Windham', 'Statewide',
                        ];
                    }
                }
                $view->with('counties', $cachedCounties);
            }
        });

        // Define an admin gate
        Gate::define('admin', function ($user) {
            return $user && $user->is_admin;
        });

        Event::listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event) {
            $event->extendSocialite('oidc_entra', EntraProvider::class);
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;

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

        // Define counties array
        $counties = [
            'Hartford County',
            'New Haven County',
            'Fairfield County',
            'Litchfield County',
            'Middlesex County',
            'Tolland County',
            'Windham County',
            'New London County'
        ];

        sort($counties);

        // Share counties with all views
        View::share('counties', $counties);

        // Define an admin gate
        Gate::define('admin', function ($user) {
            return $user && $user->is_admin;
        });
    }
}

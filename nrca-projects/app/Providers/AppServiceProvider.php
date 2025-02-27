<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
    }
}

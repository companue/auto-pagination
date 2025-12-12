<?php

namespace Companue\AutoPaginate\Providers;

use Illuminate\Support\ServiceProvider;

class AutoPaginateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish AutoPaginatedController
        $this->publishes([
            __DIR__ . '/../Controllers/AutoPaginatedController.php' => app_path('Http/Controllers/API/AutoPaginatedController.php'),
        ], 'auto-paginate-controller');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Nothing to register
    }
}

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
        // Publish config if needed in future
        // $this->publishes([
        //     __DIR__.'/../../config/auto-paginate.php' => config_path('auto-paginate.php'),
        // ], 'auto-paginate-config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Merge config if needed
        // $this->mergeConfigFrom(
        //     __DIR__.'/../../config/auto-paginate.php', 'auto-paginate'
        // );
    }
}

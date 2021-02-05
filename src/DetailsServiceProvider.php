<?php

namespace TypiCMS\Modules\Details;

use TypiCMS\Modules\Details\Commands\CreateDetails;
use Illuminate\Support\ServiceProvider;

class DetailsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/details.php' => config_path('details.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/details.php', 'details');

        $this->commands([
            CreateDetails::class,
        ]);
    }
}

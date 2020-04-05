<?php

namespace YaTmch\Kick;

use Illuminate\Support\ServiceProvider;

class KickServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/kick.php', 'kick');

        $this->publishes([
            __DIR__ . '/../config/kick.php' => config_path('kick.php')
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton(Contracts\Service::class, Service::class);
    }
}

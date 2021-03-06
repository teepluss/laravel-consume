<?php

namespace Teepluss\Consume;

use Illuminate\Support\ServiceProvider;

class ConsumeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('consume.api', function ($app) {
            return new Consume($app, $app['request'], $app['router']);
        });
    }
}

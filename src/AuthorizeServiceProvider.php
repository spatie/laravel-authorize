<?php

namespace Spatie\Authorize;

use Spatie\Authorize\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AuthorizeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources/config/laravel-authorize.php' => config_path('laravel-authorize.php'),
        ], 'config');

        $this->app->bind(UnauthorizedRequestHandler::class, function (Application $app) {
            return $app->make(config('laravel-authorize.unauthorizedRequestHandler'));
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/laravel-authorize.php', 'laravel-authorize');

        $this->app[Router::class]->middleware('userCan', Authorize::class);
    }
}

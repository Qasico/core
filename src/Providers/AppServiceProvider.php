<?php

namespace Core\Providers;

use Core\Supports\Cache;
use Core\Supports\Rest;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registering Extended Validators.
     *
     * @return void
     */
    public function boot()
    {
        Rest::setRestClient($this->app['rest.client']);
        Rest::setCache(new Cache($this->app['cache.store']));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('rest.cache', function ($app) {
            return new Cache($app['cache.store']);
        });

        $this->app->singleton('rest.client', function ($app) {
            return new Client([
                'base_uri'        => 'http://' . $app['config']->get('api.host') . ':' . $app['config']->get('api.port') . '/' . $app['config']->get('api.prefix') . '/',
                'timeout'         => 20,
                'allow_redirects' => false,
            ]);
        });
    }
}

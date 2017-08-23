<?php

namespace Core\Providers;

use Illuminate\Routing\Router as BaseRouter;
use Illuminate\Routing\RoutingServiceProvider as BaseServiceProvider;

class Router extends BaseRouter
{

    /**
     * Register GET and POST route with the router.
     *
     * @param  string                     $uri
     * @param  \Closure|array|string|null $action
     * @return \Illuminate\Routing\Route
     */
    public function form($uri, $action = null)
    {
        $post_action = $get_action = $action;

        $get_action['uses'] .= '@' . 'get' . ucfirst($action['method']);
        $this->addRoute(['GET', 'HEAD'], $uri, $get_action);
        $post_action['uses'] .= '@' . 'post' . ucfirst($action['method']);

        return $this->addRoute('POST', $uri, $post_action);
    }
}

class RoutingServiceProvider extends BaseServiceProvider
{
    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });
    }
}
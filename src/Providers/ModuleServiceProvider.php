<?php

namespace Core\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Routing\Router;

abstract class ModuleServiceProvider extends RouteServiceProvider
{
    /**
     * The names of module
     * its should be unique againts other modules.
     *
     * @var string
     */
    protected $module;

    /**
     * The controller namespace for the module.
     *
     * @var string
     */
    protected $namespaces;

    /**
     * Booting router and view.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function boot()
    {
        parent::boot();

        if (!$this->module) {
            throw new \Exception("Module name should be defined.");
        }

        if (method_exists($this, 'view')) {
            $this->loadView();
        }

        if (method_exists($this, 'lang')) {
            $this->loadTranslator();
        }
    }

    /**
     * Define the routes for the module.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     *
     * @throws \Exception
     */
    public function map(Router $router)
    {
        if (method_exists($this, 'routes') && $route_files = $this->app->call([$this, 'routes'])) {
            if (!$this->namespaces) {
                throw new \Exception("Module namespaces should be defined.");
            }

            $router->group(['namespace' => $this->namespaces, 'middleware' => ['web']],
                function ($router) use ($route_files) {
                    require $route_files;
                }
            );
        }
    }

    /**
     * Registering view modules.
     *
     * @return void
     */
    protected function loadView()
    {
        if ($view_path = $this->app->call([$this, 'view'])) {
            $this->loadViewsFrom($view_path, $this->module);
        }
    }

    /**
     * Registering lang translator modules.
     *
     * @return void
     */
    protected function loadTranslator()
    {
        if ($lang_path = $this->app->call([$this, 'lang'])) {
            $this->loadTranslationsFrom($lang_path, $this->module);
        }
    }

    /**
     * Define module view path.
     *
     * @return string
     */
    abstract public function view();

    /**
     * Define module routes files.
     *
     * @return mixed
     */
    abstract public function routes();
}
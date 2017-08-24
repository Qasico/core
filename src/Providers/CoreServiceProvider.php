<?php

namespace Core\Providers;

use Illuminate\Support\AggregateServiceProvider;

class CoreServiceProvider extends AggregateServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [
        'Core\Providers\ArtisanServiceProvider',
        'Illuminate\Foundation\Providers\ComposerServiceProvider',
    ];
}
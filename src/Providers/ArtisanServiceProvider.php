<?php

namespace Core\Providers;

use Illuminate\Foundation\Providers\ArtisanServiceProvider as BaseArtisanServiceProvider;

class ArtisanServiceProvider extends BaseArtisanServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'ClearCompiled'   => 'command.clear-compiled',
        'ClearResets'     => 'command.auth.resets.clear',
        'ConfigCache'     => 'command.config.cache',
        'ConfigClear'     => 'command.config.clear',
        'Down'            => 'command.down',
        'Environment'     => 'command.environment',
        'KeyGenerate'     => 'command.key.generate',
        'PackageDiscover' => 'command.package.discover',
        'Preset'          => 'command.preset',
        'RouteCache'      => 'command.route.cache',
        'RouteClear'      => 'command.route.clear',
        'RouteList'       => 'command.route.list',
        'Up'              => 'command.up',
        'ViewClear'       => 'command.view.clear',
    ];

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $devCommands = [
        'Serve'         => 'command.serve',
        'VendorPublish' => 'command.vendor.publish',
    ];
}
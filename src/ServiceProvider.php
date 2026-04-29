<?php

namespace SimpleStatsIo\StatamicAddon;

use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'simplestats';

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/simplestats.php', 'simplestats');

        $this->app->singleton(SimplestatsApiClient::class, function ($app) {
            $config = $app['config']->get('simplestats');

            return new SimplestatsApiClient(
                apiToken: $config['api_token'] ?? null,
                apiUrl: rtrim($config['api_url'] ?? 'https://simplestats.io/api/v1', '/'),
                cacheTtl: (int) ($config['cache_ttl'] ?? 60),
            );
        });
    }

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/simplestats.php' => config_path('simplestats.php'),
        ], 'simplestats-config');

        Nav::extend(function ($nav) {
            $nav->create('SimpleStats')
                ->section('Tools')
                ->route('simplestats.dashboard')
                ->icon('chart-monitoring-indicator');
        });
    }
}

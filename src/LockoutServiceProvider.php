<?php

namespace Rappasoft\Lockout;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Rappasoft\Lockout\Http\Middleware\CheckForReadOnlyMode;

/**
 * Class LockoutServiceProvider.
 */
class LockoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lockout.php' => config_path('lockout.php'),
            ], 'config');
        }

        $this->registerBladeExtensions();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Register the config file
        $this->mergeConfigFrom(__DIR__.'/../config/lockout.php', 'lockout');

        // Publish the middleware globally
        $this->app
            ->make(Kernel::class)
            ->pushMiddleware(CheckForReadOnlyMode::class);
    }

    protected function registerBladeExtensions()
    {
        /*
         * The block of code inside this directive indicates
         * the project is currently running in read only mode.
         */
        Blade::if('readonly', function () {
            return config('lockout.enabled');
        });
    }
}

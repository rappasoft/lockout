<?php

namespace Rappasoft\Lockout;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rappasoft\Lockout\Console\Commands\LockoutDisable;
use Rappasoft\Lockout\Console\Commands\LockoutEnable;
use Rappasoft\Lockout\Console\Commands\LockoutStatus;
use Rappasoft\Lockout\Http\Middleware\CheckForReadOnlyMode;

/**
 * Class LockoutServiceProvider.
 */
class LockoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lockout.php' => config_path('lockout.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/lockout'),
            ], 'lockout-views');
        }

        $this->registerBladeExtensions();
        $this->registerHealthCheckRoute();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Register the config file
        $this->mergeConfigFrom(__DIR__.'/../config/lockout.php', 'lockout');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'lockout');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                LockoutEnable::class,
                LockoutDisable::class,
                LockoutStatus::class,
            ]);
        }

        // Publish the middleware globally
        $this->app
            ->make(Kernel::class)
            ->pushMiddleware(CheckForReadOnlyMode::class);
    }

    /**
     * Register Blade extensions.
     */
    protected function registerBladeExtensions(): void
    {
        /*
         * The block of code inside this directive indicates
         * the project is currently running in read only mode.
         */
        Blade::if('readonly', function () {
            return config('lockout.enabled');
        });
    }

    /**
     * Register health check route.
     */
    protected function registerHealthCheckRoute(): void
    {
        if (! config('lockout.health_check_enabled', true)) {
            return;
        }

        $healthCheckPath = config('lockout.health_check_path', 'health');

        Route::get($healthCheckPath, function () {
            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'lockout_enabled' => config('lockout.enabled', false),
            ]);
        })->name('lockout.health');
    }
}

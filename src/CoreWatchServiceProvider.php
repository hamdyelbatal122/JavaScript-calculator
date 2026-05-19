<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch;

use Hamzi\CoreWatch\Console\Commands\CheckHealthCommand;
use Hamzi\CoreWatch\Http\Controllers\DashboardController;
use Hamzi\CoreWatch\Livewire\CoreWatchDashboard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CoreWatchServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        // Merge package configuration with host application
        $this->mergeConfigFrom(
            __DIR__.'/../config/corewatch.php',
            'corewatch'
        );

        // Bind System Monitor & Log Parser to container
        $this->app->singleton(Services\SystemMonitor::class, function ($app) {
            return new Services\SystemMonitor;
        });

        $this->app->singleton(Services\LogParser::class, function ($app) {
            return new Services\LogParser;
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Load Blade Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'corewatch');

        // Publish Configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/corewatch.php' => config_path('corewatch.php'),
            ], 'corewatch-config');

            // Publish Views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/corewatch'),
            ], 'corewatch-views');

            // Register Commands
            $this->commands([
                CheckHealthCommand::class,
            ]);
        }

        // Register package routes
        $this->registerRoutes();

        // Register Livewire component dynamically if Livewire class is available
        if (class_exists(Livewire::class)) {
            Livewire::component('corewatch-dashboard', CoreWatchDashboard::class);
        }
    }

    /**
     * Register Dashboard & API Route bindings.
     */
    protected function registerRoutes(): void
    {
        if (! config('corewatch.enabled', true)) {
            return;
        }

        $path = config('corewatch.path', 'corewatch');
        $middleware = config('corewatch.middleware', ['web']);

        Route::prefix($path)
            ->middleware($middleware)
            ->as('corewatch.')
            ->group(function () {
                // UI Dashboard main route
                Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

                // API Endpoint: Realtime Server Hardware Metrics
                Route::get('/api/metrics', [DashboardController::class, 'metrics'])->name('api.metrics');

                // API Endpoint: Streaming Log File Stream
                Route::get('/api/logs', [DashboardController::class, 'logs'])->name('api.logs');

                // API Endpoint: Administrative service controls
                Route::post('/api/services/control', [DashboardController::class, 'controlService'])->name('api.services.control');
            });
    }
}

<?php

declare(strict_types=1);

namespace Hamzi\CoreWatch\Tests;

use Hamzi\CoreWatch\CoreWatchServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Define package service providers.
     *
     * @param  Application  $app
     * @return array<int, string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            CoreWatchServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        // Set basic config defaults for testing
        $app['config']->set('corewatch.enabled', true);
        $app['config']->set('corewatch.environments', ['testing', 'local']);
        $app['config']->set('corewatch.path', 'corewatch');
        $app['config']->set('corewatch.middleware', ['web']);
        $app['config']->set('database.default', 'testing');
    }
}

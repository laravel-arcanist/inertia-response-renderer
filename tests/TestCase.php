<?php declare(strict_types=1);

namespace Arcanist\Tests;

use Inertia\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('arcanist.route_prefix', '/wizard');
    }
}

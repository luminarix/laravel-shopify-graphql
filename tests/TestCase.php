<?php

namespace Luminarix\Skeleton\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Luminarix\Skeleton\SkeletonServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Luminarix\\Skeleton\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SkeletonServiceProvider::class,
        ];
    }
}

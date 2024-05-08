<?php

namespace Luminarix\Shopify\GraphQLClient\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Luminarix\Shopify\GraphQLClient\GraphQLClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Luminarix\\Shopify\\GraphQLClient\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            GraphQLClientServiceProvider::class,
        ];
    }
}

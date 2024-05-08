<?php

namespace Luminarix\Shopify\GraphQLClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Luminarix\Shopify\GraphQLClient\GraphQLClient
 */
class GraphQLClient extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Luminarix\Shopify\GraphQLClient\GraphQLClient::class;
    }
}

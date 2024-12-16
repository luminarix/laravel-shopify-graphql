<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Contracts\RateLimitable;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;

class GraphQLClientCreate
{
    public function create(
        AbstractAppAuthenticator $appAuthenticator,
        ?ShopifyConnector $connector = null,
        ?RateLimitable $rateLimitService = null,
    ): GraphQLClientMethods {
        return new GraphQLClientMethods($appAuthenticator, $connector, $rateLimitService);
    }
}

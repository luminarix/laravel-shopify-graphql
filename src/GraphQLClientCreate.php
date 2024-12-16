<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Contracts\RateLimitable;

class GraphQLClientCreate
{
    public function create(
        AbstractAppAuthenticator $appAuthenticator,
        ?RateLimitable $rateLimitService = null,
    ): GraphQLClientMethods {
        return new GraphQLClientMethods($appAuthenticator, $rateLimitService);
    }
}

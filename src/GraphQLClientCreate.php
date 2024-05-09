<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;

class GraphQLClientCreate
{
    public function create(
        AbstractAppAuthenticator $appAuthenticator
    ): GraphQLClientMethods {
        return new GraphQLClientMethods($appAuthenticator);
    }
}

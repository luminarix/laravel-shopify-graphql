<?php

namespace Luminarix\Shopify\GraphQLClient;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;

class GraphQLClient
{
    public function factory(): GraphQLClientCreate
    {
        return new GraphQLClientCreate();
    }
}

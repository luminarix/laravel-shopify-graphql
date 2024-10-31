<?php

namespace Luminarix\Shopify\GraphQLClient;

class GraphQLClient
{
    public function factory(): GraphQLClientCreate
    {
        return new GraphQLClientCreate;
    }
}

<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations;

use Luminarix\Shopify\GraphQLClient\Integrations\Requests\Mutation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\Query;
use Saloon\Http\Connector;
use Saloon\Http\Response;

class ShopifyResource
{
    public function __construct(
        protected Connector $connector,
    ) {
    }

    public function query(string $graphqlQuery): Response
    {
        return $this->connector->send(new Query($graphqlQuery));
    }

    public function mutation(string $graphqlQuery, array $variables): Response
    {
        return $this->connector->send(new Mutation($graphqlQuery, $variables));
    }
}

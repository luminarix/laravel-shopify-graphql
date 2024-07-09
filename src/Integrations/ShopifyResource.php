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
    ) {}

    public function query(string $graphqlQuery, bool $detailedCost = false): Response
    {
        return $this->connector->send(
            new Query($graphqlQuery, $detailedCost)
        );
    }

    public function mutation(string $graphqlQuery, array $variables, bool $detailedCost = false): Response
    {
        return $this->connector->send(
            new Mutation($graphqlQuery, $variables, $detailedCost)
        );
    }
}

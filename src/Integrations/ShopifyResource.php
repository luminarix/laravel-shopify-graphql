<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations;

use Luminarix\Shopify\GraphQLClient\Integrations\Requests\BulkOperation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\CancelBulkOperation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\CreateBulkOperation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\CurrentBulkOperation;
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

    /**
     * @param  array<mixed, mixed>  $variables
     */
    public function mutation(string $graphqlQuery, array $variables, bool $detailedCost = false): Response
    {
        return $this->connector->send(
            new Mutation($graphqlQuery, $variables, $detailedCost)
        );
    }

    public function getBulkOperation(int $id): Response
    {
        return $this->connector->send(
            new BulkOperation($id)
        );
    }

    public function currentBulkOperation(): Response
    {
        return $this->connector->send(
            new CurrentBulkOperation
        );
    }

    public function createBulkOperation(string $graphqlQuery): Response
    {
        return $this->connector->send(
            new CreateBulkOperation($graphqlQuery)
        );
    }

    public function cancelBulkOperation(string $id): Response
    {
        return $this->connector->send(
            new CancelBulkOperation($id)
        );
    }
}

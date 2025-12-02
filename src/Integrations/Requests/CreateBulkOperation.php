<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

use Luminarix\Shopify\GraphQLClient\GraphQL\BulkOperationQueries;

class CreateBulkOperation extends BaseRequest
{
    public function __construct(
        public string $graphqlQuery,
    ) {}

    protected function defaultBody(): array
    {
        return [
            'query' => BulkOperationQueries::create($this->graphqlQuery),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

use Luminarix\Shopify\GraphQLClient\GraphQL\BulkOperationQueries;

class BulkOperation extends BaseRequest
{
    public function __construct(
        public int $id,
    ) {}

    protected function defaultBody(): array
    {
        return [
            'query' => BulkOperationQueries::getById($this->id),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

use Luminarix\Shopify\GraphQLClient\GraphQL\BulkOperationQueries;

class CancelBulkOperation extends BaseRequest
{
    public function __construct(
        public string $id,
    ) {}

    protected function defaultBody(): array
    {
        return [
            'query' => BulkOperationQueries::cancel($this->id),
        ];
    }
}

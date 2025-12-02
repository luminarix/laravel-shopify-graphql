<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

use Luminarix\Shopify\GraphQLClient\GraphQL\BulkOperationQueries;

class CurrentBulkOperation extends BaseRequest
{
    protected function defaultBody(): array
    {
        return [
            'query' => BulkOperationQueries::getCurrent(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

class CurrentBulkOperation extends BaseRequest
{
    protected function defaultBody(): array
    {
        $currentBulkOperationQuery = <<<'GRAPHQL'
{
  currentBulkOperation {
    id
    status
    errorCode
    createdAt
    completedAt
    objectCount
    fileSize
    url
    partialDataUrl
  }
}
GRAPHQL;

        return [
            'query' => $currentBulkOperationQuery,
        ];
    }
}

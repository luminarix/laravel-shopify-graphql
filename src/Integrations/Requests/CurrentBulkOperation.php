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
        completedAt
        createdAt
        errorCode
        fileSize
        id
        objectCount
        partialDataUrl
        query
        rootObjectCount
        status
        type
        url
    }
}
GRAPHQL;

        return [
            'query' => $currentBulkOperationQuery,
        ];
    }
}

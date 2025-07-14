<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

class BulkOperation extends BaseRequest
{
    public function __construct(
        public int $id,
    ) {}

    protected function defaultBody(): array
    {
        $currentBulkOperationQuery = <<<GRAPHQL
query {
    node(id: "gid://shopify/BulkOperation/{$this->id}") {
        ... on BulkOperation {
            completedAt
            createdAt
            errorCode
            fileSize
            objectCount
            partialDataUrl
            query
            rootObjectCount
            status
            type
            url
        }
    }
}
GRAPHQL;

        return [
            'query' => $currentBulkOperationQuery,
        ];
    }
}

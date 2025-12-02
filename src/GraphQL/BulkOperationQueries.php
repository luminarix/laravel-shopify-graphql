<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\GraphQL;

class BulkOperationQueries
{
    private const BULK_OPERATION_FIELDS = <<<'GRAPHQL'
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
GRAPHQL;

    public static function getById(int $id): string
    {
        $fields = self::BULK_OPERATION_FIELDS;

        return <<<GRAPHQL
query {
    node(id: "gid://shopify/BulkOperation/{$id}") {
        ... on BulkOperation {
            {$fields}
        }
    }
}
GRAPHQL;
    }

    public static function getCurrent(): string
    {
        $fields = self::BULK_OPERATION_FIELDS;

        return <<<GRAPHQL
{
    currentBulkOperation {
        {$fields}
    }
}
GRAPHQL;
    }

    public static function create(string $query): string
    {
        return <<<GRAPHQL
mutation {
    bulkOperationRunQuery(
        query: """
        {$query}
        """
    ) {
        bulkOperation {
            id
            status
        }
        userErrors {
            field
            message
        }
    }
}
GRAPHQL;
    }

    public static function cancel(string $id): string
    {
        return <<<GRAPHQL
mutation {
    bulkOperationCancel(id: "{$id}") {
        bulkOperation {
            id
            status
        }
        userErrors {
            field
            message
        }
    }
}
GRAPHQL;
    }
}

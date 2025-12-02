<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Tests\Fixtures;

final class MockResponses
{
    public static function shopQuery(): array
    {
        return [
            'data' => [
                'shop' => [
                    'name' => 'Test Shop',
                    'email' => 'test@example.com',
                ],
            ],
            'extensions' => [
                'cost' => [
                    'requestedQueryCost' => 2,
                    'actualQueryCost' => 2,
                    'throttleStatus' => [
                        'maximumAvailable' => 2_000,
                        'currentlyAvailable' => 1_998,
                        'restoreRate' => 100,
                    ],
                ],
            ],
        ];
    }

    public static function productsQuery(): array
    {
        return [
            'data' => [
                'products' => [
                    'edges' => [
                        [
                            'node' => [
                                'id' => 'gid://shopify/Product/1',
                                'title' => 'Test Product',
                            ],
                            'cursor' => 'cursor1',
                        ],
                    ],
                    'pageInfo' => [
                        'hasNextPage' => false,
                    ],
                ],
            ],
            'extensions' => [
                'cost' => [
                    'requestedQueryCost' => 10,
                    'actualQueryCost' => 5,
                    'throttleStatus' => [
                        'maximumAvailable' => 2_000,
                        'currentlyAvailable' => 1_995,
                        'restoreRate' => 100,
                    ],
                ],
            ],
        ];
    }

    public static function productCreateMutation(): array
    {
        return [
            'data' => [
                'productCreate' => [
                    'product' => [
                        'id' => 'gid://shopify/Product/123',
                        'title' => 'New Product',
                    ],
                    'userErrors' => [],
                ],
            ],
            'extensions' => [
                'cost' => [
                    'requestedQueryCost' => 10,
                    'actualQueryCost' => 10,
                    'throttleStatus' => [
                        'maximumAvailable' => 2_000,
                        'currentlyAvailable' => 1_990,
                        'restoreRate' => 100,
                    ],
                ],
            ],
        ];
    }

    public static function throttledResponse(): array
    {
        return [
            'errors' => [
                [
                    'message' => 'Throttled',
                    'extensions' => [
                        'code' => 'THROTTLED',
                    ],
                ],
            ],
            'extensions' => [
                'cost' => [
                    'requestedQueryCost' => 100,
                    'actualQueryCost' => null,
                    'throttleStatus' => [
                        'maximumAvailable' => 2_000,
                        'currentlyAvailable' => 0,
                        'restoreRate' => 100,
                    ],
                ],
            ],
        ];
    }

    public static function graphqlError(): array
    {
        return [
            'errors' => [
                [
                    'message' => 'Field "invalid" does not exist on type "Shop"',
                    'extensions' => [
                        'code' => 'GRAPHQL_VALIDATION_FAILED',
                    ],
                ],
            ],
        ];
    }

    public static function bulkOperationCreated(): array
    {
        return [
            'data' => [
                'bulkOperationRunQuery' => [
                    'bulkOperation' => [
                        'id' => 'gid://shopify/BulkOperation/123456',
                        'status' => 'CREATED',
                    ],
                    'userErrors' => [],
                ],
            ],
        ];
    }

    public static function bulkOperationRunning(): array
    {
        return [
            'data' => [
                'node' => [
                    'id' => 'gid://shopify/BulkOperation/123456',
                    'status' => 'RUNNING',
                    'objectCount' => '100',
                    'url' => null,
                ],
            ],
        ];
    }

    public static function bulkOperationCompleted(): array
    {
        return [
            'data' => [
                'node' => [
                    'id' => 'gid://shopify/BulkOperation/123456',
                    'status' => 'COMPLETED',
                    'objectCount' => '500',
                    'url' => 'https://storage.googleapis.com/shopify/bulk-operation.jsonl',
                ],
            ],
        ];
    }

    public static function currentBulkOperationRunning(): array
    {
        return [
            'data' => [
                'currentBulkOperation' => [
                    'id' => 'gid://shopify/BulkOperation/123456',
                    'status' => 'RUNNING',
                ],
            ],
        ];
    }

    public static function currentBulkOperationNull(): array
    {
        return [
            'data' => [
                'currentBulkOperation' => null,
            ],
        ];
    }

    public static function bulkOperationCancelled(): array
    {
        return [
            'data' => [
                'bulkOperationCancel' => [
                    'bulkOperation' => [
                        'id' => 'gid://shopify/BulkOperation/123456',
                        'status' => 'CANCELING',
                    ],
                    'userErrors' => [],
                ],
            ],
        ];
    }
}

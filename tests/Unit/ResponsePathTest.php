<?php

declare(strict_types=1);

use Luminarix\Shopify\GraphQLClient\Enums\ResponsePath;

it('extracts data path', function () {
    $response = [
        'data' => ['shop' => ['name' => 'Test Shop']],
    ];

    expect(ResponsePath::Data->extract($response))->toBe(['shop' => ['name' => 'Test Shop']]);
});

it('extracts bulk operation node path', function () {
    $response = [
        'data' => [
            'node' => ['id' => 'gid://shopify/BulkOperation/123', 'status' => 'COMPLETED'],
        ],
    ];

    expect(ResponsePath::BulkOperationNode->extract($response))
        ->toBe(['id' => 'gid://shopify/BulkOperation/123', 'status' => 'COMPLETED']);
});

it('extracts current bulk operation path', function () {
    $response = [
        'data' => [
            'currentBulkOperation' => ['id' => 'gid://shopify/BulkOperation/123'],
        ],
    ];

    expect(ResponsePath::CurrentBulkOperation->extract($response))
        ->toBe(['id' => 'gid://shopify/BulkOperation/123']);
});

it('extracts bulk operation run query path', function () {
    $response = [
        'data' => [
            'bulkOperationRunQuery' => [
                'bulkOperation' => ['id' => 'gid://shopify/BulkOperation/123'],
            ],
        ],
    ];

    expect(ResponsePath::BulkOperationRunQuery->extract($response))
        ->toBe(['bulkOperation' => ['id' => 'gid://shopify/BulkOperation/123']]);
});

it('extracts bulk operation cancel path', function () {
    $response = [
        'data' => [
            'bulkOperationCancel' => [
                'bulkOperation' => ['id' => 'gid://shopify/BulkOperation/123', 'status' => 'CANCELING'],
            ],
        ],
    ];

    expect(ResponsePath::BulkOperationCancel->extract($response))
        ->toBe(['bulkOperation' => ['id' => 'gid://shopify/BulkOperation/123', 'status' => 'CANCELING']]);
});

it('returns empty array for missing path', function () {
    $response = ['data' => []];

    expect(ResponsePath::BulkOperationNode->extract($response))->toBe([]);
});

it('returns empty array for null value', function () {
    $response = ['data' => ['node' => null]];

    expect(ResponsePath::BulkOperationNode->extract($response))->toBe([]);
});

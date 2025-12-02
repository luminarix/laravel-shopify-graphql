<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Enums;

enum ResponsePath: string
{
    case Data = 'data';
    case BulkOperationNode = 'data.node';
    case CurrentBulkOperation = 'data.currentBulkOperation';
    case BulkOperationRunQuery = 'data.bulkOperationRunQuery';
    case BulkOperationCancel = 'data.bulkOperationCancel';

    public function extract(array $response): array
    {
        /** @var ?array $data */
        $data = data_get($response, $this->value, []);

        return $data ?? [];
    }
}

<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

class CancelBulkOperation extends BaseRequest
{
    public function __construct(
        public string $id,
    ) {}

    protected function defaultBody(): array
    {
        $bulkOperation = <<<GRAPHQL
mutation {
  bulkOperationCancel(id: "{$this->id}") {
    bulkOperation {
      status
    }
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;

        return [
            'query' => $bulkOperation,
        ];
    }
}

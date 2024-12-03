<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

class CreateBulkOperation extends BaseRequest
{
    public function __construct(
        public string $graphqlQuery,
    ) {}

    protected function defaultBody(): array
    {
        $bulkOperation = <<<GRAPHQL
mutation {
  bulkOperationRunQuery(
   query: """
    {$this->graphqlQuery}
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

        return [
            'query' => $bulkOperation,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

class Query extends BaseRequest
{
    public function __construct(
        public string $graphqlQuery,
        public bool $detailedCost = false
    ) {}

    protected function defaultBody(): array
    {
        return [
            'query' => $this->graphqlQuery,
        ];
    }
}

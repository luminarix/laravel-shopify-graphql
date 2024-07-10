<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

class Mutation extends BaseRequest
{
    /**
     * @param  mixed[]  $variables
     */
    public function __construct(
        public string $graphqlQuery,
        public array $variables,
        public bool $detailedCost = false
    ) {}

    protected function defaultBody(): array
    {
        return [
            'query' => $this->graphqlQuery,
            'variables' => $this->variables,
        ];
    }
}

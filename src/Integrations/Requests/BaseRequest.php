<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class BaseRequest extends Request implements HasBody
{
    use HasJsonBody;

    public bool $detailedCost = false;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/graphql.json';
    }

    protected function defaultHeaders(): array
    {
        return $this->detailedCost ? $this->detailedCost() : [];
    }

    private function detailedCost(): array
    {
        return [
            'X-GraphQL-Cost-Include-Fields' => 'true',
        ];
    }
}

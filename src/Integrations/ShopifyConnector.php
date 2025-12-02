<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Contracts\ThrottleDetectable;
use Luminarix\Shopify\GraphQLClient\Services\ThrottleDetector;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;

class ShopifyConnector extends Connector
{
    private readonly ThrottleDetectable $throttleDetector;

    public function __construct(
        public AbstractAppAuthenticator $appAuthenticator,
        ?ThrottleDetectable $throttleDetector = null,
    ) {
        $this->throttleDetector = $throttleDetector ?? new ThrottleDetector;
    }

    public function resolveBaseUrl(): string
    {
        return "https://{$this->appAuthenticator->getShopDomain()}/admin/api/{$this->appAuthenticator->getApiVersion()}";
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        if ($response->serverError() || $response->clientError()) {
            return true;
        }

        $responseJson = $response->json();
        $isThrottled = $this->throttleDetector->isThrottled($responseJson);

        if ($isThrottled && config('shopify-graphql.fail_on_throttled')) {
            return true;
        }

        return $this->hasGraphQLErrors($responseJson, $isThrottled);
    }

    protected function defaultAuth(): HeaderAuthenticator
    {
        return new HeaderAuthenticator($this->appAuthenticator->accessToken, 'X-Shopify-Access-Token');
    }

    private function hasGraphQLErrors(array $responseJson, bool $isThrottled): bool
    {
        $hasErrors = data_get($responseJson, 'errors') !== null;

        /** @var array $bulkOperationUserErrors */
        $bulkOperationUserErrors = data_get($responseJson, 'data.bulkOperationRunQuery.userErrors', []);

        return ($hasErrors && !$isThrottled) || $bulkOperationUserErrors !== [];
    }
}

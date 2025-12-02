<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Illuminate\Support\Traits\Macroable;
use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Contracts\RateLimitable;
use Luminarix\Shopify\GraphQLClient\Contracts\ThrottleDetectable;
use Luminarix\Shopify\GraphQLClient\Enums\ResponsePath;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientRequestFailedException;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\BulkOperation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\CancelBulkOperation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\CreateBulkOperation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\CurrentBulkOperation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\Mutation;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\Query;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;
use Luminarix\Shopify\GraphQLClient\Services\QueryTransformer;
use Luminarix\Shopify\GraphQLClient\Services\RedisRateLimitService;
use Luminarix\Shopify\GraphQLClient\Services\RequestExecutor;
use Luminarix\Shopify\GraphQLClient\Services\ThrottleDetector;

class GraphQLClientMethods
{
    use Macroable;

    private readonly RequestExecutor $executor;

    public function __construct(
        AbstractAppAuthenticator $appAuthenticator,
        ?RateLimitable $rateLimitService = null,
        ?ThrottleDetectable $throttleDetector = null,
    ) {
        $connector = new ShopifyConnector($appAuthenticator);
        $rateLimitService ??= new RedisRateLimitService($appAuthenticator->getShopDomain());
        $throttleDetector ??= new ThrottleDetector;

        $this->executor = new RequestExecutor($connector, $rateLimitService, $throttleDetector);
    }

    /**
     * @throws ClientRequestFailedException
     */
    public function query(
        string $query,
        bool $withExtensions = false,
        bool $detailedCost = false,
        array $paginationConfig = [],
    ): GraphQLClientTransformer {
        $query = $this->applyPagination($query, $paginationConfig);

        $request = new Query($query, $detailedCost);
        $response = $this->executor->execute($request);

        return $this->transformResponse($response, ResponsePath::Data, $withExtensions);
    }

    /**
     * @throws ClientRequestFailedException
     */
    public function mutate(
        string $query,
        array $variables,
        bool $withExtensions = false,
        bool $detailedCost = false,
        array $paginationConfig = [],
    ): GraphQLClientTransformer {
        $query = $this->applyPagination($query, $paginationConfig);

        $request = new Mutation($query, $variables, $detailedCost);
        $response = $this->executor->execute($request);

        return $this->transformResponse($response, ResponsePath::Data, $withExtensions);
    }

    /**
     * @throws ClientRequestFailedException
     */
    public function getBulkOperation(int $id): GraphQLClientTransformer
    {
        $request = new BulkOperation($id);
        $response = $this->executor->execute($request, trackRateLimits: false);

        return $this->transformResponse($response, ResponsePath::BulkOperationNode);
    }

    /**
     * @throws ClientRequestFailedException
     */
    public function getCurrentBulkOperation(): GraphQLClientTransformer
    {
        $request = new CurrentBulkOperation;
        $response = $this->executor->execute($request, trackRateLimits: false);

        return $this->transformResponse($response, ResponsePath::CurrentBulkOperation);
    }

    /**
     * @throws ClientRequestFailedException
     */
    public function createBulkOperation(string $query): GraphQLClientTransformer
    {
        $request = new CreateBulkOperation($query);
        $response = $this->executor->execute($request, trackRateLimits: false);

        return $this->transformResponse($response, ResponsePath::BulkOperationRunQuery);
    }

    /**
     * @throws ClientRequestFailedException
     */
    public function cancelBulkOperation(?int $id = null): GraphQLClientTransformer
    {
        $bulkOperation = $this->resolveBulkOperationForCancellation($id);
        $this->validateBulkOperationCancellable($bulkOperation);

        /** @var string $bulkOperationId */
        $bulkOperationId = data_get($bulkOperation, 'id');

        $request = new CancelBulkOperation($bulkOperationId);
        $response = $this->executor->execute($request, trackRateLimits: false);

        return $this->transformResponse($response, ResponsePath::BulkOperationCancel);
    }

    public function getRateLimitInfo(): array
    {
        return $this->executor->getLastRateLimitState()?->toArray() ?? [
            'requestedQueryCost' => null,
            'actualQueryCost' => null,
            'maxAvailableLimit' => null,
            'lastAvailableLimit' => null,
            'restoreRate' => null,
            'isThrottled' => false,
        ];
    }

    private function applyPagination(string $query, array $paginationConfig): string
    {
        if (empty($paginationConfig)) {
            return $query;
        }

        return QueryTransformer::transformQueryWithPagination($query, $paginationConfig);
    }

    private function transformResponse(
        array $response,
        ResponsePath $path,
        bool $withExtensions = false,
    ): GraphQLClientTransformer {
        if ($withExtensions) {
            return new GraphQLClientTransformer(array_filter($response));
        }

        return new GraphQLClientTransformer($path->extract($response));
    }

    /**
     * @throws ClientRequestFailedException
     */
    private function resolveBulkOperationForCancellation(?int $id): array
    {
        if ($id !== null) {
            return $this->getBulkOperation($id)->toArray();
        }

        return $this->getCurrentBulkOperation()->toArray();
    }

    /**
     * @throws ClientRequestFailedException
     */
    private function validateBulkOperationCancellable(array $bulkOperation): void
    {
        /** @var ?string $bulkOperationId */
        $bulkOperationId = data_get($bulkOperation, 'id');
        /** @var ?string $status */
        $status = data_get($bulkOperation, 'status');

        $canCancel = $bulkOperationId !== null && in_array($status, ['CREATED', 'RUNNING'], true);

        throw_if(!$canCancel, ClientRequestFailedException::class, 'There is no bulk operation to cancel.');
    }
}

<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Contracts;

interface QueryTransformable
{
    public static function transformQueryWithPagination(string $queryString, array $paginationConfig): string;
}

<?php

declare(strict_types=1);

use Luminarix\Shopify\GraphQLClient\Services\QueryTransformer;

it('returns query unchanged when pagination config is empty', function () {
    $query = '{ shop { name } }';

    $result = QueryTransformer::transformQueryWithPagination($query, []);

    expect($result)->toBe($query);
});

it('applies first argument to connection field', function () {
    $query = '{ products { edges { node { id } } } }';

    $result = QueryTransformer::transformQueryWithPagination($query, [
        'products' => ['first' => 10],
    ]);

    expect($result)->toContain('products(first: 10)');
});

it('applies after cursor for pagination', function () {
    $query = '{ products { edges { node { id } } } }';

    $result = QueryTransformer::transformQueryWithPagination($query, [
        'products' => ['first' => 10, 'after' => 'cursor123'],
    ]);

    expect($result)->toContain('first: 10')
        ->and($result)->toContain('after: "cursor123"');
});

it('applies pagination to nested fields', function () {
    $query = '{ shop { products { edges { node { id } } } } }';

    $result = QueryTransformer::transformQueryWithPagination($query, [
        'shop.products' => ['first' => 5],
    ]);

    expect($result)->toContain('products(first: 5)');
});

it('applies multiple pagination configs', function () {
    $query = '{ products { edges { node { id variants { edges { node { id } } } } } } }';

    $result = QueryTransformer::transformQueryWithPagination($query, [
        'products' => ['first' => 10],
        'products.edges.node.variants' => ['first' => 5],
    ]);

    expect($result)->toContain('products(first: 10)')
        ->and($result)->toContain('variants(first: 5)');
});

it('handles boolean values in pagination config', function () {
    $query = '{ products { edges { node { id } } } }';

    $result = QueryTransformer::transformQueryWithPagination($query, [
        'products' => ['first' => 10, 'reverse' => true],
    ]);

    expect($result)->toContain('first: 10')
        ->and($result)->toContain('reverse: true');
});

it('handles null values in pagination config', function () {
    $query = '{ products { edges { node { id } } } }';

    $result = QueryTransformer::transformQueryWithPagination($query, [
        'products' => ['first' => 10, 'query' => null],
    ]);

    expect($result)->toContain('first: 10')
        ->and($result)->toContain('query: null');
});

it('handles array values in pagination config', function () {
    $query = '{ products { edges { node { id } } } }';

    $result = QueryTransformer::transformQueryWithPagination($query, [
        'products' => ['first' => 10, 'sortKey' => 'TITLE'],
    ]);

    expect($result)->toContain('first: 10')
        ->and($result)->toContain('sortKey: "TITLE"');
});

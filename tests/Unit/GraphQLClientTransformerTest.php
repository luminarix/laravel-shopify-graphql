<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Luminarix\Shopify\GraphQLClient\GraphQLClientTransformer;

it('converts to array', function () {
    $data = ['shop' => ['name' => 'Test Shop']];
    $transformer = new GraphQLClientTransformer($data);

    expect($transformer->toArray())->toBe($data);
});

it('converts to collection', function () {
    $data = ['shop' => ['name' => 'Test Shop']];
    $transformer = new GraphQLClientTransformer($data);

    expect($transformer->toCollection())
        ->toBeInstanceOf(Collection::class)
        ->and($transformer->toCollection()->toArray())->toBe($data);
});

it('converts to fluent', function () {
    $data = ['shop' => ['name' => 'Test Shop']];
    $transformer = new GraphQLClientTransformer($data);

    expect($transformer->toFluent())
        ->toBeInstanceOf(Fluent::class)
        ->and($transformer->toFluent()->get('shop'))->toBe(['name' => 'Test Shop']);
});

it('converts to json', function () {
    $data = ['shop' => ['name' => 'Test Shop']];
    $transformer = new GraphQLClientTransformer($data);

    expect($transformer->toJson())->toBe('{"shop":{"name":"Test Shop"}}');
});

it('throws on invalid json depth', function () {
    $transformer = new GraphQLClientTransformer([]);

    $transformer->toJson(depth: 0);
})->throws(InvalidArgumentException::class, 'Depth must be greater than 0');

it('converts to dto', function () {
    $data = ['name' => 'Test Shop'];
    $transformer = new GraphQLClientTransformer($data);

    $dto = $transformer->toDTO(TestDto::class);

    expect($dto)->toBeInstanceOf(TestDto::class)
        ->and($dto->data)->toBe($data);
});

class TestDto
{
    public function __construct(public array $data) {}
}

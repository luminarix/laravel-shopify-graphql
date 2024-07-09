<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

readonly class GraphQLClientTransformer
{
    public function __construct(private array $data) {}

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return json_encode($this->data, $flags, $depth);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $dto
     * @return T
     */
    public function toDTO(string $dto)
    {
        return new $dto($this->data);
    }
}

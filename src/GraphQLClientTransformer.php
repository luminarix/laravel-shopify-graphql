<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use InvalidArgumentException;

readonly class GraphQLClientTransformer
{
    /**
     * @param  array<mixed, mixed>  $data
     */
    public function __construct(private array $data) {}

    /**
     * @return array<mixed, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(int $flags = 0, int $depth = 512): string
    {
        if ($depth < 1) {
            throw new InvalidArgumentException('Depth must be greater than 0');
        }

        $jsonString = json_encode($this->data, $flags, $depth);

        return $jsonString === false
            ? ''
            : $jsonString;
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

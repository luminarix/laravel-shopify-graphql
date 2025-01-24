<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use InvalidArgumentException;

readonly class GraphQLClientTransformer
{
    public function __construct(private ?array $data) {}

    public function toArray(): ?array
    {
        return $this->data;
    }

    public function toFluent(): Fluent
    {
        return fluent($this->data ?? []);
    }

    public function toCollection(): Collection
    {
        return collect($this->data);
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

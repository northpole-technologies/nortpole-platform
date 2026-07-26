<?php

declare(strict_types=1);

namespace Northpole\Runtime\Search\Contracts;

interface RuntimeSearchServiceContract
{
    /**
     * @return array<int, array{
     *     registry: string,
     *     module: string,
     *     key: string,
     *     value: mixed
     * }>
     */
    public function search(
        string $query,
        ?string $module = null,
    ): array;
}
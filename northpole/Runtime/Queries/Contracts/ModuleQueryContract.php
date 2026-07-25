<?php

declare(strict_types=1);

namespace Northpole\Runtime\Queries\Contracts;

use DateTimeImmutable;

interface ModuleQueryContract
{
    public function name(): string;

    public function sourceModule(): string;

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;

    public function requestedAt(): DateTimeImmutable;
}

<?php

declare(strict_types=1);

namespace Northpole\Runtime\Agents\Contracts;

use DateTimeImmutable;

interface ModuleAgentContract
{
    public function name(): string;

    public function sourceModule(): string;

    /**
     * @return array<string, mixed>
     */
    public function input(): array;

    /**
     * @return array<string, mixed>
     */
    public function context(): array;

    public function requestedAt(): DateTimeImmutable;
}
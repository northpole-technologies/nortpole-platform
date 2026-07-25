<?php

declare(strict_types=1);

namespace Northpole\Runtime\Commands\Contracts;

use DateTimeImmutable;

interface ModuleCommandContract
{
    public function name(): string;

    public function sourceModule(): string;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;

    public function issuedAt(): DateTimeImmutable;
}

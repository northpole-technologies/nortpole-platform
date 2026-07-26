<?php

declare(strict_types=1);

namespace Northpole\Runtime\Notifications\Contracts;

use DateTimeImmutable;

interface ModuleNotificationContract
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

    public function sentAt(): DateTimeImmutable;
}
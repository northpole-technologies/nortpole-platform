<?php

declare(strict_types=1);

namespace Northpole\Runtime\Commands;

use DateTimeImmutable;
use InvalidArgumentException;
use Northpole\Runtime\Commands\Contracts\ModuleCommandContract;

final class ModuleCommand implements ModuleCommandContract
{
    private readonly DateTimeImmutable $issuedAt;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private readonly string $name,
        private readonly string $sourceModule,
        private readonly array $payload = [],
        private readonly array $metadata = [],
        ?DateTimeImmutable $issuedAt = null,
    ) {
        $this->issuedAt = $issuedAt
            ?? new DateTimeImmutable;

        $this->validate();
    }

    private function validate(): void
    {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A module command name cannot be empty.'
            );
        }

        if (trim($this->sourceModule) === '') {
            throw new InvalidArgumentException(
                'A module command source module cannot be empty.'
            );
        }
    }

    public function name(): string
    {
        return trim($this->name);
    }

    public function sourceModule(): string
    {
        return trim($this->sourceModule);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }
}

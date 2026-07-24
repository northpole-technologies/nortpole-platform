<?php

declare(strict_types=1);

namespace Northpole\Runtime\Events;

use DateTimeImmutable;
use InvalidArgumentException;
use Northpole\Runtime\Events\Contracts\ModuleEventContract;

final class ModuleEvent implements ModuleEventContract
{
    private readonly DateTimeImmutable $occurredAt;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private readonly string $name,
        private readonly string $sourceModule,
        private readonly array $payload = [],
        private readonly array $metadata = [],
        ?DateTimeImmutable $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt
            ?? new DateTimeImmutable();

        $this->validate();
    }

    private function validate(): void
    {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A module event name cannot be empty.'
            );
        }

        if (trim($this->sourceModule) === '') {
            throw new InvalidArgumentException(
                'A module event source module cannot be empty.'
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

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
<?php

declare(strict_types=1);

namespace Northpole\Runtime\Agents;

use DateTimeImmutable;
use InvalidArgumentException;
use Northpole\Runtime\Agents\Contracts\ModuleAgentContract;

final class ModuleAgent implements ModuleAgentContract
{
    private readonly DateTimeImmutable $requestedAt;

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        private readonly string $name,
        private readonly string $sourceModule,
        private readonly array $input = [],
        private readonly array $context = [],
        ?DateTimeImmutable $requestedAt = null,
    ) {
        $this->requestedAt = $requestedAt
            ?? new DateTimeImmutable;

        $this->validate();
    }

    private function validate(): void
    {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A module agent name cannot be empty.'
            );
        }

        if (trim($this->sourceModule) === '') {
            throw new InvalidArgumentException(
                'A module agent source module cannot be empty.'
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
    public function input(): array
    {
        return $this->input;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    public function requestedAt(): DateTimeImmutable
    {
        return $this->requestedAt;
    }
}
<?php

declare(strict_types=1);

namespace Northpole\Runtime\Queries;

use InvalidArgumentException;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;

final class ModuleQueryRegistry
{
    /**
     * @var array<string, class-string<ModuleQueryHandlerContract>>
     */
    private array $handlers = [];

    /**
     * @var array<string, string>
     */
    private array $owners = [];

    /**
     * @param  class-string<ModuleQueryHandlerContract>  $handler
     */
    public function register(
        string $queryName,
        string $handler,
        string $module = 'platform',
    ): self {
        $queryName = trim($queryName);
        $handler = trim($handler);
        $module = trim($module);

        if ($queryName === '') {
            throw new InvalidArgumentException(
                'A module query name cannot be empty.'
            );
        }

        if ($handler === '') {
            throw new InvalidArgumentException(
                'A module query handler class cannot be empty.'
            );
        }

        if ($module === '') {
            throw new InvalidArgumentException(
                'A module query handler owner cannot be empty.'
            );
        }

        if (isset($this->handlers[$queryName])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Query [%s] already has handler [%s] registered by module [%s].',
                    $queryName,
                    $this->handlers[$queryName],
                    $this->owners[$queryName],
                )
            );
        }

        $this->handlers[$queryName] = $handler;
        $this->owners[$queryName] = $module;

        return $this;
    }

    /**
     * @return class-string<ModuleQueryHandlerContract>|null
     */
    public function handler(
        string $queryName,
    ): ?string {
        $queryName = trim($queryName);

        if ($queryName === '') {
            return null;
        }

        return $this->handlers[$queryName] ?? null;
    }

    public function owner(
        string $queryName,
    ): ?string {
        $queryName = trim($queryName);

        if ($queryName === '') {
            return null;
        }

        return $this->owners[$queryName] ?? null;
    }

    public function hasHandler(
        string $queryName,
    ): bool {
        return $this->handler($queryName) !== null;
    }

    /**
     * @return array<string, class-string<ModuleQueryHandlerContract>>
     */
    public function all(): array
    {
        $handlers = $this->handlers;

        ksort($handlers);

        return $handlers;
    }

    /**
     * @return array<string, string>
     */
    public function owners(): array
    {
        $owners = $this->owners;

        ksort($owners);

        return $owners;
    }

    public function count(): int
    {
        return count($this->handlers);
    }

    public function clear(): self
    {
        $this->handlers = [];
        $this->owners = [];

        return $this;
    }
}

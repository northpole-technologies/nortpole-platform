<?php

declare(strict_types=1);

namespace Northpole\Runtime\Commands;

use InvalidArgumentException;
use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;

final class ModuleCommandRegistry
{
    /**
     * @var array<string, class-string<ModuleCommandHandlerContract>>
     */
    private array $handlers = [];

    /**
     * @var array<string, string>
     */
    private array $owners = [];

    /**
     * @param class-string<ModuleCommandHandlerContract> $handler
     */
    public function register(
        string $commandName,
        string $handler,
        string $module = 'platform',
    ): self {
        $commandName = trim($commandName);
        $handler = trim($handler);
        $module = trim($module);

        if ($commandName === '') {
            throw new InvalidArgumentException(
                'A module command name cannot be empty.'
            );
        }

        if ($handler === '') {
            throw new InvalidArgumentException(
                'A module command handler class cannot be empty.'
            );
        }

        if ($module === '') {
            throw new InvalidArgumentException(
                'A module command handler owner cannot be empty.'
            );
        }

        if (isset($this->handlers[$commandName])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Command [%s] already has handler [%s] registered by module [%s].',
                    $commandName,
                    $this->handlers[$commandName],
                    $this->owners[$commandName],
                )
            );
        }

        $this->handlers[$commandName] = $handler;
        $this->owners[$commandName] = $module;

        return $this;
    }

    /**
     * @return class-string<ModuleCommandHandlerContract>|null
     */
    public function handler(
        string $commandName,
    ): ?string {
        $commandName = trim($commandName);

        if ($commandName === '') {
            return null;
        }

        return $this->handlers[$commandName] ?? null;
    }

    public function owner(
        string $commandName,
    ): ?string {
        $commandName = trim($commandName);

        if ($commandName === '') {
            return null;
        }

        return $this->owners[$commandName] ?? null;
    }

    public function hasHandler(
        string $commandName,
    ): bool {
        return $this->handler($commandName) !== null;
    }

    /**
     * @return array<string, class-string<ModuleCommandHandlerContract>>
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
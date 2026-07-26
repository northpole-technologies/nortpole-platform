<?php

declare(strict_types=1);

namespace Northpole\Runtime\Agents;

use InvalidArgumentException;
use Northpole\Runtime\Agents\Contracts\ModuleAgentHandlerContract;

final class ModuleAgentRegistry
{
    /**
     * @var array<string, class-string<ModuleAgentHandlerContract>>
     */
    private array $handlers = [];

    /**
     * @var array<string, string>
     */
    private array $owners = [];

    /**
     * @param  class-string<ModuleAgentHandlerContract>  $handler
     */
    public function register(
        string $agentName,
        string $handler,
        string $module = 'platform',
    ): self {
        $agentName = trim($agentName);
        $handler = trim($handler);
        $module = trim($module);

        if ($agentName === '') {
            throw new InvalidArgumentException(
                'A module agent name cannot be empty.'
            );
        }

        if ($handler === '') {
            throw new InvalidArgumentException(
                'A module agent handler class cannot be empty.'
            );
        }

        if ($module === '') {
            throw new InvalidArgumentException(
                'A module agent handler owner cannot be empty.'
            );
        }

        if (isset($this->handlers[$agentName])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Agent [%s] already has handler [%s] registered by module [%s].',
                    $agentName,
                    $this->handlers[$agentName],
                    $this->owners[$agentName],
                )
            );
        }

        $this->handlers[$agentName] = $handler;
        $this->owners[$agentName] = $module;

        return $this;
    }

    /**
     * @return class-string<ModuleAgentHandlerContract>|null
     */
    public function handler(
        string $agentName,
    ): ?string {
        $agentName = trim($agentName);

        if ($agentName === '') {
            return null;
        }

        return $this->handlers[$agentName] ?? null;
    }

    public function owner(
        string $agentName,
    ): ?string {
        $agentName = trim($agentName);

        if ($agentName === '') {
            return null;
        }

        return $this->owners[$agentName] ?? null;
    }

    public function hasHandler(
        string $agentName,
    ): bool {
        return $this->handler($agentName) !== null;
    }

    /**
     * @return array<string, class-string<ModuleAgentHandlerContract>>
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
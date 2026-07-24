<?php

declare(strict_types=1);

namespace Northpole\Runtime\Permissions;

use InvalidArgumentException;

final readonly class Permission
{
    public function __construct(
        private string $moduleSlug,
        private string $name,
    ) {
        if (trim($this->moduleSlug) === '') {
            throw new InvalidArgumentException(
                'A permission must have a module slug.'
            );
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A permission must have a name.'
            );
        }
    }

    public static function fromString(
        string $moduleSlug,
        string $name,
    ): self {
        return new self(
            moduleSlug: trim($moduleSlug),
            name: trim($name),
        );
    }

    public function moduleSlug(): string
    {
        return $this->moduleSlug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function key(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'module' => $this->moduleSlug,
            'name' => $this->name,
        ];
    }
}
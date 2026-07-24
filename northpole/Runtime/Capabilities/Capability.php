<?php

declare(strict_types=1);

namespace Northpole\Runtime\Capabilities;

use InvalidArgumentException;

final readonly class Capability
{
    public function __construct(
        private string $moduleSlug,
        private string $name,
    ) {
        if (trim($this->moduleSlug) === '') {
            throw new InvalidArgumentException(
                'A capability must have a module slug.'
            );
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A capability must have a name.'
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
        return implode(
            ':',
            [
                $this->moduleSlug,
                $this->name,
            ]
        );
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
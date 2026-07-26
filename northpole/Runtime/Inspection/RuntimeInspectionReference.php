<?php

declare(strict_types=1);

namespace Northpole\Runtime\Inspection;

use InvalidArgumentException;

final readonly class RuntimeInspectionReference
{
    public string $registry;

    public string $module;

    public string $key;

    public function __construct(
        string $registry,
        string $module,
        string $key,
    ) {
        $registry = strtolower(
            trim($registry),
        );

        $module = strtolower(
            trim($module),
        );

        $key = trim($key);

        if ($registry === '') {
            throw new InvalidArgumentException(
                'A runtime inspection registry cannot be empty.',
            );
        }

        if ($module === '') {
            throw new InvalidArgumentException(
                'A runtime inspection module cannot be empty.',
            );
        }

        if ($key === '') {
            throw new InvalidArgumentException(
                'A runtime inspection key cannot be empty.',
            );
        }

        $this->registry = $registry;
        $this->module = $module;
        $this->key = $key;
    }

    public function identifier(): string
    {
        return implode(
            ':',
            [
                $this->registry,
                $this->module,
                $this->key,
            ],
        );
    }

    /**
     * @return array{
     *     registry: string,
     *     module: string,
     *     key: string
     * }
     */
    public function toArray(): array
    {
        return [
            'registry' => $this->registry,
            'module' => $this->module,
            'key' => $this->key,
        ];
    }
}

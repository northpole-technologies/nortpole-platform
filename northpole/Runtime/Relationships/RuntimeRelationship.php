<?php

declare(strict_types=1);

namespace Northpole\Runtime\Relationships;

use InvalidArgumentException;
use Northpole\Runtime\Inspection\RuntimeInspectionReference;

final readonly class RuntimeRelationship
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $type,
        public string $label,
        public string $value,
        public ?RuntimeInspectionReference $target = null,
        public array $metadata = [],
    ) {
        if (trim($this->type) === '') {
            throw new InvalidArgumentException(
                'A runtime relationship type cannot be empty.',
            );
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException(
                'A runtime relationship label cannot be empty.',
            );
        }

        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'A runtime relationship value cannot be empty.',
            );
        }
    }

    /**
     * @return array{
     *     type: string,
     *     label: string,
     *     value: string,
     *     target: array{
     *         registry: string,
     *         module: string,
     *         key: string
     *     }|null,
     *     metadata: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'type' => trim($this->type),
            'label' => trim($this->label),
            'value' => trim($this->value),
            'target' => $this->target?->toArray(),
            'metadata' => $this->metadata,
        ];
    }
}

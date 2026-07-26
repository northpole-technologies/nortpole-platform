<?php

declare(strict_types=1);

namespace Northpole\Runtime\Graph;

use InvalidArgumentException;

final readonly class RuntimeGraphEdge
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $source,
        public string $target,
        public string $type,
        public string $label,
        public array $metadata = [],
    ) {
        if (trim($this->source) === '') {
            throw new InvalidArgumentException(
                'A runtime graph edge source cannot be empty.',
            );
        }

        if (trim($this->target) === '') {
            throw new InvalidArgumentException(
                'A runtime graph edge target cannot be empty.',
            );
        }

        if (trim($this->type) === '') {
            throw new InvalidArgumentException(
                'A runtime graph edge type cannot be empty.',
            );
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException(
                'A runtime graph edge label cannot be empty.',
            );
        }
    }

    public function key(): string
    {
        return implode(
            ':',
            [
                trim($this->source),
                trim($this->type),
                trim($this->target),
            ],
        );
    }

    /**
     * @return array{
     *     source: string,
     *     target: string,
     *     type: string,
     *     label: string,
     *     metadata: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'source' => trim($this->source),
            'target' => trim($this->target),
            'type' => trim($this->type),
            'label' => trim($this->label),
            'metadata' => $this->metadata,
        ];
    }
}

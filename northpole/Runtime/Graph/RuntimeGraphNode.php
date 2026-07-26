<?php

declare(strict_types=1);

namespace Northpole\Runtime\Graph;

use InvalidArgumentException;

final readonly class RuntimeGraphNode
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $label,
        public ?string $module = null,
        public array $metadata = [],
    ) {
        if (trim($this->id) === '') {
            throw new InvalidArgumentException(
                'A runtime graph node id cannot be empty.',
            );
        }

        if (trim($this->type) === '') {
            throw new InvalidArgumentException(
                'A runtime graph node type cannot be empty.',
            );
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException(
                'A runtime graph node label cannot be empty.',
            );
        }

        if (
            $this->module !== null
            && trim($this->module) === ''
        ) {
            throw new InvalidArgumentException(
                'A runtime graph node module cannot be empty.',
            );
        }
    }

    /**
     * @return array{
     *     id: string,
     *     type: string,
     *     label: string,
     *     module: string|null,
     *     metadata: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => trim($this->id),
            'type' => trim($this->type),
            'label' => trim($this->label),
            'module' => $this->module === null
                ? null
                : trim($this->module),
            'metadata' => $this->metadata,
        ];
    }
}

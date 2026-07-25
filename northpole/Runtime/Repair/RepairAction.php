<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair;

use InvalidArgumentException;

final readonly class RepairAction
{
    public function __construct(
        private RepairActionType $type,
        private string $label,
        private string $content,
    ) {
        if (trim($this->label) === '') {
            throw new InvalidArgumentException(
                'A repair action must have a label.',
            );
        }

        if (trim($this->content) === '') {
            throw new InvalidArgumentException(
                'A repair action must have content.',
            );
        }
    }

    public function type(): RepairActionType
    {
        return $this->type;
    }

    public function label(): string
    {
        return trim($this->label);
    }

    public function content(): string
    {
        return trim($this->content);
    }

    /**
     * @return array{
     *     type: string,
     *     label: string,
     *     content: string
     * }
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'label' => $this->label(),
            'content' => $this->content(),
        ];
    }
}

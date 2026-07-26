<?php

declare(strict_types=1);

namespace Northpole\Runtime\Inspection;

final readonly class RuntimeInspectionResult
{
    public function __construct(
        public RuntimeInspectionReference $reference,
        public mixed $value,
    ) {}

    public function registry(): string
    {
        return $this->reference->registry;
    }

    public function module(): string
    {
        return $this->reference->module;
    }

    public function key(): string
    {
        return $this->reference->key;
    }

    /**
     * @return array{
     *     reference: array{
     *         registry: string,
     *         module: string,
     *         key: string
     *     },
     *     registry: string,
     *     module: string,
     *     key: string,
     *     value: mixed
     * }
     */
    public function toArray(): array
    {
        return [
            'reference' => $this->reference->toArray(),
            'registry' => $this->registry(),
            'module' => $this->module(),
            'key' => $this->key(),
            'value' => $this->value,
        ];
    }
}

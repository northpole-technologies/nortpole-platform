<?php

declare(strict_types=1);

namespace Northpole\Runtime\Inspector;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspection\RuntimeInspectionResult;

final readonly class RuntimeInspectorResult
{
    /**
     * @param  array{
     *     class: string|null,
     *     file: string|null
     * }  $source
     * @param  array<mixed>  $relationships
     * @param  array<mixed>  $dependencies
     * @param  array<mixed>  $warnings
     */
    public function __construct(
        public RuntimeInspectionReference $reference,
        public ?RuntimeInspectionResult $inspection,
        public array $source = [
            'class' => null,
            'file' => null,
        ],
        public array $relationships = [],
        public array $dependencies = [],
        public array $warnings = [],
    ) {}

    public function found(): bool
    {
        return $this->inspection !== null;
    }

    public function metadata(): mixed
    {
        return $this->inspection?->value;
    }

    /**
     * @return array{
     *     found: bool,
     *     reference: array{
     *         registry: string,
     *         module: string,
     *         key: string
     *     },
     *     metadata: mixed,
     *     source: array{
     *         class: string|null,
     *         file: string|null
     *     },
     *     relationships: array<mixed>,
     *     dependencies: array<mixed>,
     *     warnings: array<mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'found' => $this->found(),
            'reference' => $this->reference->toArray(),
            'metadata' => $this->metadata(),
            'source' => $this->source,
            'relationships' => $this->relationships,
            'dependencies' => $this->dependencies,
            'warnings' => $this->warnings,
        ];
    }
}

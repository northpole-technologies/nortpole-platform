<?php

declare(strict_types=1);

namespace Northpole\Runtime\Inspector;

use Northpole\Runtime\Inspection\Contracts\RuntimeInspectionServiceContract;
use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspection\RuntimeInspectionResult;
use Northpole\Runtime\Inspector\Contracts\RuntimeInspectorServiceContract;
use Northpole\Runtime\Relationships\Contracts\RuntimeRelationshipResolverContract;
use Northpole\Runtime\Relationships\RuntimeRelationship;
use ReflectionClass;
use ReflectionException;

final class RuntimeInspectorService implements RuntimeInspectorServiceContract
{
    public function __construct(
        private readonly RuntimeInspectionServiceContract $inspection,
        private readonly RuntimeRelationshipResolverContract $relationships,
    ) {}

    public function inspect(
        RuntimeInspectionReference $reference,
    ): RuntimeInspectorResult {
        $inspection = $this->inspection->inspect(
            $reference,
        );

        $resolvedReference =
            $inspection?->reference
            ?? $reference;

        return new RuntimeInspectorResult(
            reference: $resolvedReference,
            inspection: $inspection,
            source: $this->resolveSource(
                $inspection,
            ),
            relationships: $this->resolveRelationships(
                $resolvedReference,
                $inspection,
            ),
            dependencies: $inspection === null
                ? []
                : $this->relationships->dependenciesFor(
                    $resolvedReference,
                ),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveRelationships(
        RuntimeInspectionReference $reference,
        ?RuntimeInspectionResult $inspection,
    ): array {
        if ($inspection === null) {
            return [];
        }

        return array_map(
            static fn (
                RuntimeRelationship $relationship,
            ): array => $relationship->toArray(),
            $this->relationships->relationshipsFor(
                $reference,
                $inspection->value,
            ),
        );
    }

    /**
     * @return array{
     *     class: string|null,
     *     file: string|null
     * }
     */
    private function resolveSource(
        ?RuntimeInspectionResult $inspection,
    ): array {
        $class = $this->resolveClass(
            $inspection?->value,
        );

        if ($class === null) {
            return [
                'class' => null,
                'file' => null,
            ];
        }

        return [
            'class' => $class,
            'file' => $this->resolveClassFile(
                $class,
            ),
        ];
    }

    private function resolveClass(
        mixed $metadata,
    ): ?string {
        if (
            is_string($metadata)
            && class_exists($metadata)
        ) {
            return $metadata;
        }

        if (! is_array($metadata)) {
            return null;
        }

        $class = $metadata['class'] ?? null;

        if (
            ! is_string($class)
            || ! class_exists($class)
        ) {
            return null;
        }

        return $class;
    }

    /**
     * @param  class-string  $class
     */
    private function resolveClassFile(
        string $class,
    ): ?string {
        try {
            $file = new ReflectionClass(
                $class,
            )->getFileName();
        } catch (ReflectionException) {
            return null;
        }

        return is_string($file)
            ? $file
            : null;
    }
}

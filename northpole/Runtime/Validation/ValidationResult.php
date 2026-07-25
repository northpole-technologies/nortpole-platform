<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation;

use InvalidArgumentException;

final class ValidationResult
{
    /**
     * @var array<int, ValidationIssue>
     */
    private array $issues = [];

    /**
     * @param  iterable<int, ValidationIssue>  $issues
     */
    public function __construct(
        iterable $issues = [],
    ) {
        $this->addMany($issues);
    }

    public function add(
        ValidationIssue $issue,
    ): self {
        $this->issues[] = $issue;

        return $this;
    }

    /**
     * @param  iterable<int, ValidationIssue>  $issues
     */
    public function addMany(
        iterable $issues,
    ): self {
        foreach ($issues as $issue) {
            if (! $issue instanceof ValidationIssue) {
                throw new InvalidArgumentException(
                    'Validation results may only contain validation issues.',
                );
            }

            $this->add($issue);
        }

        return $this;
    }

    public function merge(
        self $result,
    ): self {
        return $this->addMany(
            $result->all(),
        );
    }

    /**
     * @return array<int, ValidationIssue>
     */
    public function all(): array
    {
        $issues = $this->issues;

        usort(
            $issues,
            static function (
                ValidationIssue $first,
                ValidationIssue $second,
            ): int {
                $severityComparison =
                    $second->severity()->weight()
                    <=>
                    $first->severity()->weight();

                if ($severityComparison !== 0) {
                    return $severityComparison;
                }

                $moduleComparison = strcmp(
                    $first->module() ?? '',
                    $second->module() ?? '',
                );

                if ($moduleComparison !== 0) {
                    return $moduleComparison;
                }

                return strcmp(
                    $first->code(),
                    $second->code(),
                );
            },
        );

        return $issues;
    }

    /**
     * @return array<int, ValidationIssue>
     */
    public function forSeverity(
        ValidationSeverity $severity,
    ): array {
        return array_values(
            array_filter(
                $this->all(),
                static fn (
                    ValidationIssue $issue,
                ): bool =>
                    $issue->severity() === $severity,
            ),
        );
    }

    /**
     * @return array<int, ValidationIssue>
     */
    public function forModule(
        string $module,
    ): array {
        $module = trim($module);

        if ($module === '') {
            return [];
        }

        return array_values(
            array_filter(
                $this->all(),
                static fn (
                    ValidationIssue $issue,
                ): bool =>
                    $issue->module() === $module,
            ),
        );
    }

    public function count(
        ?ValidationSeverity $severity = null,
    ): int {
        if ($severity === null) {
            return count($this->issues);
        }

        return count(
            $this->forSeverity($severity),
        );
    }

    public function errorCount(): int
    {
        return $this->count(
            ValidationSeverity::Error,
        );
    }

    public function warningCount(): int
    {
        return $this->count(
            ValidationSeverity::Warning,
        );
    }

    public function infoCount(): int
    {
        return $this->count(
            ValidationSeverity::Info,
        );
    }

    public function hasErrors(): bool
    {
        return $this->errorCount() > 0;
    }

    public function hasWarnings(): bool
    {
        return $this->warningCount() > 0;
    }

    public function passes(): bool
    {
        return ! $this->hasErrors();
    }

    public function isEmpty(): bool
    {
        return $this->issues === [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (
                ValidationIssue $issue,
            ): array => $issue->toArray(),
            $this->all(),
        );
    }
}
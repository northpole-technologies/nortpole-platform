<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Validation;

use InvalidArgumentException;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class ValidationResultTest extends TestCase
{
    public function test_it_collects_validation_issues(): void
    {
        $result = new ValidationResult;

        $result
            ->add(
                $this->issue(
                    'warning.one',
                    ValidationSeverity::Warning,
                ),
            )
            ->add(
                $this->issue(
                    'error.one',
                    ValidationSeverity::Error,
                ),
            );

        self::assertSame(
            2,
            $result->count(),
        );

        self::assertSame(
            1,
            $result->warningCount(),
        );

        self::assertSame(
            1,
            $result->errorCount(),
        );

        self::assertTrue(
            $result->hasWarnings(),
        );

        self::assertTrue(
            $result->hasErrors(),
        );

        self::assertFalse(
            $result->passes(),
        );
    }

    public function test_warnings_do_not_fail_validation(): void
    {
        $result = new ValidationResult([
            $this->issue(
                'warning.one',
                ValidationSeverity::Warning,
            ),
        ]);

        self::assertTrue(
            $result->passes(),
        );

        self::assertTrue(
            $result->hasWarnings(),
        );

        self::assertFalse(
            $result->hasErrors(),
        );
    }

    public function test_it_filters_issues_by_severity(): void
    {
        $result = new ValidationResult([
            $this->issue(
                'info.one',
                ValidationSeverity::Info,
            ),
            $this->issue(
                'warning.one',
                ValidationSeverity::Warning,
            ),
            $this->issue(
                'warning.two',
                ValidationSeverity::Warning,
            ),
        ]);

        self::assertCount(
            2,
            $result->forSeverity(
                ValidationSeverity::Warning,
            ),
        );

        self::assertSame(
            2,
            $result->warningCount(),
        );

        self::assertSame(
            1,
            $result->infoCount(),
        );
    }

    public function test_it_filters_issues_by_module(): void
    {
        $result = new ValidationResult([
            $this->issue(
                'crm.warning',
                ValidationSeverity::Warning,
                'crm',
            ),
            $this->issue(
                'inventory.warning',
                ValidationSeverity::Warning,
                'inventory',
            ),
        ]);

        $issues = $result->forModule('crm');

        self::assertCount(
            1,
            $issues,
        );

        self::assertSame(
            'crm.warning',
            $issues[0]->code(),
        );

        self::assertSame(
            [],
            $result->forModule(' '),
        );
    }

    public function test_it_orders_errors_before_warnings_and_info(): void
    {
        $result = new ValidationResult([
            $this->issue(
                'info.one',
                ValidationSeverity::Info,
            ),
            $this->issue(
                'warning.one',
                ValidationSeverity::Warning,
            ),
            $this->issue(
                'error.one',
                ValidationSeverity::Error,
            ),
        ]);

        self::assertSame(
            [
                'error.one',
                'warning.one',
                'info.one',
            ],
            array_map(
                static fn (
                    ValidationIssue $issue,
                ): string => $issue->code(),
                $result->all(),
            ),
        );
    }

    public function test_it_merges_results(): void
    {
        $first = new ValidationResult([
            $this->issue(
                'warning.one',
                ValidationSeverity::Warning,
            ),
        ]);

        $second = new ValidationResult([
            $this->issue(
                'error.one',
                ValidationSeverity::Error,
            ),
        ]);

        $first->merge($second);

        self::assertSame(
            2,
            $first->count(),
        );
    }

    public function test_a_new_result_is_empty_and_passes(): void
    {
        $result = new ValidationResult;

        self::assertTrue(
            $result->isEmpty(),
        );

        self::assertTrue(
            $result->passes(),
        );

        self::assertSame(
            [],
            $result->toArray(),
        );
    }

    public function test_it_rejects_non_issue_values(): void
    {
        $result = new ValidationResult;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Validation results may only contain validation issues.',
        );

        $result->addMany([
            'not-an-issue',
        ]);
    }

    private function issue(
        string $code,
        ValidationSeverity $severity,
        ?string $module = null,
    ): ValidationIssue {
        return new ValidationIssue(
            code: $code,
            message: $code,
            severity: $severity,
            module: $module,
        );
    }
}
<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Repair;

use InvalidArgumentException;
use Northpole\Runtime\Repair\RepairRecommendation;
use Northpole\Runtime\Repair\RuntimeRepairEngine;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class RuntimeRepairEngineTest extends TestCase
{
    public function test_it_registers_repair_resolvers(): void
    {
        $engine = new RuntimeRepairEngine;

        $engine->register(
            'handler.missing',
            $this->resolver(),
        );

        $this->assertTrue(
            $engine->has('handler.missing'),
        );

        $this->assertSame(
            1,
            $engine->count(),
        );
    }

    public function test_it_creates_recommendations_for_known_issues(): void
    {
        $engine = new RuntimeRepairEngine;

        $engine->register(
            'handler.missing',
            $this->resolver(),
        );

        $validationResult = new ValidationResult([
            new ValidationIssue(
                code: 'handler.missing',
                message: 'The handler is missing.',
                severity: ValidationSeverity::Error,
                module: 'crm',
            ),
        ]);

        $repairResult = $engine->recommend(
            $validationResult,
        );

        $this->assertSame(
            1,
            $repairResult->count(),
        );

        $this->assertSame(
            'repair.handler.missing',
            $repairResult->all()[0]->code(),
        );

        $this->assertSame(
            'crm',
            $repairResult->all()[0]->module(),
        );
    }

    public function test_it_ignores_unknown_issues(): void
    {
        $engine = new RuntimeRepairEngine;

        $validationResult = new ValidationResult([
            new ValidationIssue(
                code: 'unknown.issue',
                message: 'Unknown issue.',
                severity: ValidationSeverity::Warning,
            ),
        ]);

        $this->assertTrue(
            $engine
                ->recommend($validationResult)
                ->isEmpty(),
        );
    }

    public function test_a_resolver_may_decline_a_recommendation(): void
    {
        $engine = new RuntimeRepairEngine;

        $engine->register(
            'handler.missing',
            static fn (
                ValidationIssue $issue,
            ): null => null,
        );

        $validationResult = new ValidationResult([
            new ValidationIssue(
                code: 'handler.missing',
                message: 'The handler is missing.',
                severity: ValidationSeverity::Error,
            ),
        ]);

        $this->assertTrue(
            $engine
                ->recommend($validationResult)
                ->isEmpty(),
        );
    }

    public function test_it_rejects_an_empty_issue_code(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        (new RuntimeRepairEngine)->register(
            ' ',
            $this->resolver(),
        );
    }

    public function test_it_rejects_duplicate_resolvers(): void
    {
        $engine = new RuntimeRepairEngine;

        $engine->register(
            'handler.missing',
            $this->resolver(),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $engine->register(
            'handler.missing',
            $this->resolver(),
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $engine = new RuntimeRepairEngine;

        $engine->register(
            'handler.missing',
            $this->resolver(),
        );

        $engine->clear();

        $this->assertSame(
            0,
            $engine->count(),
        );
    }

    private function resolver(): \Closure
    {
        return static fn (
            ValidationIssue $issue,
        ): RepairRecommendation =>
            new RepairRecommendation(
                code: 'repair.'.$issue->code(),
                title: 'Repair handler',
                description: $issue->message(),
                severity: $issue->severity(),
                module: $issue->module(),
                issue: $issue,
            );
    }
}

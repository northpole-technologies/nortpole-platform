<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Validation;

use InvalidArgumentException;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class ValidationIssueTest extends TestCase
{
    public function test_it_creates_a_validation_issue(): void
    {
        $issue = new ValidationIssue(
            code: 'command.handler_missing',
            message: 'A command handler is missing.',
            severity: ValidationSeverity::Error,
            module: 'crm',
            context: [
                'command' => 'crm.customer.create',
            ],
        );

        self::assertSame(
            'command.handler_missing',
            $issue->code(),
        );

        self::assertSame(
            'A command handler is missing.',
            $issue->message(),
        );

        self::assertSame(
            ValidationSeverity::Error,
            $issue->severity(),
        );

        self::assertSame(
            'crm',
            $issue->module(),
        );

        self::assertSame(
            [
                'command' => 'crm.customer.create',
            ],
            $issue->context(),
        );
    }

    public function test_it_normalises_text_values(): void
    {
        $issue = new ValidationIssue(
            code: ' command.handler_missing ',
            message: ' Missing handler. ',
            severity: ValidationSeverity::Warning,
            module: ' crm ',
        );

        self::assertSame(
            'command.handler_missing',
            $issue->code(),
        );

        self::assertSame(
            'Missing handler.',
            $issue->message(),
        );

        self::assertSame(
            'crm',
            $issue->module(),
        );
    }

    public function test_it_serialises_to_an_array(): void
    {
        $issue = new ValidationIssue(
            code: 'permission.orphaned',
            message: 'Permission is not used.',
            severity: ValidationSeverity::Warning,
            module: 'crm',
        );

        self::assertSame(
            [
                'code' => 'permission.orphaned',
                'message' => 'Permission is not used.',
                'severity' => 'warning',
                'module' => 'crm',
                'context' => [],
            ],
            $issue->toArray(),
        );
    }

    public function test_it_rejects_an_empty_code(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A validation issue must have a code.',
        );

        new ValidationIssue(
            code: ' ',
            message: 'Missing handler.',
            severity: ValidationSeverity::Error,
        );
    }

    public function test_it_rejects_an_empty_message(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A validation issue must have a message.',
        );

        new ValidationIssue(
            code: 'command.handler_missing',
            message: ' ',
            severity: ValidationSeverity::Error,
        );
    }

    public function test_it_rejects_an_empty_module(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A validation issue module cannot be empty.',
        );

        new ValidationIssue(
            code: 'command.handler_missing',
            message: 'Missing handler.',
            severity: ValidationSeverity::Error,
            module: ' ',
        );
    }
}
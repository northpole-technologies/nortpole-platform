<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Validation;

use InvalidArgumentException;
use Northpole\Runtime\Validation\Contracts\ValidationRuleContract;
use Northpole\Runtime\Validation\RuntimeValidationEngine;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RuntimeValidationEngineTest extends TestCase
{
    public function test_it_registers_validation_rules(): void
    {
        $engine = new RuntimeValidationEngine;

        $rule = $this->rule(
            'commands',
        );

        $result = $engine->register($rule);

        self::assertSame(
            $engine,
            $result,
        );

        self::assertTrue(
            $engine->has('commands'),
        );

        self::assertSame(
            1,
            $engine->count(),
        );

        self::assertSame(
            [
                'commands' => $rule,
            ],
            $engine->rules(),
        );
    }

    public function test_it_registers_many_rules(): void
    {
        $engine = new RuntimeValidationEngine;

        $engine->registerMany([
            $this->rule('queries'),
            $this->rule('commands'),
        ]);

        self::assertSame(
            [
                'commands',
                'queries',
            ],
            array_keys(
                $engine->rules(),
            ),
        );
    }

    public function test_it_runs_registered_rules(): void
    {
        $engine = new RuntimeValidationEngine;

        $engine->register(
            $this->rule(
                name: 'commands',
                issues: [
                    new ValidationIssue(
                        code: 'command.handler_missing',
                        message: 'Missing command handler.',
                        severity: ValidationSeverity::Error,
                        module: 'crm',
                    ),
                ],
            ),
        );

        $result = $engine->validate();

        self::assertSame(
            1,
            $result->count(),
        );

        self::assertTrue(
            $result->hasErrors(),
        );

        self::assertSame(
            'command.handler_missing',
            $result->all()[0]->code(),
        );
    }

    public function test_rule_failures_become_validation_issues(): void
    {
        $engine = new RuntimeValidationEngine;

        $engine->register(
            new class implements ValidationRuleContract
            {
                public function name(): string
                {
                    return 'broken-rule';
                }

                public function validate(): ValidationResult
                {
                    throw new RuntimeException(
                        'Something went wrong.',
                    );
                }
            },
        );

        $result = $engine->validate();

        self::assertSame(
            1,
            $result->errorCount(),
        );

        $issue = $result->all()[0];

        self::assertSame(
            'validation.rule_failed',
            $issue->code(),
        );

        self::assertSame(
            'broken-rule',
            $issue->context()['rule'],
        );

        self::assertSame(
            RuntimeException::class,
            $issue->context()['exception'],
        );
    }

    public function test_it_rejects_an_empty_rule_name(): void
    {
        $engine = new RuntimeValidationEngine;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A runtime validation rule must have a name.',
        );

        $engine->register(
            $this->rule(' '),
        );
    }

    public function test_it_rejects_duplicate_rule_names(): void
    {
        $engine = new RuntimeValidationEngine;

        $engine->register(
            $this->rule('commands'),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Runtime validation rule [commands] is already registered.',
        );

        $engine->register(
            $this->rule('commands'),
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $engine = new RuntimeValidationEngine;

        $engine->register(
            $this->rule('commands'),
        );

        $result = $engine->clear();

        self::assertSame(
            $engine,
            $result,
        );

        self::assertSame(
            0,
            $engine->count(),
        );

        self::assertSame(
            [],
            $engine->rules(),
        );
    }

    /**
     * @param  array<int, ValidationIssue>  $issues
     */
    private function rule(
        string $name,
        array $issues = [],
    ): ValidationRuleContract {
        return new class(
            $name,
            $issues,
        ) implements ValidationRuleContract
        {
            /**
             * @param  array<int, ValidationIssue>  $issues
             */
            public function __construct(
                private readonly string $ruleName,
                private readonly array $issues,
            ) {}

            public function name(): string
            {
                return $this->ruleName;
            }

            public function validate(): ValidationResult
            {
                return new ValidationResult(
                    $this->issues,
                );
            }
        };
    }
}
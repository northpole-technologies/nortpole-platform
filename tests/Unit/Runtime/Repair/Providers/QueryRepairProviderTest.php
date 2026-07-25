<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Repair\Providers;

use Northpole\Runtime\Repair\Providers\QueryRepairProvider;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class QueryRepairProviderTest extends TestCase
{
    public function test_it_supports_query_validation_issues(): void
    {
        $provider = new QueryRepairProvider;

        self::assertTrue(
            $provider->supports(
                $this->issue(
                    'query.handler_unregistered',
                ),
            ),
        );

        self::assertFalse(
            $provider->supports(
                $this->issue(
                    'command.handler_unregistered',
                ),
            ),
        );
    }

    public function test_it_recommends_registering_a_query_handler(): void
    {
        $issue = new ValidationIssue(
            code: 'query.handler_unregistered',
            message: 'Query handler is not registered.',
            severity: ValidationSeverity::Error,
            module: 'crm',
            context: [
                'name' => 'crm.customer.find',
                'expected_handler' =>
                    'Modules\CRM\Queries\FindCustomerHandler',
            ],
        );

        $recommendation = (
            new QueryRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.query.register_handler',
            $recommendation->code(),
        );

        self::assertStringContainsString(
            'crm.customer.find',
            $recommendation->description(),
        );
    }

    public function test_it_recommends_resolving_unexpected_queries(): void
    {
        $issue = new ValidationIssue(
            code: 'query.handler_unexpected',
            message: 'Unexpected query registration.',
            severity: ValidationSeverity::Warning,
            module: 'platform',
            context: [
                'name' => 'platform.runtime.summary',
                'handler' =>
                    'Modules\Platform\Queries\RuntimeSummaryHandler',
                'owner' => 'platform',
            ],
        );

        $recommendation = (
            new QueryRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.query.resolve_unexpected_handler',
            $recommendation->code(),
        );

        self::assertSame(
            ValidationSeverity::Warning,
            $recommendation->severity(),
        );
    }

    private function issue(
        string $code,
    ): ValidationIssue {
        return new ValidationIssue(
            code: $code,
            message: 'Validation issue.',
            severity: ValidationSeverity::Error,
        );
    }
}
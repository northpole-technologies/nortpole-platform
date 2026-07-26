<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Repair\Providers;

use Northpole\Runtime\Repair\Providers\GraphRepairProvider;
use Northpole\Runtime\Repair\RepairActionType;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class GraphRepairProviderTest extends TestCase
{
    public function test_it_supports_graph_validation_issues(): void
    {
        $provider = new GraphRepairProvider;

        self::assertTrue(
            $provider->supports(
                $this->issue(
                    'graph.node_orphaned',
                ),
            ),
        );

        self::assertTrue(
            $provider->supports(
                $this->issue(
                    'graph.self_dependency',
                ),
            ),
        );

        self::assertTrue(
            $provider->supports(
                $this->issue(
                    'graph.dependency_cycle',
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

    public function test_it_recommends_connecting_an_orphaned_node(): void
    {
        $issue = new ValidationIssue(
            code: 'graph.node_orphaned',
            message: 'Runtime graph node is orphaned.',
            severity: ValidationSeverity::Warning,
            module: 'crm',
            context: [
                'node_id' => 'command:crm.customer.create',
                'node_type' => 'command',
            ],
        );

        $recommendation = (
            new GraphRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.graph.connect_orphaned_node',
            $recommendation->code(),
        );

        self::assertSame(
            'crm',
            $recommendation->module(),
        );

        self::assertSame(
            ValidationSeverity::Warning,
            $recommendation->severity(),
        );

        self::assertStringContainsString(
            'command:crm.customer.create',
            $recommendation->description(),
        );

        self::assertSame(
            RepairActionType::Instruction,
            $recommendation->actions()[0]->type(),
        );
    }

    public function test_it_recommends_removing_a_self_dependency(): void
    {
        $issue = new ValidationIssue(
            code: 'graph.self_dependency',
            message: 'Module depends on itself.',
            severity: ValidationSeverity::Error,
            module: 'crm',
            context: [
                'module' => 'crm',
                'node_id' => 'module:crm',
            ],
        );

        $recommendation = (
            new GraphRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.graph.remove_self_dependency',
            $recommendation->code(),
        );

        self::assertStringContainsString(
            'crm',
            $recommendation->description(),
        );

        self::assertStringContainsString(
            'dependencies',
            $recommendation->actions()[0]->content(),
        );
    }

    public function test_it_recommends_breaking_a_dependency_cycle(): void
    {
        $issue = new ValidationIssue(
            code: 'graph.dependency_cycle',
            message: 'Dependency cycle detected.',
            severity: ValidationSeverity::Error,
            module: 'crm',
            context: [
                'cycle' => [
                    'crm',
                    'sales',
                    'reports',
                    'crm',
                ],
            ],
        );

        $recommendation = (
            new GraphRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.graph.break_dependency_cycle',
            $recommendation->code(),
        );

        self::assertStringContainsString(
            'crm -> sales -> reports -> crm',
            $recommendation->description(),
        );

        self::assertCount(
            4,
            $recommendation->actions(),
        );

        self::assertSame(
            RepairActionType::PowerShell,
            $recommendation->actions()[3]->type(),
        );
    }

    public function test_it_returns_null_for_an_unsupported_issue(): void
    {
        $recommendation = (
            new GraphRepairProvider
        )->recommend(
            $this->issue(
                'query.handler_unregistered',
            ),
        );

        self::assertNull($recommendation);
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

<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Repair\Providers;

use Northpole\Runtime\Repair\Providers\CommandRepairProvider;
use Northpole\Runtime\Repair\RepairActionType;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class CommandRepairProviderTest extends TestCase
{
    public function test_it_supports_command_validation_issues(): void
    {
        $provider = new CommandRepairProvider;

        self::assertTrue(
            $provider->supports(
                $this->issue(
                    'command.handler_class_missing',
                ),
            ),
        );

        self::assertFalse(
            $provider->supports(
                $this->issue(
                    'query.handler_class_missing',
                ),
            ),
        );
    }

    public function test_it_recommends_creating_a_missing_handler(): void
    {
        $issue = new ValidationIssue(
            code: 'command.handler_class_missing',
            message: 'Command handler class is missing.',
            severity: ValidationSeverity::Error,
            module: 'crm',
            context: [
                'name' => 'crm.customer.create',
                'handler' =>
                    'Modules\CRM\Commands\CreateCustomerHandler',
            ],
        );

        $recommendation = (
            new CommandRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.command.create_missing_handler',
            $recommendation->code(),
        );

        self::assertSame(
            'crm',
            $recommendation->module(),
        );

        self::assertSame(
            RepairActionType::Instruction,
            $recommendation->actions()[0]->type(),
        );

        self::assertStringContainsString(
            'CreateCustomerHandler',
            $recommendation->description(),
        );
    }

    public function test_it_recommends_correcting_registry_ownership(): void
    {
        $issue = new ValidationIssue(
            code: 'command.owner_mismatch',
            message: 'Command owner does not match.',
            severity: ValidationSeverity::Error,
            module: 'crm',
            context: [
                'name' => 'crm.customer.create',
                'expected_owner' => 'crm',
                'registered_owner' => 'inventory',
            ],
        );

        $recommendation = (
            new CommandRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.command.correct_handler_owner',
            $recommendation->code(),
        );

        self::assertStringContainsString(
            'inventory',
            $recommendation->description(),
        );
    }

    public function test_it_recommends_resolving_duplicate_declarations(): void
    {
        $issue = new ValidationIssue(
            code: 'command.declaration_duplicate',
            message: 'Command is declared twice.',
            severity: ValidationSeverity::Error,
            module: 'inventory',
            context: [
                'name' => 'platform.cache.clear',
                'first_module' => 'platform',
                'second_module' => 'inventory',
            ],
        );

        $recommendation = (
            new CommandRepairProvider
        )->recommend($issue);

        self::assertNotNull($recommendation);

        self::assertSame(
            'repair.command.resolve_duplicate_declaration',
            $recommendation->code(),
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
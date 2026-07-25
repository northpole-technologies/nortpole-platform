<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Validation\Rules;

use Northpole\Runtime\Commands\Contracts\ModuleCommandContract;
use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Validation\Rules\CommandHandlerValidationRule;
use Northpole\Runtime\Validation\ValidationIssue;
use PHPUnit\Framework\TestCase;

final class CommandHandlerValidationRuleTest extends TestCase
{
    public function test_valid_command_handlers_pass(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            commandName: 'crm.customer.create',
            handler: ValidCommandHandler::class,
            module: 'crm',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    commands: [
                        'crm.customer.create' =>
                            ValidCommandHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertTrue($result->passes());
        self::assertTrue($result->isEmpty());
    }

    public function test_it_reports_a_missing_handler_class(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            commandName: 'crm.customer.create',
            handler: 'Modules\CRM\MissingHandler',
            module: 'crm',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    commands: [
                        'crm.customer.create' =>
                            'Modules\CRM\MissingHandler',
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            ['command.handler_class_missing'],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_an_invalid_handler_contract(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            commandName: 'crm.customer.create',
            handler: InvalidCommandHandler::class,
            module: 'crm',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    commands: [
                        'crm.customer.create' =>
                            InvalidCommandHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            ['command.handler_contract_invalid'],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_an_unregistered_handler(): void
    {
        $result = $this->rule(
            registry: new ModuleCommandRegistry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    commands: [
                        'crm.customer.create' =>
                            ValidCommandHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            ['command.handler_unregistered'],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_handler_and_owner_mismatches(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            commandName: 'crm.customer.create',
            handler: AlternativeCommandHandler::class,
            module: 'inventory',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    commands: [
                        'crm.customer.create' =>
                            ValidCommandHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            [
                'command.handler_mismatch',
                'command.owner_mismatch',
            ],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_unexpected_registry_entries(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            commandName: 'platform.cache.clear',
            handler: ValidCommandHandler::class,
            module: 'platform',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [],
        )->validate();

        self::assertTrue($result->passes());
        self::assertSame(1, $result->warningCount());

        self::assertSame(
            ['command.handler_unexpected'],
            $this->codes($result->all()),
        );
    }

    /**
     * @param  array<int, ModuleManifest>  $modules
     */
    private function rule(
        ModuleCommandRegistry $registry,
        array $modules,
    ): CommandHandlerValidationRule {
        return new CommandHandlerValidationRule(
            modulesResolver: static fn (): array =>
                $modules,
            registry: $registry,
        );
    }

    /**
     * @param  array<string, string>  $commands
     */
    private function manifest(
        string $slug,
        array $commands,
    ): ModuleManifest {
        return new ModuleManifest(
            data: [
                'name' => ucfirst($slug),
                'slug' => $slug,
                'version' => '1.0.0',
                'enabled' => true,
                'commands' => [
                    'handles' => $commands,
                ],
            ],
            path: '/modules/'.$slug,
            manifestPath: '/modules/'.$slug.'/module.json',
        );
    }

    /**
     * @param  array<int, ValidationIssue>  $issues
     * @return array<int, string>
     */
    private function codes(array $issues): array
    {
        return array_map(
            static fn (
                ValidationIssue $issue,
            ): string => $issue->code(),
            $issues,
        );
    }
}

final class ValidCommandHandler implements
    ModuleCommandHandlerContract
{
    public function handle(
        ModuleCommandContract $command,
    ): mixed {
        return null;
    }
}

final class AlternativeCommandHandler implements
    ModuleCommandHandlerContract
{
    public function handle(
        ModuleCommandContract $command,
    ): mixed {
        return null;
    }
}

final class InvalidCommandHandler
{
}
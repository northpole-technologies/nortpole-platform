<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Commands\ModuleCommandRegistrar;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\CommandHandlerStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class CommandHandlerStageTest extends TestCase
{
    public function test_it_registers_module_command_handlers(): void
    {
        $registry = new ModuleCommandRegistry;

        $stage = new CommandHandlerStage(
            new ModuleCommandRegistrar($registry),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customer.create' => CreateCustomerHandler::class,
                    'crm.customer.update' => UpdateCustomerHandler::class,
                ]),
            ),
        );

        $this->assertSame(
            2,
            $registry->count(),
        );

        $this->assertSame(
            CreateCustomerHandler::class,
            $registry->handler(
                'crm.customer.create',
            ),
        );

        $this->assertSame(
            UpdateCustomerHandler::class,
            $registry->handler(
                'crm.customer.update',
            ),
        );

        $this->assertSame(
            'crm',
            $registry->owner(
                'crm.customer.create',
            ),
        );
    }

    public function test_it_skips_modules_without_command_handlers(): void
    {
        $registry = new ModuleCommandRegistry;

        $stage = new CommandHandlerStage(
            new ModuleCommandRegistrar($registry),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([]),
            ),
        );

        $this->assertSame(
            0,
            $registry->count(),
        );

        $this->assertSame(
            [],
            $registry->all(),
        );
    }

    public function test_it_rejects_empty_module_slugs(): void
    {
        $registry = new ModuleCommandRegistry;

        $stage = new CommandHandlerStage(
            new ModuleCommandRegistrar($registry),
        );

        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('   ');

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module command handler owner cannot be empty.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest,
            ),
        );
    }

    public function test_it_rejects_empty_command_names(): void
    {
        $registry = new ModuleCommandRegistry;

        $stage = new CommandHandlerStage(
            new ModuleCommandRegistrar($registry),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Handled command names for module [crm] must be non-empty strings.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    '   ' => CreateCustomerHandler::class,
                ]),
            ),
        );
    }

    public function test_it_rejects_empty_handler_class_names(): void
    {
        $registry = new ModuleCommandRegistry;

        $stage = new CommandHandlerStage(
            new ModuleCommandRegistrar($registry),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Command handlers for module [crm] and command [crm.customer.create] must be non-empty class names.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customer.create' => '   ',
                ]),
            ),
        );
    }

    public function test_it_rejects_duplicate_command_handler_registrations(): void
    {
        $registry = new ModuleCommandRegistry;

        $stage = new CommandHandlerStage(
            new ModuleCommandRegistrar($registry),
        );

        $context = new BootContext(
            $this->createRuntime(),
            $this->createManifestMock([
                'crm.customer.create' => CreateCustomerHandler::class,
            ]),
        );

        $stage->boot($context);

        $this->expectException(
            InvalidArgumentException::class,
        );

        $stage->boot($context);
    }

    public function test_stage_has_the_expected_name_and_priority(): void
    {
        $stage = new CommandHandlerStage(
            new ModuleCommandRegistrar(
                new ModuleCommandRegistry,
            ),
        );

        $this->assertSame(
            'command-handlers',
            $stage->name(),
        );

        $this->assertSame(
            700,
            $stage->priority(),
        );
    }

    /**
     * @param  array<string, string>  $handledCommands
     */
    private function createManifestMock(
        array $handledCommands,
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('crm');

        $manifest
            ->method('handledCommands')
            ->willReturn($handledCommands);

        return $manifest;
    }

    private function createRuntime(): Runtime
    {
        $repository = new ModuleRepository;

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder,
                new ManifestLoader,
                $repository,
            ),
            $repository,
            new ModuleDependencyResolver,
            base_path('modules'),
        );
    }
}

final class CreateCustomerHandler {}

final class UpdateCustomerHandler {}

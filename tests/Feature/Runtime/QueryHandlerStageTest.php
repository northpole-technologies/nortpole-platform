<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\QueryHandlerStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Queries\ModuleQueryRegistrar;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class QueryHandlerStageTest extends TestCase
{
    public function test_it_registers_module_query_handlers(): void
    {
        $registry = new ModuleQueryRegistry();

        $stage = new QueryHandlerStage(
            new ModuleQueryRegistrar($registry),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customer.find' => FindCustomerHandler::class,
                    'crm.customer.list' => ListCustomersHandler::class,
                ]),
            ),
        );

        $this->assertSame(
            2,
            $registry->count(),
        );

        $this->assertSame(
            FindCustomerHandler::class,
            $registry->handler(
                'crm.customer.find',
            ),
        );

        $this->assertSame(
            ListCustomersHandler::class,
            $registry->handler(
                'crm.customer.list',
            ),
        );

        $this->assertSame(
            'crm',
            $registry->owner(
                'crm.customer.find',
            ),
        );
    }

    public function test_it_skips_modules_without_query_handlers(): void
    {
        $registry = new ModuleQueryRegistry();

        $stage = new QueryHandlerStage(
            new ModuleQueryRegistrar($registry),
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
        $registry = new ModuleQueryRegistry();

        $stage = new QueryHandlerStage(
            new ModuleQueryRegistrar($registry),
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
            'A module query handler owner cannot be empty.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest,
            ),
        );
    }

    public function test_it_rejects_empty_query_names(): void
    {
        $registry = new ModuleQueryRegistry();

        $stage = new QueryHandlerStage(
            new ModuleQueryRegistrar($registry),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Handled query names for module [crm] must be non-empty strings.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    '   ' => FindCustomerHandler::class,
                ]),
            ),
        );
    }

    public function test_it_rejects_empty_handler_class_names(): void
    {
        $registry = new ModuleQueryRegistry();

        $stage = new QueryHandlerStage(
            new ModuleQueryRegistrar($registry),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Query handlers for module [crm] and query [crm.customer.find] must be non-empty class names.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customer.find' => '   ',
                ]),
            ),
        );
    }

    public function test_it_rejects_duplicate_query_handler_registrations(): void
    {
        $registry = new ModuleQueryRegistry();

        $stage = new QueryHandlerStage(
            new ModuleQueryRegistrar($registry),
        );

        $context = new BootContext(
            $this->createRuntime(),
            $this->createManifestMock([
                'crm.customer.find' => FindCustomerHandler::class,
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
        $stage = new QueryHandlerStage(
            new ModuleQueryRegistrar(
                new ModuleQueryRegistry(),
            ),
        );

        $this->assertSame(
            'query-handlers',
            $stage->name(),
        );

        $this->assertSame(
            710,
            $stage->priority(),
        );
    }

    /**
     * @param array<string, string> $handledQueries
     */
    private function createManifestMock(
        array $handledQueries,
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('crm');

        $manifest
            ->method('handledQueries')
            ->willReturn($handledQueries);

        return $manifest;
    }

    private function createRuntime(): Runtime
    {
        $repository = new ModuleRepository();

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder(),
                new ManifestLoader(),
                $repository,
            ),
            $repository,
            new ModuleDependencyResolver(),
            base_path('modules'),
        );
    }
}

final class FindCustomerHandler
{
}

final class ListCustomersHandler
{
}

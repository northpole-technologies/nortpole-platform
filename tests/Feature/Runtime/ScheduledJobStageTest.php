<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistrar;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\ScheduledJobStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class ScheduledJobStageTest extends TestCase
{
    public function test_it_registers_module_scheduled_jobs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $stage = new ScheduledJobStage(
            new ModuleScheduledJobRegistrar(
                $registry
            ),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    [
                        'class' => RefreshReportsJob::class,
                        'frequency' => 'daily',
                        'at' => '02:00',
                        'queue' => 'reports',
                        'without_overlapping' => true,
                        'run_in_background' => true,
                    ],
                    [
                        'class' => CleanupReportsJob::class,
                        'frequency' => 'hourly',
                    ],
                ]),
            ),
        );

        $this->assertSame(
            2,
            $registry->count(),
        );

        $this->assertSame(
            [
                [
                    'module' => 'reports',
                    'class' => CleanupReportsJob::class,
                    'frequency' => 'hourly',
                    'at' => null,
                    'queue' => null,
                    'without_overlapping' => false,
                    'run_in_background' => false,
                ],
                [
                    'module' => 'reports',
                    'class' => RefreshReportsJob::class,
                    'frequency' => 'daily',
                    'at' => '02:00',
                    'queue' => 'reports',
                    'without_overlapping' => true,
                    'run_in_background' => true,
                ],
            ],
            array_map(
                static fn ($job): array => $job->toArray(),
                $registry->all(),
            ),
        );
    }

    public function test_it_skips_modules_without_scheduled_jobs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $stage = new ScheduledJobStage(
            new ModuleScheduledJobRegistrar(
                $registry
            ),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([]),
            ),
        );

        $this->assertTrue(
            $registry->isEmpty(),
        );

        $this->assertSame(
            0,
            $registry->count(),
        );
    }

    public function test_it_rejects_empty_module_slugs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $stage = new ScheduledJobStage(
            new ModuleScheduledJobRegistrar(
                $registry
            ),
        );

        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('   ');

        $manifest
            ->method('scheduledJobs')
            ->willReturn([
                [
                    'class' => RefreshReportsJob::class,
                    'frequency' => 'daily',
                ],
            ]);

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A scheduled job module owner cannot be empty.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest,
            ),
        );
    }

    public function test_it_rejects_duplicate_scheduled_jobs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $stage = new ScheduledJobStage(
            new ModuleScheduledJobRegistrar(
                $registry
            ),
        );

        $context = new BootContext(
            $this->createRuntime(),
            $this->createManifestMock([
                [
                    'class' => RefreshReportsJob::class,
                    'frequency' => 'daily',
                    'at' => '02:00',
                ],
            ]),
        );

        $stage->boot($context);

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Scheduled job ['.
            RefreshReportsJob::class.
            '] from module [reports] is already registered for frequency [daily].',
        );

        $stage->boot($context);
    }

    public function test_stage_has_the_expected_name_and_priority(): void
    {
        $stage = new ScheduledJobStage(
            new ModuleScheduledJobRegistrar(
                new ModuleScheduledJobRegistry
            ),
        );

        $this->assertSame(
            'scheduled-jobs',
            $stage->name(),
        );

        $this->assertSame(
            675,
            $stage->priority(),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $scheduledJobs
     */
    private function createManifestMock(
        array $scheduledJobs,
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('reports');

        $manifest
            ->method('scheduledJobs')
            ->willReturn($scheduledJobs);

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

final class RefreshReportsJob {}

final class CleanupReportsJob {}
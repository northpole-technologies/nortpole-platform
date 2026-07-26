<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Providers;

use Northpole\Runtime\Diagnostics\RuntimeDashboardViewModel;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsService;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsViewModel;
use Northpole\Runtime\Diagnostics\RuntimeDoctorViewModel;
use Northpole\Runtime\Diagnostics\RuntimeEnvironmentService;
use Northpole\Runtime\Diagnostics\RuntimeModuleStatisticsService;
use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;
use Northpole\Runtime\Health\RuntimeHealthSummaryService;
use Northpole\Runtime\Inspection\Contracts\RuntimeInspectionServiceContract;
use Northpole\Runtime\Inspection\RuntimeInspectionService;
use Northpole\Runtime\Inspector\Contracts\RuntimeInspectorServiceContract;
use Northpole\Runtime\Inspector\RuntimeInspectorService;
use Northpole\Runtime\Providers\NorthpoleRuntimeServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class NorthpoleRuntimeServiceProviderTest extends TestCase
{
    public function test_the_runtime_provider_is_registered(): void
    {
        self::assertNotEmpty(
            $this->app->getProviders(
                NorthpoleRuntimeServiceProvider::class,
            ),
        );
    }

    #[DataProvider('runtimeSingletonProvider')]
    public function test_runtime_services_are_registered_as_singletons(
        string $service,
    ): void {
        $first = $this->app->make($service);
        $second = $this->app->make($service);

        self::assertInstanceOf(
            $service,
            $first,
        );

        self::assertSame(
            $first,
            $second,
        );
    }

    public function test_inspection_contract_resolves_to_the_inspection_service(): void
    {
        $contract = $this->app->make(
            RuntimeInspectionServiceContract::class,
        );

        $service = $this->app->make(
            RuntimeInspectionService::class,
        );

        self::assertSame(
            $service,
            $contract,
        );
    }

    public function test_inspector_contract_resolves_to_the_inspector_service(): void
    {
        $contract = $this->app->make(
            RuntimeInspectorServiceContract::class,
        );

        $service = $this->app->make(
            RuntimeInspectorService::class,
        );

        self::assertSame(
            $service,
            $contract,
        );
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function runtimeSingletonProvider(): array
    {
        return [
            'diagnostics service' => [
                RuntimeDiagnosticsService::class,
            ],
            'environment service' => [
                RuntimeEnvironmentService::class,
            ],
            'module statistics service' => [
                RuntimeModuleStatisticsService::class,
            ],
            'registry statistics service' => [
                RuntimeRegistryStatisticsService::class,
            ],
            'health summary service' => [
                RuntimeHealthSummaryService::class,
            ],
            'dashboard view model' => [
                RuntimeDashboardViewModel::class,
            ],
            'diagnostics view model' => [
                RuntimeDiagnosticsViewModel::class,
            ],
            'doctor view model' => [
                RuntimeDoctorViewModel::class,
            ],
            'inspection service' => [
                RuntimeInspectionService::class,
            ],
            'inspector service' => [
                RuntimeInspectorService::class,
            ],
        ];
    }
}

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
        ];
    }
}
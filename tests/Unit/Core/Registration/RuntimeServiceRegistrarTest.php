<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Registration;

use Northpole\Core\Registration\Contracts\ServiceRegistrar;
use Northpole\Core\Registration\RuntimeServiceRegistrar;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandBus;
use Northpole\Runtime\Events\ModuleEventBus;
use Northpole\Runtime\Health\RuntimeHealthService;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Runtime;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class RuntimeServiceRegistrarTest extends TestCase
{
    public function test_it_implements_the_service_registrar_contract(): void
    {
        $registrar = new RuntimeServiceRegistrar;

        $this->assertInstanceOf(
            ServiceRegistrar::class,
            $registrar
        );
    }

    #[DataProvider('runtimeServiceProvider')]
    public function test_it_registers_runtime_services_as_singletons(
        string $service
    ): void {
        $first = $this->app->make(
            $service
        );

        $second = $this->app->make(
            $service
        );

        $this->assertSame(
            $first,
            $second
        );
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function runtimeServiceProvider(): array
    {
        return [
            'runtime' => [
                Runtime::class,
            ],
            'capability registry' => [
                CapabilityRegistry::class,
            ],
            'event bus' => [
                ModuleEventBus::class,
            ],
            'command bus' => [
                ModuleCommandBus::class,
            ],
            'health service' => [
                RuntimeHealthService::class,
            ],
            'boot pipeline' => [
                BootPipeline::class,
            ],
        ];
    }
}

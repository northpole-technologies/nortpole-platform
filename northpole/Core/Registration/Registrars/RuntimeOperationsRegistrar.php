<?php

declare(strict_types=1);

namespace Northpole\Core\Registration\Registrars;

use Illuminate\Contracts\Foundation\Application;
use Northpole\Core\Registration\Contracts\ServiceRegistrar;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Health\RuntimeHealthService;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Repair\Providers\CommandRepairProvider;
use Northpole\Runtime\Repair\Providers\GraphRepairProvider;
use Northpole\Runtime\Repair\Providers\QueryRepairProvider;
use Northpole\Runtime\Repair\RuntimeRepairEngine;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Validation\Rules\CommandHandlerValidationRule;
use Northpole\Runtime\Validation\Rules\Graph\DependencyCycleValidationRule;
use Northpole\Runtime\Validation\Rules\Graph\OrphanNodeValidationRule;
use Northpole\Runtime\Validation\Rules\QueryHandlerValidationRule;
use Northpole\Runtime\Validation\RuntimeValidationEngine;

final class RuntimeOperationsRegistrar implements ServiceRegistrar
{
    public function register(
        Application $application
    ): void {
        $application->singleton(
            RuntimeValidationEngine::class,
            function (
                Application $application
            ): RuntimeValidationEngine {
                $modulesResolver = static function () use (
                    $application
                ): array {
                    return $application->make(
                        Runtime::class
                    )->modules();
                };

                return (new RuntimeValidationEngine)
                    ->registerMany([
                        new CommandHandlerValidationRule(
                            modulesResolver: $modulesResolver,
                            registry: $application->make(
                                ModuleCommandRegistry::class
                            ),
                        ),
                        new QueryHandlerValidationRule(
                            modulesResolver: $modulesResolver,
                            registry: $application->make(
                                ModuleQueryRegistry::class
                            ),
                        ),
                        new DependencyCycleValidationRule(
                            graphBuilder: $application->make(
                                RuntimeGraphBuilderContract::class
                            ),
                        ),
                        new OrphanNodeValidationRule(
                            graphBuilder: $application->make(
                                RuntimeGraphBuilderContract::class
                            ),
                        ),
                    ]);
            },
        );

        $application->singleton(
            RuntimeRepairEngine::class,
            function (): RuntimeRepairEngine {
                return (new RuntimeRepairEngine)
                    ->registerProviders([
                        new CommandRepairProvider,
                        new QueryRepairProvider,
                        new GraphRepairProvider,
                    ]);
            },
        );

        $application->singleton(
            RuntimeHealthService::class,
            function (
                Application $application
            ): RuntimeHealthService {
                return new RuntimeHealthService(
                    $application->make(
                        Runtime::class
                    ),
                );
            },
        );
    }
}

<?php

declare(strict_types=1);

namespace Northpole\Core\Registration\Registrars;

use Illuminate\Contracts\Foundation\Application;
use Northpole\Core\Registration\Contracts\ServiceRegistrar;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;
use Northpole\Runtime\Synchronisation\TenantAccessSynchroniser;

final class FeatureRegistryRegistrar implements ServiceRegistrar
{
    public function register(
        Application $application
    ): void {
        $application->singleton(
            CapabilityRegistry::class,
            function (): CapabilityRegistry {
                return new CapabilityRegistry;
            },
        );

        $application->singleton(
            PermissionRegistry::class,
            function (): PermissionRegistry {
                return new PermissionRegistry;
            },
        );

        $application->singleton(
            RoleDefinitionRegistry::class,
            function (): RoleDefinitionRegistry {
                return new RoleDefinitionRegistry;
            },
        );

        $application->singleton(
            TenantAccessSynchroniser::class,
            function (
                Application $application
            ): TenantAccessSynchroniser {
                return new TenantAccessSynchroniser(
                    permissions: $application->make(
                        PermissionRegistry::class
                    ),
                    roles: $application->make(
                        RoleDefinitionRegistry::class
                    ),
                );
            },
        );

        $application->singleton(
            NavigationRegistry::class,
            function (): NavigationRegistry {
                return new NavigationRegistry;
            },
        );
    }
}

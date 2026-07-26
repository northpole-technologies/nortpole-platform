<?php

declare(strict_types=1);

namespace Northpole\Core\Registration;

use Illuminate\Contracts\Foundation\Application;
use Northpole\Core\Registration\Contracts\ServiceRegistrar;
use Northpole\Core\Registration\Registrars\BootPipelineRegistrar;
use Northpole\Core\Registration\Registrars\FeatureRegistryRegistrar;
use Northpole\Core\Registration\Registrars\InfrastructureRegistrar;
use Northpole\Core\Registration\Registrars\MessagingRegistrar;
use Northpole\Core\Registration\Registrars\RuntimeOperationsRegistrar;

final class RuntimeServiceRegistrar implements ServiceRegistrar
{
    /**
     * @var list<ServiceRegistrar>
     */
    private array $registrars;

    public function __construct()
    {
        $this->registrars = [
            new InfrastructureRegistrar,
            new FeatureRegistryRegistrar,
            new MessagingRegistrar,
            new RuntimeOperationsRegistrar,
            new BootPipelineRegistrar,
        ];
    }

    public function register(
        Application $application
    ): void {
        foreach ($this->registrars as $registrar) {
            $registrar->register(
                $application
            );
        }
    }
}

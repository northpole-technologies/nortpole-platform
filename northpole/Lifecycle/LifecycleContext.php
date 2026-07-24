<?php

declare(strict_types=1);

namespace Northpole\Lifecycle;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use App\Models\OrganisationModule;
use Northpole\Lifecycle\Enums\LifecycleOperation;

final class LifecycleContext
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    private ?OrganisationModule $installation = null;

    public function __construct(
        private readonly LifecycleOperation $operation,
        private readonly MarketplaceModule $module,
        private readonly Organisation $organisation,
    ) {
    }

    public function operation(): LifecycleOperation
    {
        return $this->operation;
    }

    public function module(): MarketplaceModule
    {
        return $this->module;
    }

    public function organisation(): Organisation
    {
        return $this->organisation;
    }

    public function installation(): ?OrganisationModule
    {
        return $this->installation;
    }

    public function setInstallation(
        ?OrganisationModule $installation
    ): self {
        $this->installation = $installation;

        return $this;
    }

    public function hasInstallation(): bool
    {
        return $this->installation !== null;
    }

    public function set(
        string $key,
        mixed $value
    ): self {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->attributes[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists(
            $key,
            $this->attributes
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
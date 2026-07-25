<?php

namespace App\Support\Tenancy;

use App\Models\Organisation;
use Closure;
use LogicException;

class TenantContext
{
    private ?Organisation $organisation = null;

    private int $bypassDepth = 0;

    public function set(Organisation $organisation): void
    {
        $this->organisation = $organisation;
    }

    public function organisation(): Organisation
    {
        if ($this->organisation === null) {
            throw new LogicException(
                'No organisation has been resolved for the current operation.'
            );
        }

        return $this->organisation;
    }

    public function organisationId(): string
    {
        return (string) $this->organisation()->getKey();
    }

    public function hasOrganisation(): bool
    {
        return $this->organisation !== null;
    }

    public function isBypassed(): bool
    {
        return $this->bypassDepth > 0;
    }

    public function runFor(
        Organisation $organisation,
        Closure $callback
    ): mixed {
        $previousOrganisation = $this->organisation;

        $this->organisation = $organisation;

        try {
            return $callback();
        } finally {
            $this->organisation = $previousOrganisation;
        }
    }

    public function withoutTenancy(Closure $callback): mixed
    {
        $this->bypassDepth++;

        try {
            return $callback();
        } finally {
            $this->bypassDepth--;
        }
    }

    public function clear(): void
    {
        $this->organisation = null;
        $this->bypassDepth = 0;
    }
}

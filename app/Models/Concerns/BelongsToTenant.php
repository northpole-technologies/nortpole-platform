<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Support\Tenancy\TenantContext;
use LogicException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model): void {
            $tenantContext = app(TenantContext::class);

            if ($tenantContext->isBypassed()) {
                return;
            }

            if ($tenantContext->hasOrganisation()) {
                $model->setAttribute(
                    $model->getTenantColumn(),
                    $tenantContext->organisationId()
                );

                return;
            }

            if (
                blank(
                    $model->getAttribute(
                        $model->getTenantColumn()
                    )
                )
            ) {
                throw new LogicException(
                    sprintf(
                        'A tenant must be resolved before creating a %s record.',
                        $model::class
                    )
                );
            }
        });

        static::updating(function ($model): void {
            $tenantColumn = $model->getTenantColumn();

            if ($model->isDirty($tenantColumn)) {
                throw new LogicException(
                    sprintf(
                        'The tenant ownership of a %s record cannot be changed.',
                        $model::class
                    )
                );
            }
        });
    }

    public function getTenantColumn(): string
    {
        return 'organisation_id';
    }
}
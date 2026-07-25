<?php

namespace App\Models\Scopes;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use LogicException;

class TenantScope implements Scope
{
    public function apply(
        Builder $builder,
        Model $model
    ): void {
        $tenantContext = app(TenantContext::class);

        if ($tenantContext->isBypassed()) {
            return;
        }

        if (! $tenantContext->hasOrganisation()) {
            throw new LogicException(
                sprintf(
                    'A tenant must be resolved before querying the %s model.',
                    $model::class
                )
            );
        }

        $builder->where(
            $model->qualifyColumn(
                $model->getTenantColumn()
            ),
            $tenantContext->organisationId()
        );
    }
}

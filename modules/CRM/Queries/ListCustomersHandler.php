<?php

declare(strict_types=1);

namespace Modules\CRM\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\CRM\Models\Customer;
use Northpole\Runtime\Queries\Contracts\ModuleQueryContract;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;

final class ListCustomersHandler implements ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): LengthAwarePaginator {
        $parameters = $query->parameters();

        $search = $this->nullableString(
            $parameters,
            'search',
        );

        $status = $this->nullableString(
            $parameters,
            'status',
        );

        $type = $this->nullableString(
            $parameters,
            'type',
        );

        $sort = $this->sortColumn(
            $parameters,
        );

        $direction = $this->sortDirection(
            $parameters,
        );

        $perPage = $this->perPage(
            $parameters,
        );

        return Customer::query()
            ->when(
                $search !== null,
                fn (Builder $builder): Builder => $builder
                    ->where(
                        function (Builder $builder) use ($search): void {
                            $builder
                                ->where(
                                    'name',
                                    'like',
                                    sprintf('%%%s%%', $search),
                                )
                                ->orWhere(
                                    'company_name',
                                    'like',
                                    sprintf('%%%s%%', $search),
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    sprintf('%%%s%%', $search),
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    sprintf('%%%s%%', $search),
                                )
                                ->orWhere(
                                    'mobile',
                                    'like',
                                    sprintf('%%%s%%', $search),
                                )
                                ->orWhere(
                                    'city',
                                    'like',
                                    sprintf('%%%s%%', $search),
                                );
                        },
                    ),
            )
            ->when(
                $status !== null,
                fn (Builder $builder): Builder => $builder
                    ->where('status', $status),
            )
            ->when(
                $type !== null,
                fn (Builder $builder): Builder => $builder
                    ->where('type', $type),
            )
            ->orderBy(
                $sort,
                $direction,
            )
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function nullableString(
        array $parameters,
        string $key,
    ): ?string {
        if (! array_key_exists($key, $parameters)) {
            return null;
        }

        $value = trim(
            (string) $parameters[$key],
        );

        return $value === ''
            ? null
            : $value;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function sortColumn(
        array $parameters,
    ): string {
        $sort = $this->nullableString(
            $parameters,
            'sort',
        );

        $allowed = [
            'name',
            'company_name',
            'email',
            'status',
            'type',
            'created_at',
            'updated_at',
        ];

        if (
            $sort === null
            || ! in_array(
                $sort,
                $allowed,
                true,
            )
        ) {
            return 'name';
        }

        return $sort;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function sortDirection(
        array $parameters,
    ): string {
        $direction = strtolower(
            $this->nullableString(
                $parameters,
                'direction',
            ) ?? 'asc',
        );

        return $direction === 'desc'
            ? 'desc'
            : 'asc';
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function perPage(
        array $parameters,
    ): int {
        $perPage = (int) (
            $parameters['per_page']
            ?? 20
        );

        return max(
            1,
            min(
                100,
                $perPage,
            ),
        );
    }
}

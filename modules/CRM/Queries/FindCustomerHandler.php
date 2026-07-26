<?php

declare(strict_types=1);

namespace Modules\CRM\Queries;

use InvalidArgumentException;
use Modules\CRM\Models\Customer;
use Northpole\Runtime\Queries\Contracts\ModuleQueryContract;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;

final class FindCustomerHandler implements ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): Customer {
        $customerId = trim(
            (string) (
                $query->parameters()['customer_id']
                ?? ''
            ),
        );

        if ($customerId === '') {
            throw new InvalidArgumentException(
                'A customer ID is required.'
            );
        }

        return Customer::query()
            ->findOrFail($customerId);
    }
}

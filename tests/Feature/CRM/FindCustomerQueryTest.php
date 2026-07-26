<?php

declare(strict_types=1);

namespace Tests\Feature\CRM;

use App\Models\Organisation;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\CRM\Models\Customer;
use Modules\CRM\Queries\FindCustomerHandler;
use Northpole\Runtime\Queries\ModuleQuery;
use Northpole\Runtime\Queries\ModuleQueryBus;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Tests\TestCase;

final class FindCustomerQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_query_is_registered_by_the_crm_module(): void
    {
        $registry = $this->app->make(
            ModuleQueryRegistry::class,
        );

        $this->assertSame(
            FindCustomerHandler::class,
            $registry->handler(
                'crm.customer.find',
            ),
        );

        $this->assertSame(
            'crm',
            $registry->owner(
                'crm.customer.find',
            ),
        );
    }

    public function test_it_finds_a_customer_for_the_active_tenant(): void
    {
        $organisation = $this->createOrganisation(
            name: 'Active Organisation',
            slug: 'active-organisation',
        );

        $customer = Customer::factory()
            ->for(
                $organisation,
                'organisation',
            )
            ->create();

        $result = $this->executeFor(
            organisation: $organisation,
            customerId: (string) $customer->getKey(),
        );

        $this->assertTrue(
            $result->is($customer),
        );
    }

    public function test_it_cannot_find_a_customer_from_another_tenant(): void
    {
        $activeOrganisation = $this->createOrganisation(
            name: 'Active Organisation',
            slug: 'active-organisation',
        );

        $otherOrganisation = $this->createOrganisation(
            name: 'Other Organisation',
            slug: 'other-organisation',
        );

        $otherCustomer = Customer::factory()
            ->for(
                $otherOrganisation,
                'organisation',
            )
            ->create();

        $this->expectException(
            ModelNotFoundException::class,
        );

        $this->executeFor(
            organisation: $activeOrganisation,
            customerId: (string) $otherCustomer->getKey(),
        );
    }

    public function test_it_requires_a_customer_id(): void
    {
        $organisation = $this->createOrganisation(
            name: 'Validation Organisation',
            slug: 'validation-organisation',
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A customer ID is required.',
        );

        $this->executeFor(
            organisation: $organisation,
            customerId: '',
        );
    }

    private function executeFor(
        Organisation $organisation,
        string $customerId,
    ): Customer {
        return $this->app
            ->make(TenantContext::class)
            ->runFor(
                $organisation,
                fn (): mixed => $this->app
                    ->make(ModuleQueryBus::class)
                    ->execute(
                        new ModuleQuery(
                            name: 'crm.customer.find',
                            sourceModule: 'tests',
                            parameters: [
                                'customer_id' => $customerId,
                            ],
                        ),
                    ),
            );
    }

    private function createOrganisation(
        string $name,
        string $slug,
    ): Organisation {
        return Organisation::query()->create([
            'name' => $name,
            'slug' => $slug,
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ]);
    }
}

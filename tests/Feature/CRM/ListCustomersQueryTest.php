<?php

declare(strict_types=1);

namespace Tests\Feature\CRM;

use App\Models\Organisation;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Models\Customer;
use Modules\CRM\Queries\ListCustomersHandler;
use Northpole\Runtime\Queries\ModuleQuery;
use Northpole\Runtime\Queries\ModuleQueryBus;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Tests\TestCase;

final class ListCustomersQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_query_is_registered_by_the_crm_module(): void
    {
        $registry = $this->app->make(
            ModuleQueryRegistry::class,
        );

        $this->assertSame(
            ListCustomersHandler::class,
            $registry->handler(
                'crm.customer.list',
            ),
        );

        $this->assertSame(
            'crm',
            $registry->owner(
                'crm.customer.list',
            ),
        );
    }

    public function test_it_lists_only_customers_for_the_active_tenant(): void
    {
        $activeOrganisation = $this->createOrganisation(
            name: 'Active Organisation',
            slug: 'active-organisation',
        );

        $otherOrganisation = $this->createOrganisation(
            name: 'Other Organisation',
            slug: 'other-organisation',
        );

        $activeCustomers = Customer::factory()
            ->count(3)
            ->for(
                $activeOrganisation,
                'organisation',
            )
            ->create();

        Customer::factory()
            ->count(2)
            ->for(
                $otherOrganisation,
                'organisation',
            )
            ->create();

        $result = $this->executeFor(
            $activeOrganisation,
        );

        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $result,
        );

        $this->assertSame(
            3,
            $result->total(),
        );

        $this->assertEqualsCanonicalizing(
            $activeCustomers->modelKeys(),
            collect($result->items())
                ->pluck('id')
                ->all(),
        );
    }

    public function test_it_searches_customer_fields(): void
    {
        $organisation = $this->createOrganisation(
            name: 'Search Organisation',
            slug: 'search-organisation',
        );

        Customer::factory()
            ->for(
                $organisation,
                'organisation',
            )
            ->create([
                'name' => 'Mary Murphy',
                'email' => 'mary@example.test',
                'city' => 'Dublin',
            ]);

        Customer::factory()
            ->for(
                $organisation,
                'organisation',
            )
            ->create([
                'name' => 'John Kelly',
                'email' => 'john@example.test',
                'city' => 'Cork',
            ]);

        $result = $this->executeFor(
            organisation: $organisation,
            parameters: [
                'search' => 'mary',
            ],
        );

        $this->assertSame(
            1,
            $result->total(),
        );

        $this->assertSame(
            'Mary Murphy',
            $result->items()[0]->name,
        );
    }

    public function test_it_filters_by_status_and_type(): void
    {
        $organisation = $this->createOrganisation(
            name: 'Filter Organisation',
            slug: 'filter-organisation',
        );

        Customer::factory()
            ->for(
                $organisation,
                'organisation',
            )
            ->company()
            ->create([
                'status' => 'active',
            ]);

        Customer::factory()
            ->for(
                $organisation,
                'organisation',
            )
            ->company()
            ->inactive()
            ->create();

        Customer::factory()
            ->for(
                $organisation,
                'organisation',
            )
            ->create([
                'status' => 'active',
            ]);

        $result = $this->executeFor(
            organisation: $organisation,
            parameters: [
                'status' => 'active',
                'type' => 'company',
            ],
        );

        $this->assertSame(
            1,
            $result->total(),
        );

        $this->assertSame(
            'company',
            $result->items()[0]->type,
        );

        $this->assertSame(
            'active',
            $result->items()[0]->status,
        );
    }

    public function test_it_applies_sorting_and_pagination(): void
    {
        $organisation = $this->createOrganisation(
            name: 'Pagination Organisation',
            slug: 'pagination-organisation',
        );

        foreach (
            [
                'Charlie Customer',
                'Alice Customer',
                'Bob Customer',
            ] as $name
        ) {
            Customer::factory()
                ->for(
                    $organisation,
                    'organisation',
                )
                ->create([
                    'name' => $name,
                ]);
        }

        $result = $this->executeFor(
            organisation: $organisation,
            parameters: [
                'sort' => 'name',
                'direction' => 'desc',
                'per_page' => 2,
            ],
        );

        $this->assertSame(
            3,
            $result->total(),
        );

        $this->assertSame(
            2,
            $result->perPage(),
        );

        $this->assertSame(
            [
                'Charlie Customer',
                'Bob Customer',
            ],
            collect($result->items())
                ->pluck('name')
                ->all(),
        );
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function executeFor(
        Organisation $organisation,
        array $parameters = [],
    ): LengthAwarePaginator {
        return $this->app
            ->make(TenantContext::class)
            ->runFor(
                $organisation,
                fn (): mixed => $this->app
                    ->make(ModuleQueryBus::class)
                    ->execute(
                        new ModuleQuery(
                            name: 'crm.customer.list',
                            sourceModule: 'tests',
                            parameters: $parameters,
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

<?php

declare(strict_types=1);

namespace Tests\Feature\CRM;

use App\Models\Organisation;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\CRM\Agents\CustomerSummaryAgent;
use Modules\CRM\Models\Customer;
use Northpole\Runtime\Agents\ModuleAgent;
use Northpole\Runtime\Agents\ModuleAgentBus;
use Northpole\Runtime\Agents\ModuleAgentRegistry;
use Tests\TestCase;

final class CustomerSummaryAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_agent_is_registered_by_the_crm_module(): void
    {
        $registry = $this->app->make(
            ModuleAgentRegistry::class,
        );

        $this->assertSame(
            CustomerSummaryAgent::class,
            $registry->handler(
                'crm.customer.summary',
            ),
        );

        $this->assertSame(
            'crm',
            $registry->owner(
                'crm.customer.summary',
            ),
        );
    }

    public function test_it_returns_a_customer_summary_for_the_active_tenant(): void
    {
        $organisation = $this->createOrganisation(
            name: 'NorthPole Test Organisation',
            slug: 'northpole-test-organisation',
        );

        $customer = Customer::factory()
            ->for(
                $organisation,
                'organisation',
            )
            ->company()
            ->create([
                'name' => 'Mary Murphy',
                'company_name' => 'NorthPole Technologies',
                'status' => 'active',
                'email' => 'mary@example.test',
                'phone' => '01 555 0101',
                'mobile' => null,
                'city' => 'Dublin',
                'country' => 'IE',
            ]);

        $result = $this->executeFor(
            organisation: $organisation,
            customerId: (string) $customer->getKey(),
        );

        $this->assertSame(
            (string) $customer->getKey(),
            $result['id'],
        );

        $this->assertSame(
            (string) $organisation->getKey(),
            $result['organisation_id'],
        );

        $this->assertSame(
            'Mary Murphy',
            $result['name'],
        );

        $this->assertSame(
            'NorthPole Technologies',
            $result['company_name'],
        );

        $this->assertSame(
            'company',
            $result['type'],
        );

        $this->assertSame(
            'active',
            $result['status'],
        );

        $this->assertSame(
            'mary@example.test',
            $result['email'],
        );

        $this->assertSame(
            'Mary Murphy is a active company customer. '
            .'They are associated with NorthPole Technologies. '
            .'Their primary contact is mary@example.test.',
            $result['summary'],
        );
    }

    public function test_it_cannot_summarise_a_customer_from_another_tenant(): void
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
            customerId: (string)
                $otherCustomer->getKey(),
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

    /**
     * @return array<string, mixed>
     */
    private function executeFor(
        Organisation $organisation,
        string $customerId,
    ): array {
        return $this->app
            ->make(TenantContext::class)
            ->runFor(
                $organisation,
                fn (): mixed => $this->app
                    ->make(ModuleAgentBus::class)
                    ->execute(
                        new ModuleAgent(
                            name: 'crm.customer.summary',
                            sourceModule: 'tests',
                            input: [
                                'customer_id' => $customerId,
                            ],
                            context: [
                                'requested_by' => 'feature-test',
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

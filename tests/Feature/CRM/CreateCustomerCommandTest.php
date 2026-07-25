<?php

declare(strict_types=1);

namespace Tests\Feature\CRM;

use App\Models\Organisation;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\CRM\Commands\CreateCustomerHandler;
use Modules\CRM\Models\Customer;
use Northpole\Runtime\Commands\ModuleCommand;
use Northpole\Runtime\Commands\ModuleCommandBus;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Events\Contracts\ModuleEventContract;
use Northpole\Runtime\Events\Contracts\ModuleEventListenerContract;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Tests\TestCase;

final class CreateCustomerCommandTest extends TestCase
{
    use RefreshDatabase;
    public function test_the_runtime_creates_a_tenant_customer_and_publishes_an_event(): void
    {
        RecordingCustomerCreatedListener::reset();

        $organisation = Organisation::query()->create([
            'name' => 'NorthPole Test Organisation',
            'slug' => 'northpole-test-organisation',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ]);

        $commandRegistry = $this->app->make(
            ModuleCommandRegistry::class,
        );

        $this->assertSame(
            CreateCustomerHandler::class,
            $commandRegistry->handler(
                'crm.customer.create',
            ),
        );

        $this->assertSame(
            'crm',
            $commandRegistry->owner(
                'crm.customer.create',
            ),
        );

        $eventRegistry = $this->app->make(
            ModuleEventRegistry::class,
        );

        $eventRegistry->listen(
            eventName: 'crm.customer.created',
            listener: RecordingCustomerCreatedListener::class,
            module: 'tests',
        );

        $command = new ModuleCommand(
            name: 'crm.customer.create',
            sourceModule: 'tests',
            payload: [
                'name' => 'Mary Murphy',
                'email' => 'mary@example.test',
                'phone' => '01 555 0101',
                'city' => 'Dublin',
                'country' => 'IE',
                'notes' => 'Created through the NorthPole command bus.',
            ],
            metadata: [
                'requestedBy' => 'feature-test',
            ],
        );

        $customer = $this->app
            ->make(TenantContext::class)
            ->runFor(
                $organisation,
                fn (): mixed => $this->app
                    ->make(ModuleCommandBus::class)
                    ->execute($command),
            );

        $this->assertInstanceOf(
            Customer::class,
            $customer,
        );

        $this->assertSame(
            'Mary Murphy',
            $customer->name,
        );

        $this->assertSame(
            $organisation->getKey(),
            $customer->organisation_id,
        );

        $this->assertDatabaseHas(
            'crm_customers',
            [
                'id' => $customer->getKey(),
                'organisation_id' => $organisation->getKey(),
                'name' => 'Mary Murphy',
                'email' => 'mary@example.test',
                'city' => 'Dublin',
                'country' => 'IE',
                'type' => 'individual',
                'status' => 'active',
            ],
        );

        $this->assertCount(
            1,
            RecordingCustomerCreatedListener::$events,
        );

        $event = RecordingCustomerCreatedListener::$events[0];

        $this->assertSame(
            'crm.customer.created',
            $event->name(),
        );

        $this->assertSame(
            'crm',
            $event->sourceModule(),
        );

        $this->assertSame(
            $customer->getKey(),
            $event->payload()['customerId'],
        );

        $this->assertSame(
            $organisation->getKey(),
            $event->payload()['organisationId'],
        );

        $this->assertSame(
            'Mary Murphy',
            $event->payload()['name'],
        );

        $this->assertSame(
            'crm.customer.create',
            $event->metadata()['commandName'],
        );

        $this->assertSame(
            'tests',
            $event->metadata()['commandSourceModule'],
        );

        $this->assertSame(
            'feature-test',
            $event->metadata()['commandMetadata']['requestedBy'],
        );
    }

    public function test_the_command_rejects_an_empty_customer_name(): void
    {
        $organisation = Organisation::query()->create([
            'name' => 'Validation Test Organisation',
            'slug' => 'validation-test-organisation',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ]);

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A customer name is required.',
        );

        $this->app
            ->make(TenantContext::class)
            ->runFor(
                $organisation,
                fn (): mixed => $this->app
                    ->make(ModuleCommandBus::class)
                    ->execute(
                        new ModuleCommand(
                            name: 'crm.customer.create',
                            sourceModule: 'tests',
                            payload: [
                                'name' => '   ',
                            ],
                        ),
                    ),
            );
    }

    public function test_the_command_cannot_override_tenant_ownership(): void
    {
        $activeOrganisation = Organisation::query()->create([
            'name' => 'Active Organisation',
            'slug' => 'active-organisation',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ]);

        $otherOrganisation = Organisation::query()->create([
            'name' => 'Other Organisation',
            'slug' => 'other-organisation',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ]);

        $customer = $this->app
            ->make(TenantContext::class)
            ->runFor(
                $activeOrganisation,
                fn (): mixed => $this->app
                    ->make(ModuleCommandBus::class)
                    ->execute(
                        new ModuleCommand(
                            name: 'crm.customer.create',
                            sourceModule: 'tests',
                            payload: [
                                'name' => 'Tenant Safe Customer',
                                'organisation_id' => $otherOrganisation
                                    ->getKey(),
                            ],
                        ),
                    ),
            );

        $this->assertSame(
            $activeOrganisation->getKey(),
            $customer->organisation_id,
        );

        $this->assertNotSame(
            $otherOrganisation->getKey(),
            $customer->organisation_id,
        );
    }
}

final class RecordingCustomerCreatedListener implements
    ModuleEventListenerContract
{
    /**
     * @var array<int, ModuleEventContract>
     */
    public static array $events = [];

    public static function reset(): void
    {
        self::$events = [];
    }

    public function handle(
        ModuleEventContract $event,
    ): void {
        self::$events[] = $event;
    }
}
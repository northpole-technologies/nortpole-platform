<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Relationships;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Relationships\RuntimeRelationshipResolver;
use Tests\TestCase;

final class RuntimeRelationshipResolverTest extends TestCase
{
    public function test_it_resolves_a_command_handler_relationship(): void
    {
        $relationships = $this->resolver()->relationshipsFor(
            new RuntimeInspectionReference(
                registry: 'commands',
                module: 'crm',
                key: 'crm.customer.create',
            ),
            'Modules\CRM\Commands\CreateCustomerHandler',
        );

        self::assertCount(
            1,
            $relationships,
        );

        self::assertSame(
            [
                'type' => 'handled_by',
                'label' => 'Handled by',
                'value' => 'Modules\CRM\Commands\CreateCustomerHandler',
                'target' => null,
                'metadata' => [
                    'registry' => 'commands',
                ],
            ],
            $relationships[0]->toArray(),
        );
    }

    public function test_it_resolves_navigation_relationships(): void
    {
        $relationships = $this->resolver()->relationshipsFor(
            new RuntimeInspectionReference(
                registry: 'navigation',
                module: 'crm',
                key: 'crm.customers.index',
            ),
            [
                'label' => 'Customers',
                'route' => 'crm.customers.index',
                'permission' => 'crm.customers.view',
                'group' => 'CRM',
            ],
        );

        $serialised = array_map(
            static fn ($relationship): array => $relationship->toArray(),
            $relationships,
        );

        self::assertCount(
            3,
            $serialised,
        );

        self::assertContains(
            [
                'type' => 'requires',
                'label' => 'Requires permission',
                'value' => 'crm.customers.view',
                'target' => [
                    'registry' => 'permissions',
                    'module' => 'crm',
                    'key' => 'crm.customers.view',
                ],
                'metadata' => [],
            ],
            $serialised,
        );

        self::assertContains(
            [
                'type' => 'routes_to',
                'label' => 'Routes to',
                'value' => 'crm.customers.index',
                'target' => null,
                'metadata' => [],
            ],
            $serialised,
        );
    }

    public function test_it_resolves_published_event_relationships(): void
    {
        $relationships = $this->resolver()->relationshipsFor(
            new RuntimeInspectionReference(
                registry: 'events',
                module: 'crm',
                key: 'crm.customer.created',
            ),
            [
                'published' => true,
                'listeners' => [],
            ],
        );

        self::assertCount(
            1,
            $relationships,
        );

        self::assertSame(
            'published_by',
            $relationships[0]->type,
        );

        self::assertSame(
            'crm',
            $relationships[0]->value,
        );
    }

    public function test_it_resolves_module_dependencies(): void
    {
        $dependencies = $this->resolver()->dependenciesFor(
            new RuntimeInspectionReference(
                registry: 'commands',
                module: 'crm',
                key: 'crm.customer.create',
            ),
        );

        self::assertSame(
            [],
            $dependencies,
        );
    }

    private function resolver(): RuntimeRelationshipResolver
    {
        return $this->app->make(
            RuntimeRelationshipResolver::class,
        );
    }
}

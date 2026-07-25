<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleManifestTest extends TestCase
{
    public function test_it_returns_legacy_dependency_slugs(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm',
                'notifications',
            ],
        ]);

        self::assertSame(
            [
                'crm',
                'notifications',
            ],
            $manifest->dependencies()
        );
    }

    public function test_it_normalises_legacy_dependencies_to_wildcard_constraints(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm',
                'notifications',
            ],
        ]);

        self::assertSame(
            [
                'crm' => '*',
                'notifications' => '*',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_removes_duplicate_legacy_dependencies(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm',
                'crm',
                'notifications',
            ],
        ]);

        self::assertSame(
            [
                'crm',
                'notifications',
            ],
            $manifest->dependencies()
        );

        self::assertSame(
            [
                'crm' => '*',
                'notifications' => '*',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_returns_versioned_dependency_slugs(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
        ]);

        self::assertSame(
            [
                'crm',
                'notifications',
            ],
            $manifest->dependencies()
        );
    }

    public function test_it_returns_versioned_dependency_constraints(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
        ]);

        self::assertSame(
            [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_trims_dependency_names_and_constraints(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                ' crm ' => ' ^2.0 ',
                ' notifications ' => ' >=1.5 <2.0 ',
            ],
        ]);

        self::assertSame(
            [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_returns_empty_dependencies_when_not_defined(): void
    {
        $manifest = $this->manifest();

        self::assertSame(
            [],
            $manifest->dependencies()
        );

        self::assertSame(
            [],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_rejects_a_non_array_dependencies_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [dependencies] must be an array'
        );

        $this->manifest([
            'dependencies' => 'crm',
        ]);
    }

    public function test_it_rejects_an_empty_legacy_dependency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependencies must contain non-empty strings'
        );

        $this->manifest([
            'dependencies' => [
                'crm',
                '',
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_legacy_dependency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependencies must contain non-empty strings'
        );

        $this->manifest([
            'dependencies' => [
                'crm',
                123,
            ],
        ]);
    }

    public function test_it_rejects_an_empty_versioned_dependency_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependency names must be non-empty strings'
        );

        $this->manifest([
            'dependencies' => [
                '' => '^1.0',
            ],
        ]);
    }

    public function test_it_rejects_an_empty_version_constraint(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependency [crm] must have a non-empty version constraint'
        );

        $this->manifest([
            'dependencies' => [
                'crm' => '',
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_version_constraint(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependency [crm] must have a non-empty version constraint'
        );

        $this->manifest([
            'dependencies' => [
                'crm' => 2,
            ],
        ]);
    }

    public function test_it_returns_published_events(): void
    {
        $manifest = $this->manifest([
            'events' => [
                'publishes' => [
                    'customer.created',
                    'customer.updated',
                ],
            ],
        ]);

        self::assertSame(
            [
                'customer.created',
                'customer.updated',
            ],
            $manifest->publishedEvents()
        );
    }

    public function test_it_trims_and_removes_duplicate_published_events(): void
    {
        $manifest = $this->manifest([
            'events' => [
                'publishes' => [
                    ' customer.created ',
                    'customer.created',
                    ' customer.updated ',
                ],
            ],
        ]);

        self::assertSame(
            [
                'customer.created',
                'customer.updated',
            ],
            $manifest->publishedEvents()
        );
    }

    public function test_it_returns_empty_published_events_when_not_defined(): void
    {
        $manifest = $this->manifest();

        self::assertSame(
            [],
            $manifest->publishedEvents()
        );
    }

    public function test_it_returns_event_subscribers(): void
    {
        $manifest = $this->manifest([
            'events' => [
                'subscribes' => [
                    'invoice.paid' => [
                        'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                    ],
                    'customer.created' => [
                        'Modules\\Reports\\Listeners\\CreateCustomerReport',
                        'Modules\\Reports\\Listeners\\NotifyReportOwner',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                'invoice.paid' => [
                    'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                ],
                'customer.created' => [
                    'Modules\\Reports\\Listeners\\CreateCustomerReport',
                    'Modules\\Reports\\Listeners\\NotifyReportOwner',
                ],
            ],
            $manifest->eventSubscribers()
        );
    }

    public function test_it_trims_event_names_and_listener_classes(): void
    {
        $manifest = $this->manifest([
            'events' => [
                'subscribes' => [
                    ' invoice.paid ' => [
                        ' Modules\\Reports\\Listeners\\UpdateRevenueReport ',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                'invoice.paid' => [
                    'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                ],
            ],
            $manifest->eventSubscribers()
        );
    }

    public function test_it_removes_duplicate_listener_classes(): void
    {
        $manifest = $this->manifest([
            'events' => [
                'subscribes' => [
                    'invoice.paid' => [
                        'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                        'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                'invoice.paid' => [
                    'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                ],
            ],
            $manifest->eventSubscribers()
        );
    }

    public function test_it_returns_empty_event_subscribers_when_not_defined(): void
    {
        $manifest = $this->manifest();

        self::assertSame(
            [],
            $manifest->eventSubscribers()
        );
    }

    public function test_it_rejects_a_non_array_events_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [events] must be an object'
        );

        $this->manifest([
            'events' => 'customer.created',
        ]);
    }

    public function test_it_rejects_a_non_list_published_events_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [events.publishes] must be a list'
        );

        $this->manifest([
            'events' => [
                'publishes' => [
                    'customer.created' => true,
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_published_event_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [events.publishes] must contain non-empty strings'
        );

        $this->manifest([
            'events' => [
                'publishes' => [
                    'customer.created',
                    '',
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_published_event_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [events.publishes] must contain non-empty strings'
        );

        $this->manifest([
            'events' => [
                'publishes' => [
                    'customer.created',
                    123,
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_array_subscribers_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [events.subscribes] must be an object'
        );

        $this->manifest([
            'events' => [
                'subscribes' => 'invoice.paid',
            ],
        ]);
    }

    public function test_it_rejects_an_empty_subscribed_event_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest subscribed event names must be non-empty strings'
        );

        $this->manifest([
            'events' => [
                'subscribes' => [
                    '' => [
                        'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_list_subscription(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest event subscription [invoice.paid] must contain a list of listener classes'
        );

        $this->manifest([
            'events' => [
                'subscribes' => [
                    'invoice.paid' => [
                        'listener' => 'Modules\\Reports\\Listeners\\UpdateRevenueReport',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_listener_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest event subscription [invoice.paid] must contain non-empty listener class names'
        );

        $this->manifest([
            'events' => [
                'subscribes' => [
                    'invoice.paid' => [
                        '',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_listener_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest event subscription [invoice.paid] must contain non-empty listener class names'
        );

        $this->manifest([
            'events' => [
                'subscribes' => [
                    'invoice.paid' => [
                        123,
                    ],
                ],
            ],
        ]);
    }

    public function test_it_preserves_the_original_manifest_data(): void
    {
        $data = [
            'name' => 'Reports',
            'slug' => 'reports',
            'version' => '2.1.0',
            'enabled' => true,
            'dependencies' => [
                'crm' => '^2.0',
            ],
            'events' => [
                'publishes' => [
                    'report.created',
                ],
                'subscribes' => [
                    'customer.created' => [
                        'Modules\\Reports\\Listeners\\CreateCustomerReport',
                    ],
                ],
            ],
        ];

        $manifest = new ModuleManifest(
            data: $data,
            path: '/modules/reports',
            manifestPath: '/modules/reports/module.json',
        );

        self::assertSame(
            $data,
            $manifest->toArray()
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function manifest(
        array $overrides = []
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_replace(
                [
                    'name' => 'Reports',
                    'slug' => 'reports',
                    'version' => '1.0.0',
                    'enabled' => true,
                ],
                $overrides
            ),
            path: '/modules/reports',
            manifestPath: '/modules/reports/module.json',
        );
    }
}

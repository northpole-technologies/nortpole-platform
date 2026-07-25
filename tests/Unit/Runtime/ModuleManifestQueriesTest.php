<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleManifestQueriesTest extends TestCase
{
    public function test_it_returns_handled_queries(): void
    {
        $manifest = $this->manifest([
            'queries' => [
                'handles' => [
                    'crm.customer.find' => 'Modules\\CRM\\Queries\\FindCustomerHandler',
                    'crm.customer.list' => 'Modules\\CRM\\Queries\\ListCustomersHandler',
                ],
            ],
        ]);

        self::assertSame(
            [
                'crm.customer.find' => 'Modules\\CRM\\Queries\\FindCustomerHandler',
                'crm.customer.list' => 'Modules\\CRM\\Queries\\ListCustomersHandler',
            ],
            $manifest->handledQueries(),
        );
    }

    public function test_it_trims_query_names_and_handler_classes(): void
    {
        $manifest = $this->manifest([
            'queries' => [
                'handles' => [
                    ' crm.customer.find ' => ' Modules\\CRM\\Queries\\FindCustomerHandler ',
                ],
            ],
        ]);

        self::assertSame(
            [
                'crm.customer.find' => 'Modules\\CRM\\Queries\\FindCustomerHandler',
            ],
            $manifest->handledQueries(),
        );
    }

    public function test_it_returns_empty_handled_queries_when_not_defined(): void
    {
        $manifest = $this->manifest();

        self::assertSame(
            [],
            $manifest->handledQueries(),
        );
    }

    public function test_it_returns_empty_handled_queries_when_queries_are_empty(): void
    {
        $manifest = $this->manifest([
            'queries' => [],
        ]);

        self::assertSame(
            [],
            $manifest->handledQueries(),
        );
    }

    public function test_it_rejects_a_non_array_queries_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest field [queries] must be an object',
        );

        $this->manifest([
            'queries' => 'crm.customer.find',
        ]);
    }

    public function test_it_rejects_a_non_array_handles_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest field [queries.handles] must be an object',
        );

        $this->manifest([
            'queries' => [
                'handles' => 'Modules\\CRM\\Queries\\FindCustomerHandler',
            ],
        ]);
    }

    public function test_it_rejects_an_empty_handled_query_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest handled query names must be non-empty strings',
        );

        $this->manifest([
            'queries' => [
                'handles' => [
                    '' => 'Modules\\CRM\\Queries\\FindCustomerHandler',
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_query_handler_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest query [crm.customer.find] must have a non-empty handler class',
        );

        $this->manifest([
            'queries' => [
                'handles' => [
                    'crm.customer.find' => '',
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_query_handler_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest query [crm.customer.find] must have a non-empty handler class',
        );

        $this->manifest([
            'queries' => [
                'handles' => [
                    'crm.customer.find' => 123,
                ],
            ],
        ]);
    }

    public function test_it_preserves_query_data_in_the_original_manifest(): void
    {
        $data = [
            'name' => 'CRM',
            'slug' => 'crm',
            'version' => '1.0.0',
            'enabled' => true,
            'queries' => [
                'handles' => [
                    'crm.customer.find' => 'Modules\\CRM\\Queries\\FindCustomerHandler',
                ],
            ],
        ];

        $manifest = new ModuleManifest(
            data: $data,
            path: '/modules/crm',
            manifestPath: '/modules/crm/module.json',
        );

        self::assertSame(
            $data,
            $manifest->toArray(),
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function manifest(
        array $overrides = [],
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_replace(
                [
                    'name' => 'CRM',
                    'slug' => 'crm',
                    'version' => '1.0.0',
                    'enabled' => true,
                ],
                $overrides,
            ),
            path: '/modules/crm',
            manifestPath: '/modules/crm/module.json',
        );
    }
}

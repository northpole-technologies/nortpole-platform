<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Inspection;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspection\RuntimeInspectionService;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Tests\TestCase;

final class RuntimeInspectionServiceTest extends TestCase
{
    public function test_it_inspects_a_registered_command(): void
    {
        $result = $this->service()->inspect(
            new RuntimeInspectionReference(
                registry: 'commands',
                module: 'crm',
                key: 'crm.customer.create',
            ),
        );

        self::assertNotNull(
            $result,
        );

        self::assertSame(
            'commands',
            $result->registry(),
        );

        self::assertSame(
            'crm',
            $result->module(),
        );

        self::assertSame(
            'crm.customer.create',
            $result->key(),
        );

        self::assertIsString(
            $result->value,
        );

        self::assertStringContainsString(
            'CreateCustomerHandler',
            $result->value,
        );
    }

    public function test_it_inspects_a_registered_query(): void
    {
        $queries = $this->app
            ->make(
                RuntimeMetadataService::class,
            )
            ->queries();

        $crmQueries = $queries['crm'] ?? [];

        self::assertIsArray(
            $crmQueries,
        );

        self::assertNotEmpty(
            $crmQueries,
        );

        $queryKey = array_key_first(
            $crmQueries,
        );

        self::assertIsString(
            $queryKey,
        );

        $result = $this->service()->inspect(
            new RuntimeInspectionReference(
                registry: 'queries',
                module: 'crm',
                key: $queryKey,
            ),
        );

        self::assertNotNull(
            $result,
        );

        self::assertSame(
            'queries',
            $result->registry(),
        );

        self::assertSame(
            'crm',
            $result->module(),
        );

        self::assertSame(
            $queryKey,
            $result->key(),
        );

        self::assertSame(
            $crmQueries[$queryKey],
            $result->value,
        );
    }

    public function test_it_inspects_a_registered_agent(): void
    {
        $result = $this->service()->inspect(
            new RuntimeInspectionReference(
                registry: 'agents',
                module: 'crm',
                key: 'crm.customer.summary',
            ),
        );

        self::assertNotNull(
            $result,
        );

        self::assertSame(
            'agents',
            $result->registry(),
        );

        self::assertStringContainsString(
            'CustomerSummaryAgent',
            (string) $result->value,
        );
    }

    public function test_reference_matching_is_case_insensitive(): void
    {
        $result = $this->service()->inspect(
            new RuntimeInspectionReference(
                registry: 'COMMANDS',
                module: 'CRM',
                key: 'CRM.CUSTOMER.CREATE',
            ),
        );

        self::assertNotNull(
            $result,
        );

        self::assertSame(
            'crm.customer.create',
            $result->key(),
        );
    }

    public function test_it_returns_null_for_an_unknown_registration(): void
    {
        $result = $this->service()->inspect(
            new RuntimeInspectionReference(
                registry: 'commands',
                module: 'crm',
                key: 'crm.command.missing',
            ),
        );

        self::assertNull(
            $result,
        );
    }

    public function test_it_returns_null_for_an_unknown_registry(): void
    {
        $result = $this->service()->inspect(
            new RuntimeInspectionReference(
                registry: 'unknown',
                module: 'crm',
                key: 'crm.customer.create',
            ),
        );

        self::assertNull(
            $result,
        );
    }

    private function service(): RuntimeInspectionService
    {
        return $this->app->make(
            RuntimeInspectionService::class,
        );
    }
}

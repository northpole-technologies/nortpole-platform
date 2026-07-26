<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Inspector;

use Northpole\Runtime\Inspection\Contracts\RuntimeInspectionServiceContract;
use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspector\RuntimeInspectorService;
use Tests\TestCase;

final class RuntimeInspectorServiceTest extends TestCase
{
    public function test_it_inspects_a_registered_runtime_entry(): void
    {
        $reference = new RuntimeInspectionReference(
            registry: 'commands',
            module: 'crm',
            key: 'crm.customer.create',
        );

        $inspection = $this->app
            ->make(
                RuntimeInspectionServiceContract::class,
            )
            ->inspect(
                $reference,
            );

        self::assertNotNull(
            $inspection,
        );

        $result = $this->service()->inspect(
            $reference,
        );

        self::assertTrue(
            $result->found(),
        );

        self::assertSame(
            $inspection->reference->identifier(),
            $result->reference->identifier(),
        );

        self::assertSame(
            $inspection->value,
            $result->metadata(),
        );

        self::assertSame(
            [
                [
                    'type' => 'handled_by',
                    'label' => 'Handled by',
                    'value' => 'Modules\CRM\Commands\CreateCustomerHandler',
                    'target' => null,
                    'metadata' => [
                        'registry' => 'commands',
                    ],
                ],
            ],
            $result->relationships,
        );

        self::assertSame(
            [],
            $result->dependencies,
        );

        self::assertSame(
            [],
            $result->warnings,
        );
    }

    public function test_it_resolves_source_information_for_a_registered_class(): void
    {
        $result = $this->service()->inspect(
            new RuntimeInspectionReference(
                registry: 'commands',
                module: 'crm',
                key: 'crm.customer.create',
            ),
        );

        self::assertTrue(
            $result->found(),
        );

        self::assertIsString(
            $result->source['class'],
        );

        self::assertStringContainsString(
            'CreateCustomerHandler',
            $result->source['class'],
        );

        self::assertIsString(
            $result->source['file'],
        );

        self::assertFileExists(
            $result->source['file'],
        );
    }

    public function test_it_returns_an_empty_result_for_a_missing_registration(): void
    {
        $reference = new RuntimeInspectionReference(
            registry: 'commands',
            module: 'crm',
            key: 'crm.command.missing',
        );

        $result = $this->service()->inspect(
            $reference,
        );

        self::assertFalse(
            $result->found(),
        );

        self::assertSame(
            $reference,
            $result->reference,
        );

        self::assertNull(
            $result->inspection,
        );

        self::assertNull(
            $result->metadata(),
        );

        self::assertSame(
            [
                'class' => null,
                'file' => null,
            ],
            $result->source,
        );

        self::assertSame(
            [],
            $result->relationships,
        );

        self::assertSame(
            [],
            $result->dependencies,
        );

        self::assertSame(
            [],
            $result->warnings,
        );
    }

    private function service(): RuntimeInspectorService
    {
        return $this->app->make(
            RuntimeInspectorService::class,
        );
    }
}

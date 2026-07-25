<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Repair;

use InvalidArgumentException;
use Northpole\Runtime\Repair\RepairAction;
use Northpole\Runtime\Repair\RepairActionType;
use Northpole\Runtime\Repair\RepairRecommendation;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class RepairRecommendationTest extends TestCase
{
    public function test_it_creates_a_repair_recommendation(): void
    {
        $recommendation = new RepairRecommendation(
            code: 'repair.command_handler',
            title: 'Repair command handler',
            description: 'Correct the registered command handler.',
            severity: ValidationSeverity::Error,
            module: 'crm',
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Review manifest',
                    content: 'Check the CRM manifest declaration.',
                ),
            ],
        );

        $this->assertSame(
            'repair.command_handler',
            $recommendation->code(),
        );

        $this->assertSame(
            'Repair command handler',
            $recommendation->title(),
        );

        $this->assertSame(
            ValidationSeverity::Error,
            $recommendation->severity(),
        );

        $this->assertSame(
            'crm',
            $recommendation->module(),
        );

        $this->assertCount(
            1,
            $recommendation->actions(),
        );
    }

    public function test_it_serialises_to_an_array(): void
    {
        $recommendation = new RepairRecommendation(
            code: 'repair.validation_rule',
            title: 'Repair validation rule',
            description: 'Review the failed validation rule.',
            severity: ValidationSeverity::Warning,
        );

        $this->assertSame(
            [
                'code' => 'repair.validation_rule',
                'title' => 'Repair validation rule',
                'description' =>
                    'Review the failed validation rule.',
                'severity' => 'warning',
                'module' => null,
                'actions' => [],
            ],
            $recommendation->toArray(),
        );
    }

    public function test_it_rejects_non_action_values(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RepairRecommendation(
            code: 'repair.invalid',
            title: 'Invalid repair',
            description: 'Invalid repair action collection.',
            severity: ValidationSeverity::Error,
            actions: [
                'not-an-action',
            ],
        );
    }
}

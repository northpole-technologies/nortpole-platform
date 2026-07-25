<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Repair;

use InvalidArgumentException;
use Northpole\Runtime\Repair\RepairAction;
use Northpole\Runtime\Repair\RepairActionType;
use PHPUnit\Framework\TestCase;

final class RepairActionTest extends TestCase
{
    public function test_it_creates_a_repair_action(): void
    {
        $action = new RepairAction(
            type: RepairActionType::PowerShell,
            label: 'Run the validation tests',
            content: 'php artisan test',
        );

        $this->assertSame(
            RepairActionType::PowerShell,
            $action->type(),
        );

        $this->assertSame(
            'Run the validation tests',
            $action->label(),
        );

        $this->assertSame(
            'php artisan test',
            $action->content(),
        );
    }

    public function test_it_serialises_to_an_array(): void
    {
        $action = new RepairAction(
            type: RepairActionType::Instruction,
            label: 'Review the handler',
            content: 'Confirm the handler implements the contract.',
        );

        $this->assertSame(
            [
                'type' => 'instruction',
                'label' => 'Review the handler',
                'content' =>
                    'Confirm the handler implements the contract.',
            ],
            $action->toArray(),
        );
    }

    public function test_it_rejects_an_empty_label(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RepairAction(
            type: RepairActionType::Instruction,
            label: ' ',
            content: 'Review the handler.',
        );
    }

    public function test_it_rejects_empty_content(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RepairAction(
            type: RepairActionType::Instruction,
            label: 'Review the handler',
            content: ' ',
        );
    }
}

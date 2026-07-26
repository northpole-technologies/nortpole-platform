<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Manifest;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleManifestAgentHandlersTest extends TestCase
{
    public function test_it_returns_normalised_agent_handlers(): void
    {
        $manifest = $this->manifest([
            'agents' => [
                'handles' => [
                    ' crm.customer.lookup ' =>
                        ' Modules\CRM\Agents\CustomerLookupAgentHandler ',
                    'inventory.stock.reserve' =>
                        'Modules\Inventory\Agents\ReserveStockAgentHandler',
                ],
            ],
        ]);

        self::assertSame(
            [
                'crm.customer.lookup' =>
                    'Modules\CRM\Agents\CustomerLookupAgentHandler',
                'inventory.stock.reserve' =>
                    'Modules\Inventory\Agents\ReserveStockAgentHandler',
            ],
            $manifest->handledAgents()
        );
    }

    public function test_it_returns_no_agent_handlers_by_default(): void
    {
        self::assertSame(
            [],
            $this->manifest()->handledAgents()
        );
    }

    public function test_it_rejects_non_object_agents(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [agents] must be an object'
        );

        $this->manifest([
            'agents' => 'invalid',
        ]);
    }

    public function test_it_rejects_non_object_agent_handlers(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [agents.handles] must be an object'
        );

        $this->manifest([
            'agents' => [
                'handles' => 'invalid',
            ],
        ]);
    }

    public function test_it_rejects_empty_agent_names(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest handled agent names must be non-empty strings'
        );

        $this->manifest([
            'agents' => [
                'handles' => [
                    '   ' =>
                        'Modules\CRM\Agents\CustomerLookupAgentHandler',
                ],
            ],
        ]);
    }

    public function test_it_rejects_empty_agent_handler_classes(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest agent [crm.customer.lookup] must have a non-empty handler class'
        );

        $this->manifest([
            'agents' => [
                'handles' => [
                    'crm.customer.lookup' => '   ',
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function manifest(
        array $overrides = [],
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_replace_recursive(
                [
                    'name' => 'CRM',
                    'slug' => 'crm',
                    'version' => '1.0.0',
                ],
                $overrides,
            ),
            path: '/modules/CRM',
            manifestPath: '/modules/CRM/module.json',
        );
    }
}
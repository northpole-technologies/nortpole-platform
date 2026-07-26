<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Agents;

use InvalidArgumentException;
use Northpole\Runtime\Agents\Contracts\ModuleAgentContract;
use Northpole\Runtime\Agents\Contracts\ModuleAgentHandlerContract;
use Northpole\Runtime\Agents\ModuleAgentRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleAgentRegistryTest extends TestCase
{
    public function test_it_registers_agent_handlers(): void
    {
        $registry = new ModuleAgentRegistry;

        $registry->register(
            agentName: 'crm.customer.lookup',
            handler: CustomerLookupAgentHandler::class,
            module: 'crm',
        );

        self::assertTrue(
            $registry->hasHandler(
                'crm.customer.lookup'
            )
        );

        self::assertSame(
            CustomerLookupAgentHandler::class,
            $registry->handler(
                'crm.customer.lookup'
            )
        );

        self::assertSame(
            'crm',
            $registry->owner(
                'crm.customer.lookup'
            )
        );

        self::assertSame(
            1,
            $registry->count()
        );
    }

    public function test_it_orders_registered_agent_handlers(): void
    {
        $registry = new ModuleAgentRegistry;

        $registry
            ->register(
                'inventory.stock.reserve',
                ReserveStockAgentHandler::class,
                'inventory',
            )
            ->register(
                'crm.customer.lookup',
                CustomerLookupAgentHandler::class,
                'crm',
            );

        self::assertSame(
            [
                'crm.customer.lookup' =>
                    CustomerLookupAgentHandler::class,
                'inventory.stock.reserve' =>
                    ReserveStockAgentHandler::class,
            ],
            $registry->all()
        );

        self::assertSame(
            [
                'crm.customer.lookup' => 'crm',
                'inventory.stock.reserve' => 'inventory',
            ],
            $registry->owners()
        );
    }

    public function test_it_rejects_duplicate_agent_handlers(): void
    {
        $registry = new ModuleAgentRegistry;

        $registry->register(
            'crm.customer.lookup',
            CustomerLookupAgentHandler::class,
            'crm',
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            sprintf(
                'Agent [crm.customer.lookup] already has handler [%s] registered by module [crm].',
                CustomerLookupAgentHandler::class,
            )
        );

        $registry->register(
            'crm.customer.lookup',
            ReserveStockAgentHandler::class,
            'inventory',
        );
    }

    public function test_it_rejects_empty_agent_names(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A module agent name cannot be empty.'
        );

        (new ModuleAgentRegistry)->register(
            '   ',
            CustomerLookupAgentHandler::class,
            'crm',
        );
    }

    public function test_it_rejects_empty_handler_classes(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A module agent handler class cannot be empty.'
        );

        (new ModuleAgentRegistry)->register(
            'crm.customer.lookup',
            '   ',
            'crm',
        );
    }

    public function test_it_rejects_empty_handler_owners(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A module agent handler owner cannot be empty.'
        );

        (new ModuleAgentRegistry)->register(
            'crm.customer.lookup',
            CustomerLookupAgentHandler::class,
            '   ',
        );
    }

    public function test_it_returns_null_for_empty_lookups(): void
    {
        $registry = new ModuleAgentRegistry;

        self::assertNull(
            $registry->handler('   ')
        );

        self::assertNull(
            $registry->owner('   ')
        );

        self::assertFalse(
            $registry->hasHandler('   ')
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new ModuleAgentRegistry;

        $registry
            ->register(
                'crm.customer.lookup',
                CustomerLookupAgentHandler::class,
                'crm',
            )
            ->clear();

        self::assertSame(
            [],
            $registry->all()
        );

        self::assertSame(
            [],
            $registry->owners()
        );

        self::assertSame(
            0,
            $registry->count()
        );
    }
}

final class CustomerLookupAgentHandler implements ModuleAgentHandlerContract
{
    public function handle(
        ModuleAgentContract $agent,
    ): mixed {
        return null;
    }
}

final class ReserveStockAgentHandler implements ModuleAgentHandlerContract
{
    public function handle(
        ModuleAgentContract $agent,
    ): mixed {
        return null;
    }
}
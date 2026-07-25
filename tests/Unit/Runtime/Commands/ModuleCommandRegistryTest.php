<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Commands;

use InvalidArgumentException;
use Northpole\Runtime\Commands\Contracts\ModuleCommandContract;
use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleCommandRegistryTest extends TestCase
{
    public function test_it_registers_a_command_handler(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            'crm.customer.create',
            RegistryCreateCustomerHandler::class,
            'crm',
        );

        self::assertSame(
            RegistryCreateCustomerHandler::class,
            $registry->handler(
                'crm.customer.create',
            ),
        );

        self::assertSame(
            'crm',
            $registry->owner(
                'crm.customer.create',
            ),
        );

        self::assertTrue(
            $registry->hasHandler(
                'crm.customer.create',
            ),
        );

        self::assertSame(
            1,
            $registry->count(),
        );
    }

    public function test_it_registers_multiple_command_handlers(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry
            ->register(
                'crm.customer.create',
                RegistryCreateCustomerHandler::class,
                'crm',
            )
            ->register(
                'inventory.stock.reserve',
                RegistryReserveStockHandler::class,
                'inventory',
            );

        self::assertSame(
            RegistryCreateCustomerHandler::class,
            $registry->handler(
                'crm.customer.create',
            ),
        );

        self::assertSame(
            RegistryReserveStockHandler::class,
            $registry->handler(
                'inventory.stock.reserve',
            ),
        );

        self::assertSame(
            2,
            $registry->count(),
        );
    }

    public function test_it_rejects_a_second_handler_for_the_same_command(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            'crm.customer.create',
            RegistryCreateCustomerHandler::class,
            'crm',
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            sprintf(
                'Command [crm.customer.create] already has handler [%s] registered by module [crm].',
                RegistryCreateCustomerHandler::class,
            ),
        );

        $registry->register(
            'crm.customer.create',
            RegistryReserveStockHandler::class,
            'inventory',
        );
    }

    public function test_it_rejects_an_empty_command_name(): void
    {
        $registry = new ModuleCommandRegistry;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module command name cannot be empty.',
        );

        $registry->register(
            '   ',
            RegistryCreateCustomerHandler::class,
            'crm',
        );
    }

    public function test_it_rejects_an_empty_handler_class(): void
    {
        $registry = new ModuleCommandRegistry;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module command handler class cannot be empty.',
        );

        $registry->register(
            'crm.customer.create',
            '   ',
            'crm',
        );
    }

    public function test_it_rejects_an_empty_module_owner(): void
    {
        $registry = new ModuleCommandRegistry;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module command handler owner cannot be empty.',
        );

        $registry->register(
            'crm.customer.create',
            RegistryCreateCustomerHandler::class,
            '   ',
        );
    }

    public function test_it_returns_null_for_an_unknown_command(): void
    {
        $registry = new ModuleCommandRegistry;

        self::assertNull(
            $registry->handler(
                'crm.customer.unknown',
            ),
        );

        self::assertNull(
            $registry->owner(
                'crm.customer.unknown',
            ),
        );

        self::assertFalse(
            $registry->hasHandler(
                'crm.customer.unknown',
            ),
        );
    }

    public function test_it_returns_registered_handlers_sorted_by_command_name(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry
            ->register(
                'inventory.stock.reserve',
                RegistryReserveStockHandler::class,
                'inventory',
            )
            ->register(
                'crm.customer.create',
                RegistryCreateCustomerHandler::class,
                'crm',
            );

        self::assertSame(
            [
                'crm.customer.create' => RegistryCreateCustomerHandler::class,
                'inventory.stock.reserve' => RegistryReserveStockHandler::class,
            ],
            $registry->all(),
        );

        self::assertSame(
            [
                'crm.customer.create' => 'crm',
                'inventory.stock.reserve' => 'inventory',
            ],
            $registry->owners(),
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            'crm.customer.create',
            RegistryCreateCustomerHandler::class,
            'crm',
        );

        $registry->clear();

        self::assertSame(
            [],
            $registry->all(),
        );

        self::assertSame(
            [],
            $registry->owners(),
        );

        self::assertSame(
            0,
            $registry->count(),
        );

        self::assertFalse(
            $registry->hasHandler(
                'crm.customer.create',
            ),
        );
    }
}

final class RegistryCreateCustomerHandler implements ModuleCommandHandlerContract
{
    public function handle(
        ModuleCommandContract $command,
    ): mixed {
        return null;
    }
}

final class RegistryReserveStockHandler implements ModuleCommandHandlerContract
{
    public function handle(
        ModuleCommandContract $command,
    ): mixed {
        return null;
    }
}

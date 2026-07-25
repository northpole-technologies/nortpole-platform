<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Queries;

use InvalidArgumentException;
use Northpole\Runtime\Queries\Contracts\ModuleQueryContract;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleQueryRegistryTest extends TestCase
{
    public function test_it_registers_a_query_handler(): void
    {
        $registry = new ModuleQueryRegistry;

        $result = $registry->register(
            'crm.customer.find',
            FindCustomerQueryHandler::class,
            'crm',
        );

        self::assertSame(
            $registry,
            $result,
        );

        self::assertSame(
            FindCustomerQueryHandler::class,
            $registry->handler(
                'crm.customer.find',
            ),
        );

        self::assertSame(
            'crm',
            $registry->owner(
                'crm.customer.find',
            ),
        );

        self::assertTrue(
            $registry->hasHandler(
                'crm.customer.find',
            ),
        );

        self::assertSame(
            1,
            $registry->count(),
        );
    }

    public function test_it_uses_platform_as_the_default_owner(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            'platform.health.status',
            HealthStatusQueryHandler::class,
        );

        self::assertSame(
            'platform',
            $registry->owner(
                'platform.health.status',
            ),
        );
    }

    public function test_it_normalises_registered_values(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            '  crm.customer.find  ',
            '  '.FindCustomerQueryHandler::class.'  ',
            '  crm  ',
        );

        self::assertSame(
            FindCustomerQueryHandler::class,
            $registry->handler(
                'crm.customer.find',
            ),
        );

        self::assertSame(
            'crm',
            $registry->owner(
                'crm.customer.find',
            ),
        );
    }

    public function test_it_returns_null_for_unknown_queries(): void
    {
        $registry = new ModuleQueryRegistry;

        self::assertNull(
            $registry->handler(
                'crm.customer.find',
            ),
        );

        self::assertNull(
            $registry->owner(
                'crm.customer.find',
            ),
        );

        self::assertFalse(
            $registry->hasHandler(
                'crm.customer.find',
            ),
        );
    }

    public function test_it_returns_null_for_empty_lookup_names(): void
    {
        $registry = new ModuleQueryRegistry;

        self::assertNull(
            $registry->handler('   '),
        );

        self::assertNull(
            $registry->owner('   '),
        );

        self::assertFalse(
            $registry->hasHandler('   '),
        );
    }

    public function test_it_returns_handlers_sorted_by_query_name(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            'sales.order.find',
            FindOrderQueryHandler::class,
            'sales',
        );

        $registry->register(
            'crm.customer.find',
            FindCustomerQueryHandler::class,
            'crm',
        );

        self::assertSame(
            [
                'crm.customer.find' => FindCustomerQueryHandler::class,
                'sales.order.find' => FindOrderQueryHandler::class,
            ],
            $registry->all(),
        );
    }

    public function test_it_returns_owners_sorted_by_query_name(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            'sales.order.find',
            FindOrderQueryHandler::class,
            'sales',
        );

        $registry->register(
            'crm.customer.find',
            FindCustomerQueryHandler::class,
            'crm',
        );

        self::assertSame(
            [
                'crm.customer.find' => 'crm',
                'sales.order.find' => 'sales',
            ],
            $registry->owners(),
        );
    }

    public function test_it_rejects_duplicate_query_handlers(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            'crm.customer.find',
            FindCustomerQueryHandler::class,
            'crm',
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            sprintf(
                'Query [crm.customer.find] already has handler [%s] registered by module [crm].',
                FindCustomerQueryHandler::class,
            ),
        );

        $registry->register(
            'crm.customer.find',
            AlternativeFindCustomerQueryHandler::class,
            'sales',
        );
    }

    public function test_it_rejects_an_empty_query_name(): void
    {
        $registry = new ModuleQueryRegistry;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module query name cannot be empty.',
        );

        $registry->register(
            '   ',
            FindCustomerQueryHandler::class,
            'crm',
        );
    }

    public function test_it_rejects_an_empty_handler_class(): void
    {
        $registry = new ModuleQueryRegistry;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module query handler class cannot be empty.',
        );

        $registry->register(
            'crm.customer.find',
            '   ',
            'crm',
        );
    }

    public function test_it_rejects_an_empty_owner(): void
    {
        $registry = new ModuleQueryRegistry;

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module query handler owner cannot be empty.',
        );

        $registry->register(
            'crm.customer.find',
            FindCustomerQueryHandler::class,
            '   ',
        );
    }

    public function test_it_can_clear_all_query_handlers(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            'crm.customer.find',
            FindCustomerQueryHandler::class,
            'crm',
        );

        $registry->register(
            'sales.order.find',
            FindOrderQueryHandler::class,
            'sales',
        );

        $result = $registry->clear();

        self::assertSame(
            $registry,
            $result,
        );

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
    }
}

final class FindCustomerQueryHandler implements ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed {
        return null;
    }
}

final class AlternativeFindCustomerQueryHandler implements ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed {
        return null;
    }
}

final class FindOrderQueryHandler implements ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed {
        return null;
    }
}

final class HealthStatusQueryHandler implements ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed {
        return null;
    }
}

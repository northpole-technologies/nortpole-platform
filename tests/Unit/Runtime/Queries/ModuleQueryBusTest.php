<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Queries;

use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use Northpole\Runtime\Queries\Contracts\ModuleQueryContract;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;
use Northpole\Runtime\Queries\ModuleQuery;
use Northpole\Runtime\Queries\ModuleQueryBus;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleQueryBusTest extends TestCase
{
    public function test_it_executes_a_registered_query_handler(): void
    {
        RecordingModuleQueryHandler::reset();

        $registry = new ModuleQueryRegistry();

        $registry->register(
            'crm.customer.find',
            RecordingModuleQueryHandler::class,
            'crm',
        );

        $bus = new ModuleQueryBus(
            $registry,
            static fn (string $handler): object => new $handler(),
        );

        $query = new ModuleQuery(
            name: 'crm.customer.find',
            sourceModule: 'sales',
            parameters: [
                'customerId' => 501,
            ],
        );

        $result = $bus->execute($query);

        self::assertSame(
            [
                'id' => 501,
                'name' => 'John Smith',
            ],
            $result,
        );

        self::assertCount(
            1,
            RecordingModuleQueryHandler::$queries,
        );

        self::assertSame(
            $query,
            RecordingModuleQueryHandler::$queries[0],
        );

        self::assertSame(
            501,
            RecordingModuleQueryHandler::$queries[0]
                ->parameters()['customerId'],
        );
    }

    public function test_it_returns_the_handler_result(): void
    {
        $registry = new ModuleQueryRegistry();

        $registry->register(
            'inventory.stock.available',
            AvailableStockQueryHandler::class,
            'inventory',
        );

        $bus = new ModuleQueryBus(
            $registry,
            static fn (string $handler): object => new $handler(),
        );

        $result = $bus->execute(
            new ModuleQuery(
                name: 'inventory.stock.available',
                sourceModule: 'orders',
                parameters: [
                    'productId' => 88,
                ],
            ),
        );

        self::assertSame(
            [
                'productId' => 88,
                'available' => 42,
            ],
            $result,
        );
    }

    public function test_it_rejects_a_query_without_a_registered_handler(): void
    {
        $bus = new ModuleQueryBus(
            new ModuleQueryRegistry(),
            static fn (string $handler): object => new $handler(),
        );

        $this->expectException(
            LogicException::class,
        );

        $this->expectExceptionMessage(
            'No handler is registered for module query [crm.customer.find].',
        );

        $bus->execute(
            new ModuleQuery(
                name: 'crm.customer.find',
                sourceModule: 'crm',
            ),
        );
    }

    public function test_it_rejects_a_resolved_handler_with_the_wrong_contract(): void
    {
        $registry = new ModuleQueryRegistry();

        $registry->register(
            'crm.customer.find',
            InvalidModuleQueryHandler::class,
            'crm',
        );

        $bus = new ModuleQueryBus(
            $registry,
            static fn (): object => new \stdClass(),
        );

        $this->expectException(
            LogicException::class,
        );

        $this->expectExceptionMessage(
            sprintf(
                'Resolved module query handler [%s] must implement [%s].',
                InvalidModuleQueryHandler::class,
                ModuleQueryHandlerContract::class,
            ),
        );

        $bus->execute(
            new ModuleQuery(
                name: 'crm.customer.find',
                sourceModule: 'sales',
            ),
        );
    }

    public function test_it_preserves_query_data(): void
    {
        $requestedAt = new DateTimeImmutable(
            '2026-07-25 09:30:00',
        );

        $query = new ModuleQuery(
            name: 'crm.customer.find',
            sourceModule: 'sales',
            parameters: [
                'customerId' => 501,
            ],
            metadata: [
                'tenantId' => 10,
                'requestedBy' => 25,
            ],
            requestedAt: $requestedAt,
        );

        self::assertSame(
            'crm.customer.find',
            $query->name(),
        );

        self::assertSame(
            'sales',
            $query->sourceModule(),
        );

        self::assertSame(
            501,
            $query->parameters()['customerId'],
        );

        self::assertSame(
            10,
            $query->metadata()['tenantId'],
        );

        self::assertSame(
            25,
            $query->metadata()['requestedBy'],
        );

        self::assertSame(
            $requestedAt,
            $query->requestedAt(),
        );
    }

    public function test_it_assigns_one_immutable_request_time_automatically(): void
    {
        $query = new ModuleQuery(
            name: 'crm.customer.find',
            sourceModule: 'sales',
        );

        $firstRead = $query->requestedAt();

        usleep(1000);

        $secondRead = $query->requestedAt();

        self::assertSame(
            $firstRead,
            $secondRead,
        );
    }

    public function test_it_rejects_an_empty_query_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module query name cannot be empty.',
        );

        new ModuleQuery(
            name: '   ',
            sourceModule: 'crm',
        );
    }

    public function test_it_rejects_an_empty_source_module(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module query source module cannot be empty.',
        );

        new ModuleQuery(
            name: 'crm.customer.find',
            sourceModule: '   ',
        );
    }

    public function test_it_exposes_the_query_registry(): void
    {
        $registry = new ModuleQueryRegistry();

        $bus = new ModuleQueryBus(
            $registry,
            static fn (string $handler): object => new $handler(),
        );

        self::assertSame(
            $registry,
            $bus->registry(),
        );
    }
}

final class RecordingModuleQueryHandler implements ModuleQueryHandlerContract
{
    /**
     * @var array<int, ModuleQueryContract>
     */
    public static array $queries = [];

    public static function reset(): void
    {
        self::$queries = [];
    }

    /**
     * @return array{
     *     id: mixed,
     *     name: string
     * }
     */
    public function handle(
        ModuleQueryContract $query,
    ): array {
        self::$queries[] = $query;

        return [
            'id' => $query->parameters()['customerId'],
            'name' => 'John Smith',
        ];
    }
}

final class AvailableStockQueryHandler implements ModuleQueryHandlerContract
{
    /**
     * @return array{
     *     productId: mixed,
     *     available: int
     * }
     */
    public function handle(
        ModuleQueryContract $query,
    ): array {
        return [
            'productId' => $query->parameters()['productId'],
            'available' => 42,
        ];
    }
}

/**
 * This class provides a valid registry class name while the test resolver
 * deliberately returns an object that violates the handler contract.
 */
final class InvalidModuleQueryHandler implements ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed {
        return null;
    }
}

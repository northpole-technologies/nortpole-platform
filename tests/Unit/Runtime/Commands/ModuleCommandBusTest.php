<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Commands;

use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use Northpole\Runtime\Commands\Contracts\ModuleCommandContract;
use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;
use Northpole\Runtime\Commands\ModuleCommand;
use Northpole\Runtime\Commands\ModuleCommandBus;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleCommandBusTest extends TestCase
{
    public function test_it_executes_a_registered_command_handler(): void
    {
        RecordingModuleCommandHandler::reset();

        $registry = new ModuleCommandRegistry;

        $registry->register(
            'crm.customer.create',
            RecordingModuleCommandHandler::class,
            'crm',
        );

        $bus = new ModuleCommandBus(
            $registry,
            static fn (string $handler): object => new $handler,
        );

        $command = new ModuleCommand(
            name: 'crm.customer.create',
            sourceModule: 'sales',
            payload: [
                'name' => 'John Smith',
            ],
        );

        $result = $bus->execute($command);

        self::assertSame(
            501,
            $result,
        );

        self::assertCount(
            1,
            RecordingModuleCommandHandler::$commands,
        );

        self::assertSame(
            $command,
            RecordingModuleCommandHandler::$commands[0],
        );

        self::assertSame(
            'John Smith',
            RecordingModuleCommandHandler::$commands[0]
                ->payload()['name'],
        );
    }

    public function test_it_returns_the_handler_result(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            'inventory.stock.reserve',
            ReserveStockCommandHandler::class,
            'inventory',
        );

        $bus = new ModuleCommandBus(
            $registry,
            static fn (string $handler): object => new $handler,
        );

        $result = $bus->execute(
            new ModuleCommand(
                name: 'inventory.stock.reserve',
                sourceModule: 'orders',
                payload: [
                    'productId' => 88,
                    'quantity' => 2,
                ],
            ),
        );

        self::assertSame(
            [
                'reservationId' => 7001,
                'productId' => 88,
                'quantity' => 2,
            ],
            $result,
        );
    }

    public function test_it_rejects_a_command_without_a_registered_handler(): void
    {
        $bus = new ModuleCommandBus(
            new ModuleCommandRegistry,
            static fn (string $handler): object => new $handler,
        );

        $this->expectException(
            LogicException::class,
        );

        $this->expectExceptionMessage(
            'No handler is registered for module command [crm.customer.delete].',
        );

        $bus->execute(
            new ModuleCommand(
                name: 'crm.customer.delete',
                sourceModule: 'crm',
            ),
        );
    }

    public function test_it_rejects_a_resolved_handler_with_the_wrong_contract(): void
    {
        $registry = new ModuleCommandRegistry;

        $registry->register(
            'crm.customer.create',
            InvalidModuleCommandHandler::class,
            'crm',
        );

        $bus = new ModuleCommandBus(
            $registry,
            static fn (): object => new \stdClass,
        );

        $this->expectException(
            LogicException::class,
        );

        $this->expectExceptionMessage(
            sprintf(
                'Resolved module command handler [%s] must implement [%s].',
                InvalidModuleCommandHandler::class,
                ModuleCommandHandlerContract::class,
            ),
        );

        $bus->execute(
            new ModuleCommand(
                name: 'crm.customer.create',
                sourceModule: 'sales',
            ),
        );
    }

    public function test_it_preserves_command_data(): void
    {
        $issuedAt = new DateTimeImmutable(
            '2026-07-25 09:30:00',
        );

        $command = new ModuleCommand(
            name: 'crm.customer.create',
            sourceModule: 'sales',
            payload: [
                'name' => 'Mary Jones',
            ],
            metadata: [
                'tenantId' => 10,
                'requestedBy' => 25,
            ],
            issuedAt: $issuedAt,
        );

        self::assertSame(
            'crm.customer.create',
            $command->name(),
        );

        self::assertSame(
            'sales',
            $command->sourceModule(),
        );

        self::assertSame(
            'Mary Jones',
            $command->payload()['name'],
        );

        self::assertSame(
            10,
            $command->metadata()['tenantId'],
        );

        self::assertSame(
            25,
            $command->metadata()['requestedBy'],
        );

        self::assertSame(
            $issuedAt,
            $command->issuedAt(),
        );
    }

    public function test_it_assigns_one_immutable_issue_time_automatically(): void
    {
        $command = new ModuleCommand(
            name: 'crm.customer.create',
            sourceModule: 'sales',
        );

        $firstRead = $command->issuedAt();

        usleep(1000);

        $secondRead = $command->issuedAt();

        self::assertSame(
            $firstRead,
            $secondRead,
        );
    }

    public function test_it_rejects_an_empty_command_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module command name cannot be empty.',
        );

        new ModuleCommand(
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
            'A module command source module cannot be empty.',
        );

        new ModuleCommand(
            name: 'crm.customer.create',
            sourceModule: '   ',
        );
    }

    public function test_it_exposes_the_command_registry(): void
    {
        $registry = new ModuleCommandRegistry;

        $bus = new ModuleCommandBus(
            $registry,
            static fn (string $handler): object => new $handler,
        );

        self::assertSame(
            $registry,
            $bus->registry(),
        );
    }
}

final class RecordingModuleCommandHandler implements ModuleCommandHandlerContract
{
    /**
     * @var array<int, ModuleCommandContract>
     */
    public static array $commands = [];

    public static function reset(): void
    {
        self::$commands = [];
    }

    public function handle(
        ModuleCommandContract $command,
    ): mixed {
        self::$commands[] = $command;

        return 501;
    }
}

final class ReserveStockCommandHandler implements ModuleCommandHandlerContract
{
    /**
     * @return array{
     *     reservationId: int,
     *     productId: mixed,
     *     quantity: mixed
     * }
     */
    public function handle(
        ModuleCommandContract $command,
    ): array {
        return [
            'reservationId' => 7001,
            'productId' => $command->payload()['productId'],
            'quantity' => $command->payload()['quantity'],
        ];
    }
}

/**
 * This class provides a valid registry class name while the test resolver
 * deliberately returns an object that violates the handler contract.
 */
final class InvalidModuleCommandHandler implements ModuleCommandHandlerContract
{
    public function handle(
        ModuleCommandContract $command,
    ): mixed {
        return null;
    }
}

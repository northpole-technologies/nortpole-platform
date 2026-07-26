<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Agents;

use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use Northpole\Runtime\Agents\Contracts\ModuleAgentContract;
use Northpole\Runtime\Agents\Contracts\ModuleAgentHandlerContract;
use Northpole\Runtime\Agents\ModuleAgent;
use Northpole\Runtime\Agents\ModuleAgentBus;
use Northpole\Runtime\Agents\ModuleAgentRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleAgentBusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RecordingAgentHandler::reset();
    }

    public function test_it_executes_a_registered_agent_handler(): void
    {
        $registry = new ModuleAgentRegistry;

        $registry->register(
            agentName: 'crm.customer.lookup',
            handler: RecordingAgentHandler::class,
            module: 'crm',
        );

        $bus = new ModuleAgentBus(
            registry: $registry,
            handlerResolver: static fn (
                string $handler
            ): object => new $handler,
        );

        $requestedAt = new DateTimeImmutable(
            '2026-07-26 09:00:00'
        );

        $agent = new ModuleAgent(
            name: ' crm.customer.lookup ',
            sourceModule: ' platform ',
            input: [
                'customerId' => 42,
            ],
            context: [
                'organisationId' => 7,
            ],
            requestedAt: $requestedAt,
        );

        $result = $bus->execute($agent);

        self::assertSame(
            [
                'customerId' => 42,
                'summary' => 'Customer summary',
            ],
            $result
        );

        self::assertSame(
            [$agent],
            RecordingAgentHandler::$agents
        );

        self::assertSame(
            'crm.customer.lookup',
            $agent->name()
        );

        self::assertSame(
            'platform',
            $agent->sourceModule()
        );

        self::assertSame(
            [
                'customerId' => 42,
            ],
            $agent->input()
        );

        self::assertSame(
            [
                'organisationId' => 7,
            ],
            $agent->context()
        );

        self::assertSame(
            $requestedAt,
            $agent->requestedAt()
        );
    }

    public function test_it_rejects_unregistered_agents(): void
    {
        $bus = new ModuleAgentBus(
            registry: new ModuleAgentRegistry,
            handlerResolver: static fn (
                string $handler
            ): object => new $handler,
        );

        $this->expectException(
            LogicException::class
        );

        $this->expectExceptionMessage(
            'No handler is registered for module agent [crm.customer.lookup].'
        );

        $bus->execute(
            new ModuleAgent(
                name: 'crm.customer.lookup',
                sourceModule: 'platform',
            )
        );
    }

    public function test_it_rejects_invalid_resolved_handlers(): void
    {
        $registry = new ModuleAgentRegistry;

        $registry->register(
            agentName: 'crm.customer.lookup',
            handler: RecordingAgentHandler::class,
            module: 'crm',
        );

        $bus = new ModuleAgentBus(
            registry: $registry,
            handlerResolver: static fn (
                string $handler
            ): object => new \stdClass,
        );

        $this->expectException(
            LogicException::class
        );

        $this->expectExceptionMessage(
            sprintf(
                'Resolved module agent handler [%s] must implement [%s].',
                RecordingAgentHandler::class,
                ModuleAgentHandlerContract::class,
            )
        );

        $bus->execute(
            new ModuleAgent(
                name: 'crm.customer.lookup',
                sourceModule: 'platform',
            )
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

        new ModuleAgent(
            name: '   ',
            sourceModule: 'platform',
        );
    }

    public function test_it_rejects_empty_source_modules(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A module agent source module cannot be empty.'
        );

        new ModuleAgent(
            name: 'crm.customer.lookup',
            sourceModule: '   ',
        );
    }

    public function test_it_exposes_the_agent_registry(): void
    {
        $registry = new ModuleAgentRegistry;

        $bus = new ModuleAgentBus(
            registry: $registry,
            handlerResolver: static fn (
                string $handler
            ): object => new $handler,
        );

        self::assertSame(
            $registry,
            $bus->registry()
        );
    }
}

final class RecordingAgentHandler implements ModuleAgentHandlerContract
{
    /**
     * @var array<int, ModuleAgentContract>
     */
    public static array $agents = [];

    public static function reset(): void
    {
        self::$agents = [];
    }

    /**
     * @return array{
     *     customerId: mixed,
     *     summary: string
     * }
     */
    public function handle(
        ModuleAgentContract $agent,
    ): array {
        self::$agents[] = $agent;

        return [
            'customerId' =>
                $agent->input()['customerId'],
            'summary' => 'Customer summary',
        ];
    }
}
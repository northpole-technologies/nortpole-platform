<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Validation\Rules;

use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Queries\Contracts\ModuleQueryContract;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Validation\Rules\QueryHandlerValidationRule;
use Northpole\Runtime\Validation\ValidationIssue;
use PHPUnit\Framework\TestCase;

final class QueryHandlerValidationRuleTest extends TestCase
{
    public function test_valid_query_handlers_pass(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            queryName: 'crm.customer.find',
            handler: ValidQueryHandler::class,
            module: 'crm',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    queries: [
                        'crm.customer.find' =>
                            ValidQueryHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertTrue($result->passes());
        self::assertTrue($result->isEmpty());
    }

    public function test_it_reports_a_missing_handler_class(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            queryName: 'crm.customer.find',
            handler: 'Modules\CRM\MissingQueryHandler',
            module: 'crm',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    queries: [
                        'crm.customer.find' =>
                            'Modules\CRM\MissingQueryHandler',
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            ['query.handler_class_missing'],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_an_invalid_handler_contract(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            queryName: 'crm.customer.find',
            handler: InvalidQueryHandler::class,
            module: 'crm',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    queries: [
                        'crm.customer.find' =>
                            InvalidQueryHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            ['query.handler_contract_invalid'],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_an_unregistered_handler(): void
    {
        $result = $this->rule(
            registry: new ModuleQueryRegistry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    queries: [
                        'crm.customer.find' =>
                            ValidQueryHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            ['query.handler_unregistered'],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_handler_and_owner_mismatches(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            queryName: 'crm.customer.find',
            handler: AlternativeQueryHandler::class,
            module: 'inventory',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [
                $this->manifest(
                    slug: 'crm',
                    queries: [
                        'crm.customer.find' =>
                            ValidQueryHandler::class,
                    ],
                ),
            ],
        )->validate();

        self::assertSame(
            [
                'query.handler_mismatch',
                'query.owner_mismatch',
            ],
            $this->codes($result->all()),
        );
    }

    public function test_it_reports_unexpected_registry_entries(): void
    {
        $registry = new ModuleQueryRegistry;

        $registry->register(
            queryName: 'platform.runtime.summary',
            handler: ValidQueryHandler::class,
            module: 'platform',
        );

        $result = $this->rule(
            registry: $registry,
            modules: [],
        )->validate();

        self::assertTrue($result->passes());
        self::assertSame(1, $result->warningCount());

        self::assertSame(
            ['query.handler_unexpected'],
            $this->codes($result->all()),
        );
    }

    /**
     * @param  array<int, ModuleManifest>  $modules
     */
    private function rule(
        ModuleQueryRegistry $registry,
        array $modules,
    ): QueryHandlerValidationRule {
        return new QueryHandlerValidationRule(
            modulesResolver: static fn (): array =>
                $modules,
            registry: $registry,
        );
    }

    /**
     * @param  array<string, string>  $queries
     */
    private function manifest(
        string $slug,
        array $queries,
    ): ModuleManifest {
        return new ModuleManifest(
            data: [
                'name' => ucfirst($slug),
                'slug' => $slug,
                'version' => '1.0.0',
                'enabled' => true,
                'queries' => [
                    'handles' => $queries,
                ],
            ],
            path: '/modules/'.$slug,
            manifestPath: '/modules/'.$slug.'/module.json',
        );
    }

    /**
     * @param  array<int, ValidationIssue>  $issues
     * @return array<int, string>
     */
    private function codes(array $issues): array
    {
        return array_map(
            static fn (
                ValidationIssue $issue,
            ): string => $issue->code(),
            $issues,
        );
    }
}

final class ValidQueryHandler implements
    ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed {
        return null;
    }
}

final class AlternativeQueryHandler implements
    ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed {
        return null;
    }
}

final class InvalidQueryHandler
{
}
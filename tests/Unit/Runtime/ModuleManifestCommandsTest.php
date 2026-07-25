<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleManifestCommandsTest extends TestCase
{
    public function test_it_returns_handled_commands(): void
    {
        $manifest = $this->manifest([
            'commands' => [
                'handles' => [
                    'crm.customer.create' => 'Modules\\CRM\\Commands\\CreateCustomerHandler',
                    'crm.customer.update' => 'Modules\\CRM\\Commands\\UpdateCustomerHandler',
                ],
            ],
        ]);

        self::assertSame(
            [
                'crm.customer.create' => 'Modules\\CRM\\Commands\\CreateCustomerHandler',
                'crm.customer.update' => 'Modules\\CRM\\Commands\\UpdateCustomerHandler',
            ],
            $manifest->handledCommands(),
        );
    }

    public function test_it_trims_command_names_and_handler_classes(): void
    {
        $manifest = $this->manifest([
            'commands' => [
                'handles' => [
                    ' crm.customer.create ' => ' Modules\\CRM\\Commands\\CreateCustomerHandler ',
                ],
            ],
        ]);

        self::assertSame(
            [
                'crm.customer.create' => 'Modules\\CRM\\Commands\\CreateCustomerHandler',
            ],
            $manifest->handledCommands(),
        );
    }

    public function test_it_returns_empty_handled_commands_when_not_defined(): void
    {
        $manifest = $this->manifest();

        self::assertSame(
            [],
            $manifest->handledCommands(),
        );
    }

    public function test_it_returns_empty_handled_commands_when_commands_are_empty(): void
    {
        $manifest = $this->manifest([
            'commands' => [],
        ]);

        self::assertSame(
            [],
            $manifest->handledCommands(),
        );
    }

    public function test_it_rejects_a_non_array_commands_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest field [commands] must be an object',
        );

        $this->manifest([
            'commands' => 'crm.customer.create',
        ]);
    }

    public function test_it_rejects_a_non_array_handles_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest field [commands.handles] must be an object',
        );

        $this->manifest([
            'commands' => [
                'handles' => 'Modules\\CRM\\Commands\\CreateCustomerHandler',
            ],
        ]);
    }

    public function test_it_rejects_an_empty_handled_command_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest handled command names must be non-empty strings',
        );

        $this->manifest([
            'commands' => [
                'handles' => [
                    '' => 'Modules\\CRM\\Commands\\CreateCustomerHandler',
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_command_handler_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest command [crm.customer.create] must have a non-empty handler class',
        );

        $this->manifest([
            'commands' => [
                'handles' => [
                    'crm.customer.create' => '',
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_command_handler_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Module manifest command [crm.customer.create] must have a non-empty handler class',
        );

        $this->manifest([
            'commands' => [
                'handles' => [
                    'crm.customer.create' => 123,
                ],
            ],
        ]);
    }

    public function test_it_preserves_command_data_in_the_original_manifest(): void
    {
        $data = [
            'name' => 'CRM',
            'slug' => 'crm',
            'version' => '1.0.0',
            'enabled' => true,
            'commands' => [
                'handles' => [
                    'crm.customer.create' => 'Modules\\CRM\\Commands\\CreateCustomerHandler',
                ],
            ],
        ];

        $manifest = new ModuleManifest(
            data: $data,
            path: '/modules/crm',
            manifestPath: '/modules/crm/module.json',
        );

        self::assertSame(
            $data,
            $manifest->toArray(),
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function manifest(
        array $overrides = [],
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_replace(
                [
                    'name' => 'CRM',
                    'slug' => 'crm',
                    'version' => '1.0.0',
                    'enabled' => true,
                ],
                $overrides,
            ),
            path: '/modules/crm',
            manifestPath: '/modules/crm/module.json',
        );
    }
}

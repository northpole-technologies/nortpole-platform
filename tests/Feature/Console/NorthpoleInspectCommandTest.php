<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class NorthpoleInspectCommandTest extends TestCase
{
    public function test_it_inspects_a_discovered_module(): void
    {
        $exitCode = Artisan::call(
            'northpole:inspect',
            [
                'module' => 'crm',
            ],
        );

        $output = Artisan::output();

        $this->assertSame(
            0,
            $exitCode,
        );

        $this->assertStringContainsString(
            'NorthPole Module Inspector',
            $output,
        );

        $this->assertStringContainsString(
            'CRM',
            $output,
        );

        $this->assertStringContainsString(
            'Commands',
            $output,
        );

        $this->assertStringContainsString(
            'Queries',
            $output,
        );

        $this->assertStringContainsString(
            'Published Events',
            $output,
        );

        $this->assertStringContainsString(
            'Permissions',
            $output,
        );

        $this->assertStringContainsString(
            'Navigation',
            $output,
        );

        $this->assertStringContainsString(
            'Inspection complete.',
            $output,
        );
    }

    public function test_it_outputs_module_information_as_json(): void
    {
        $exitCode = Artisan::call(
            'northpole:inspect',
            [
                'module' => 'crm',
                '--json' => true,
            ],
        );

        $output = trim(
            Artisan::output(),
        );

        $data = json_decode(
            $output,
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertSame(
            0,
            $exitCode,
        );

        $this->assertSame(
            'CRM',
            $data['module']['name'],
        );

        $this->assertSame(
            'crm',
            $data['module']['slug'],
        );

        $this->assertSame(
            '1.0.0',
            $data['module']['version'],
        );

        $this->assertTrue(
            $data['module']['enabled'],
        );

        $this->assertSame(
            'Modules\CRM\Commands\CreateCustomerHandler',
            $data['commands']['crm.customer.create'],
        );

        $this->assertSame(
            'Modules\CRM\Queries\FindCustomerHandler',
            $data['queries']['crm.customer.find'],
        );

        $this->assertContains(
            'crm.customer.created',
            $data['published_events'],
        );

        $this->assertContains(
            'crm.customers.view',
            $data['permissions'],
        );

        $this->assertSame(
            'crm.customers.index',
            $data['navigation'][0]['route'],
        );
    }

    public function test_it_fails_when_the_module_does_not_exist(): void
    {
        $exitCode = Artisan::call(
            'northpole:inspect',
            [
                'module' => 'missing-module',
            ],
        );

        $output = Artisan::output();

        $this->assertSame(
            1,
            $exitCode,
        );

        $this->assertStringContainsString(
            'NorthPole module [missing-module] was not found.',
            $output,
        );
    }

    public function test_module_slug_is_case_insensitive(): void
    {
        $exitCode = Artisan::call(
            'northpole:inspect',
            [
                'module' => 'CRM',
            ],
        );

        $output = Artisan::output();

        $this->assertSame(
            0,
            $exitCode,
        );

        $this->assertStringContainsString(
            'NorthPole Module Inspector',
            $output,
        );

        $this->assertStringContainsString(
            'Inspection complete.',
            $output,
        );
    }
}
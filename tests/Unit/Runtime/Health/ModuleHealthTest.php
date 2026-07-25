<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Health;

use Northpole\Runtime\Health\ModuleHealth;
use PHPUnit\Framework\TestCase;

final class ModuleHealthTest extends TestCase
{
    public function test_module_health_can_be_serialised(): void
    {
        $health = new ModuleHealth(
            slug: 'crm',
            name: 'CRM',
            status: 'healthy',
            score: 100,
            checks: [
                [
                    'key' => 'manifest',
                    'label' => 'Manifest valid',
                    'healthy' => true,
                    'message' => null,
                ],
            ],
        );

        $this->assertSame(
            [
                'slug' => 'crm',
                'name' => 'CRM',
                'status' => 'healthy',
                'score' => 100,
                'checks' => [
                    [
                        'key' => 'manifest',
                        'label' => 'Manifest valid',
                        'healthy' => true,
                        'message' => null,
                    ],
                ],
            ],
            $health->toArray(),
        );
    }
}
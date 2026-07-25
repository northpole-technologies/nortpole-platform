<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Health;

use Northpole\Runtime\Health\ModuleHealth;
use Northpole\Runtime\Health\RuntimeHealth;
use PHPUnit\Framework\TestCase;

final class RuntimeHealthTest extends TestCase
{
    public function test_runtime_health_can_be_serialised(): void
    {
        $module = new ModuleHealth(
            slug: 'crm',
            name: 'CRM',
            status: 'healthy',
            score: 100,
            checks: [],
        );

        $health = new RuntimeHealth(
            status: 'healthy',
            score: 100,
            modules: [$module],
        );

        $this->assertSame(
            [
                'status' => 'healthy',
                'score' => 100,
                'modules' => [
                    [
                        'slug' => 'crm',
                        'name' => 'CRM',
                        'status' => 'healthy',
                        'score' => 100,
                        'checks' => [],
                    ],
                ],
            ],
            $health->toArray(),
        );
    }
}
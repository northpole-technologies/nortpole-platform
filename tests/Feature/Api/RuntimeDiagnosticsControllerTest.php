<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

final class RuntimeDiagnosticsControllerTest extends TestCase
{
    public function test_runtime_diagnostics_can_be_retrieved(): void
    {
        $this->getJson('/api/runtime/diagnostics')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'health' => [
                        'status',
                        'score',
                        'modules',
                    ],
                    'validation' => [
                        'status',
                        'rules',
                        'issues',
                        'errors',
                        'warnings',
                        'information',
                        'results',
                    ],
                    'repairs' => [
                        'status',
                        'providers',
                        'recommendations',
                        'results',
                    ],
                ],
            ]);
    }

    public function test_runtime_diagnostics_returns_health_data(): void
    {
        $response = $this->getJson(
            '/api/runtime/diagnostics',
        );

        $response->assertOk();

        $this->assertContains(
            $response->json('data.health.status'),
            [
                'healthy',
                'degraded',
                'unhealthy',
            ],
        );

        $this->assertIsInt(
            $response->json('data.health.score'),
        );

        $this->assertIsArray(
            $response->json('data.health.modules'),
        );
    }

    public function test_runtime_diagnostics_returns_validation_data(): void
    {
        $response = $this->getJson(
            '/api/runtime/diagnostics',
        );

        $response->assertOk();

        $this->assertContains(
            $response->json('data.validation.status'),
            [
                'passed',
                'failed',
            ],
        );

        $this->assertIsInt(
            $response->json('data.validation.rules'),
        );

        $this->assertIsArray(
            $response->json('data.validation.results'),
        );

        $this->assertSame(
            count(
                $response->json(
                    'data.validation.results',
                ),
            ),
            $response->json(
                'data.validation.issues',
            ),
        );
    }

    public function test_runtime_diagnostics_returns_repair_data(): void
    {
        $response = $this->getJson(
            '/api/runtime/diagnostics',
        );

        $response->assertOk();

        $this->assertContains(
            $response->json('data.repairs.status'),
            [
                'clear',
                'recommended',
            ],
        );

        $this->assertSame(
            2,
            $response->json(
                'data.repairs.providers',
            ),
        );

        $this->assertIsArray(
            $response->json('data.repairs.results'),
        );

        $this->assertSame(
            count(
                $response->json(
                    'data.repairs.results',
                ),
            ),
            $response->json(
                'data.repairs.recommendations',
            ),
        );
    }
}
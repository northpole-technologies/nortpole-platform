<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;







use Tests\Support\CreatesRuntime;
use Tests\TestCase;

final class RuntimeTest extends TestCase
{
    use CreatesRuntime;
    public function test_runtime_discovers_and_exposes_modules(): void
    {
        $runtime = $this->createRuntime();

        $runtime->discover();

        $this->assertGreaterThanOrEqual(
            1,
            $runtime->count(),
        );

        $this->assertTrue(
            $runtime->has('santa-buddy'),
        );

        $module = $runtime->module('santa-buddy');

        $this->assertNotNull($module);

        $this->assertSame(
            'santa-buddy',
            $module->slug(),
        );

        $this->assertArrayHasKey(
            'santa-buddy',
            $runtime->modules(),
        );

        $this->assertArrayHasKey(
            'santa-buddy',
            $runtime->enabledModules(),
        );

        $this->assertSame(
            base_path('modules'),
            $runtime->modulesPath(),
        );
    }
}

<?php

namespace Tests\Unit\Runtime;

use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

class RuntimeTest extends TestCase
{
    public function test_runtime_discovers_and_exposes_modules(): void
    {
        $repository = new ModuleRepository();

        $runtime = new Runtime(
            new ModuleDiscovery(
                new ModuleFinder(),
                new ManifestLoader(),
                $repository
            ),
            $repository,
            base_path('modules')
        );

        $runtime->discover();

        $this->assertGreaterThanOrEqual(1, $runtime->count());
        $this->assertTrue($runtime->has('santa-buddy'));

        $module = $runtime->module('santa-buddy');

        $this->assertNotNull($module);
        $this->assertSame('santa-buddy', $module->slug());
        $this->assertArrayHasKey(
            'santa-buddy',
            $runtime->modules()
        );
        $this->assertArrayHasKey(
            'santa-buddy',
            $runtime->enabledModules()
        );
        $this->assertSame(
            base_path('modules'),
            $runtime->modulesPath()
        );
    }
}
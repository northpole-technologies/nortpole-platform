<?php

declare(strict_types=1);

namespace Tests\Feature\Lifecycle;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\Exceptions\ModuleManifestNotFoundException;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Lifecycle\Stages\ResolveManifestStage;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class ResolveManifestStageTest extends TestCase
{
    public function test_it_resolves_the_runtime_manifest_for_the_marketplace_module(): void
    {
        $context = $this->context(
            'santa-buddy'
        );

        $stage = new ResolveManifestStage(
            app(Runtime::class)
        );

        $stage->handle($context);

        $this->assertTrue(
            $context->hasManifest()
        );

        $this->assertNotNull(
            $context->manifest()
        );

        $this->assertSame(
            'santa-buddy',
            $context->manifest()?->slug()
        );

        $this->assertSame(
            'SantaBuddy',
            $context->manifest()?->name()
        );
    }

    public function test_it_throws_when_the_runtime_manifest_cannot_be_found(): void
    {
        $context = $this->context(
            'missing-module'
        );

        $stage = new ResolveManifestStage(
            app(Runtime::class)
        );

        $this->expectException(
            ModuleManifestNotFoundException::class
        );

        $this->expectExceptionMessage(
            'The runtime manifest for module [missing-module] could not be found.'
        );

        $stage->handle($context);
    }

    public function test_it_supports_every_lifecycle_operation(): void
    {
        $stage = new ResolveManifestStage(
            app(Runtime::class)
        );

        foreach (LifecycleOperation::cases() as $operation) {
            $context = $this->context(
                'santa-buddy',
                $operation
            );

            $this->assertTrue(
                $stage->supports($context)
            );
        }
    }

    public function test_it_runs_after_installation_resolution(): void
    {
        $stage = new ResolveManifestStage(
            app(Runtime::class)
        );

        $this->assertSame(
            'resolve_manifest',
            $stage->name()
        );

        $this->assertSame(
            150,
            $stage->priority()
        );
    }

    private function context(
        string $moduleKey,
        LifecycleOperation $operation = LifecycleOperation::Install
    ): LifecycleContext {
        $module = new MarketplaceModule([
            'key' => $moduleKey,
            'name' => 'Test Module',
            'version' => '1.0.0',
        ]);

        $organisation = new Organisation([
            'name' => 'NorthPole Technologies',
            'slug' => 'northpole-technologies',
        ]);

        return new LifecycleContext(
            $operation,
            $module,
            $organisation
        );
    }
}
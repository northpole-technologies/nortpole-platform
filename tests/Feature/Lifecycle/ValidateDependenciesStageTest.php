<?php

declare(strict_types=1);

namespace Tests\Feature\Lifecycle;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use App\Models\OrganisationModule;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\Exceptions\ModuleDependencyValidationException;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Lifecycle\Stages\ValidateDependenciesStage;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class ValidateDependenciesStageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private array $temporaryModulePaths = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryModulePaths as $path) {
            File::deleteDirectory($path);
        }

        parent::tearDown();
    }

    public function test_it_runs_before_the_mutation_stages(): void
    {
        $stage = $this->stage();

        $this->assertSame(
            'validate-dependencies',
            $stage->name()
        );

        $this->assertSame(
            175,
            $stage->priority()
        );
    }

    public function test_it_supports_dependency_sensitive_operations(): void
    {
        $stage = $this->stage();
        $organisation = $this->createOrganisation();
        $module = $this->createMarketplaceModule(
            'target-module'
        );

        foreach (
            [
                LifecycleOperation::Install,
                LifecycleOperation::Enable,
                LifecycleOperation::Disable,
                LifecycleOperation::Uninstall,
            ] as $operation
        ) {
            $context = $this->context(
                $operation,
                $module,
                $organisation
            );

            $this->assertTrue(
                $stage->supports($context)
            );
        }

        $upgradeContext = $this->context(
            LifecycleOperation::Upgrade,
            $module,
            $organisation
        );

        $this->assertFalse(
            $stage->supports($upgradeContext)
        );
    }

    public function test_installation_requires_dependencies_to_be_installed(): void
    {
        $organisation = $this->createOrganisation();

        $target = $this->createMarketplaceModule(
            'target-module'
        );

        $this->createMarketplaceModule(
            'required-module'
        );

        $context = $this->context(
            LifecycleOperation::Install,
            $target,
            $organisation,
            ['required-module']
        );

        $this->expectException(
            ModuleDependencyValidationException::class
        );

        $this->expectExceptionMessage(
            'Module [target-module] requires dependency [required-module] to be installed.'
        );

        $this->stage()->handle($context);
    }

    public function test_enabling_requires_dependencies_to_be_enabled(): void
    {
        $organisation = $this->createOrganisation();

        $target = $this->createMarketplaceModule(
            'target-module'
        );

        $dependency = $this->createMarketplaceModule(
            'required-module'
        );

        $this->createInstallation(
            $organisation,
            $dependency,
            false
        );

        $context = $this->context(
            LifecycleOperation::Enable,
            $target,
            $organisation,
            ['required-module']
        );

        $this->expectException(
            ModuleDependencyValidationException::class
        );

        $this->expectExceptionMessage(
            'Module [target-module] requires dependency [required-module] to be enabled.'
        );

        $this->stage()->handle($context);
    }

    public function test_installation_passes_when_dependencies_are_installed_and_enabled(): void
    {
        $organisation = $this->createOrganisation();

        $target = $this->createMarketplaceModule(
            'target-module'
        );

        $dependency = $this->createMarketplaceModule(
            'required-module'
        );

        $this->createInstallation(
            $organisation,
            $dependency,
            true
        );

        $context = $this->context(
            LifecycleOperation::Install,
            $target,
            $organisation,
            ['required-module']
        );

        $this->stage()->handle($context);

        $this->addToAssertionCount(1);
    }

    public function test_disabling_is_blocked_by_an_enabled_dependant(): void
    {
        $organisation = $this->createOrganisation();

        $target = $this->createMarketplaceModule(
            'target-module'
        );

        $dependant = $this->createMarketplaceModule(
            'dependant-module'
        );

        $this->createInstallation(
            $organisation,
            $target,
            true
        );

        $this->createInstallation(
            $organisation,
            $dependant,
            true
        );

        $this->registerRuntimeModule(
            'dependant-module',
            ['target-module']
        );

        $context = $this->context(
            LifecycleOperation::Disable,
            $target,
            $organisation
        );

        $this->expectException(
            ModuleDependencyValidationException::class
        );

        $this->expectExceptionMessage(
            'Module [target-module] cannot be disabled because it is required by module [dependant-module].'
        );

        $this->stage()->handle($context);
    }

    public function test_disabling_is_not_blocked_by_a_disabled_dependant(): void
    {
        $organisation = $this->createOrganisation();

        $target = $this->createMarketplaceModule(
            'target-module'
        );

        $dependant = $this->createMarketplaceModule(
            'dependant-module'
        );

        $this->createInstallation(
            $organisation,
            $target,
            true
        );

        $this->createInstallation(
            $organisation,
            $dependant,
            false
        );

        $this->registerRuntimeModule(
            'dependant-module',
            ['target-module']
        );

        $context = $this->context(
            LifecycleOperation::Disable,
            $target,
            $organisation
        );

        $this->stage()->handle($context);

        $this->addToAssertionCount(1);
    }

    public function test_uninstalling_is_blocked_by_a_disabled_installed_dependant(): void
    {
        $organisation = $this->createOrganisation();

        $target = $this->createMarketplaceModule(
            'target-module'
        );

        $dependant = $this->createMarketplaceModule(
            'dependant-module'
        );

        $this->createInstallation(
            $organisation,
            $target,
            true
        );

        $this->createInstallation(
            $organisation,
            $dependant,
            false
        );

        $this->registerRuntimeModule(
            'dependant-module',
            ['target-module']
        );

        $context = $this->context(
            LifecycleOperation::Uninstall,
            $target,
            $organisation
        );

        $this->expectException(
            ModuleDependencyValidationException::class
        );

        $this->expectExceptionMessage(
            'Module [target-module] cannot be uninstalled because it is required by module [dependant-module].'
        );

        $this->stage()->handle($context);
    }

    public function test_dependencies_are_isolated_between_organisations(): void
    {
        $organisation = $this->createOrganisation(
            'NorthPole Technologies',
            'northpole-technologies'
        );

        $otherOrganisation = $this->createOrganisation(
            'Other Organisation',
            'other-organisation'
        );

        $target = $this->createMarketplaceModule(
            'target-module'
        );

        $dependant = $this->createMarketplaceModule(
            'dependant-module'
        );

        $this->createInstallation(
            $organisation,
            $target,
            true
        );

        $this->createInstallation(
            $otherOrganisation,
            $dependant,
            true
        );

        $this->registerRuntimeModule(
            'dependant-module',
            ['target-module']
        );

        $context = $this->context(
            LifecycleOperation::Disable,
            $target,
            $organisation
        );

        $this->stage()->handle($context);

        $this->addToAssertionCount(1);
    }

    private function stage(): ValidateDependenciesStage
    {
        return new ValidateDependenciesStage(
            app(Runtime::class),
            app(TenantContext::class),
        );
    }

    private function context(
        LifecycleOperation $operation,
        MarketplaceModule $module,
        Organisation $organisation,
        array $dependencies = []
    ): LifecycleContext {
        $context = new LifecycleContext(
            $operation,
            $module,
            $organisation
        );

        $context->setManifest(
            new ModuleManifest(
                [
                    'name' => $module->name,
                    'slug' => $module->key,
                    'version' => $module->version,
                    'enabled' => true,
                    'dependencies' => $dependencies,
                ],
                base_path(
                    'modules/TestLifecycleModule'
                ),
                base_path(
                    'modules/TestLifecycleModule/module.json'
                ),
            )
        );

        return $context;
    }

    private function createOrganisation(
        string $name = 'NorthPole Technologies',
        string $slug = 'northpole-technologies'
    ): Organisation {
        return Organisation::create([
            'name' => $name,
            'slug' => $slug,
            'email' => "{$slug}@example.com",
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ]);
    }

    private function createMarketplaceModule(
        string $key
    ): MarketplaceModule {
        return MarketplaceModule::create([
            'key' => $key,
            'name' => str($key)
                ->replace('-', ' ')
                ->title()
                ->toString(),
            'description' => "{$key} module.",
            'version' => '1.0.0',
            'category' => 'Testing',
            'icon' => 'box',
            'is_core' => false,
            'is_active' => true,
        ]);
    }

    private function createInstallation(
        Organisation $organisation,
        MarketplaceModule $module,
        bool $enabled
    ): OrganisationModule {
        return app(TenantContext::class)->withoutTenancy(
            fn (): OrganisationModule => OrganisationModule::create([
                'organisation_id' => $organisation->getKey(),
                'marketplace_module_id' => $module->getKey(),
                'is_enabled' => $enabled,
                'installed_at' => now(),
            ])
        );
    }

    private function registerRuntimeModule(
        string $slug,
        array $dependencies
    ): void {
        $directoryName = str($slug)
            ->studly()
            ->toString();

        $modulePath = base_path(
            "modules/{$directoryName}"
        );

        File::ensureDirectoryExists(
            $modulePath
        );

        File::put(
            "{$modulePath}/module.json",
            json_encode(
                [
                    'name' => str($slug)
                        ->replace('-', ' ')
                        ->title()
                        ->toString(),
                    'slug' => $slug,
                    'version' => '1.0.0',
                    'description' => 'Lifecycle dependency test module.',
                    'enabled' => true,
                    'dependencies' => $dependencies,
                ],
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            )
        );

        $this->temporaryModulePaths[] = $modulePath;

        app(Runtime::class)->discover();
    }
}

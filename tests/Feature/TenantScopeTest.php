<?php

namespace Tests\Feature;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use App\Models\OrganisationModule;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    private TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantContext = app(TenantContext::class);
    }

    protected function tearDown(): void
    {
        $this->tenantContext->clear();

        parent::tearDown();
    }

    public function test_queries_only_return_records_for_the_active_tenant(): void
    {
        $firstOrganisation = $this->createOrganisation(
            'First Organisation',
            'first-organisation'
        );

        $secondOrganisation = $this->createOrganisation(
            'Second Organisation',
            'second-organisation'
        );

        $firstModule = $this->createModule(
            'first-module',
            'First Module'
        );

        $secondModule = $this->createModule(
            'second-module',
            'Second Module'
        );

        $this->createInstallation(
            $firstOrganisation,
            $firstModule
        );

        $this->createInstallation(
            $secondOrganisation,
            $secondModule
        );

        $installations = $this->tenantContext->runFor(
            $firstOrganisation,
            fn () => OrganisationModule::query()->get()
        );

        $this->assertCount(1, $installations);

        $this->assertSame(
            $firstOrganisation->id,
            $installations->first()->organisation_id
        );

        $this->assertSame(
            $firstModule->id,
            $installations->first()->marketplace_module_id
        );
    }

    public function test_the_active_tenant_is_assigned_automatically_on_create(): void
    {
        $organisation = $this->createOrganisation(
            'NorthPole Technologies',
            'northpole-technologies'
        );

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $installation = $this->tenantContext->runFor(
            $organisation,
            fn () => OrganisationModule::create([
                'marketplace_module_id' => $module->id,
                'is_enabled' => true,
                'installed_at' => now(),
            ])
        );

        $this->assertSame(
            $organisation->id,
            $installation->organisation_id
        );

        $this->assertDatabaseHas('organisation_modules', [
            'id' => $installation->id,
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
        ]);
    }

    public function test_a_supplied_organisation_id_cannot_override_the_active_tenant(): void
    {
        $activeOrganisation = $this->createOrganisation(
            'Active Organisation',
            'active-organisation'
        );

        $otherOrganisation = $this->createOrganisation(
            'Other Organisation',
            'other-organisation'
        );

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $installation = $this->tenantContext->runFor(
            $activeOrganisation,
            fn () => OrganisationModule::create([
                'organisation_id' => $otherOrganisation->id,
                'marketplace_module_id' => $module->id,
                'is_enabled' => true,
                'installed_at' => now(),
            ])
        );

        $this->assertSame(
            $activeOrganisation->id,
            $installation->organisation_id
        );

        $this->assertDatabaseMissing('organisation_modules', [
            'organisation_id' => $otherOrganisation->id,
            'marketplace_module_id' => $module->id,
        ]);
    }

    public function test_querying_without_a_tenant_fails_closed(): void
    {
        $this->expectException(LogicException::class);

        $this->expectExceptionMessage(
            'A tenant must be resolved before querying'
        );

        OrganisationModule::query()->get();
    }

    public function test_creating_without_a_tenant_or_explicit_owner_fails(): void
    {
        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $this->expectException(LogicException::class);

        $this->expectExceptionMessage(
            'A tenant must be resolved before creating'
        );

        OrganisationModule::create([
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
            'installed_at' => now(),
        ]);
    }

    public function test_system_operations_can_bypass_the_tenant_scope(): void
    {
        $firstOrganisation = $this->createOrganisation(
            'First Organisation',
            'first-organisation'
        );

        $secondOrganisation = $this->createOrganisation(
            'Second Organisation',
            'second-organisation'
        );

        $firstModule = $this->createModule(
            'first-module',
            'First Module'
        );

        $secondModule = $this->createModule(
            'second-module',
            'Second Module'
        );

        $this->createInstallation(
            $firstOrganisation,
            $firstModule
        );

        $this->createInstallation(
            $secondOrganisation,
            $secondModule
        );

        $installations = $this->tenantContext->withoutTenancy(
            fn () => OrganisationModule::query()->get()
        );

        $this->assertCount(2, $installations);
    }

    public function test_tenant_ownership_cannot_be_changed(): void
    {
        $firstOrganisation = $this->createOrganisation(
            'First Organisation',
            'first-organisation'
        );

        $secondOrganisation = $this->createOrganisation(
            'Second Organisation',
            'second-organisation'
        );

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $installation = $this->createInstallation(
            $firstOrganisation,
            $module
        );

        $this->expectException(LogicException::class);

        $this->expectExceptionMessage(
            'tenant ownership'
        );

        $this->tenantContext->runFor(
            $firstOrganisation,
            function () use (
                $installation,
                $secondOrganisation
            ): void {
                $installation->update([
                    'organisation_id' => $secondOrganisation->id,
                ]);
            }
        );
    }

    private function createOrganisation(
        string $name,
        string $slug
    ): Organisation {
        return Organisation::create([
            'name' => $name,
            'slug' => $slug,
            'active' => true,
        ]);
    }

    private function createModule(
        string $key,
        string $name
    ): MarketplaceModule {
        return MarketplaceModule::create([
            'key' => $key,
            'name' => $name,
            'description' => "{$name} module.",
            'version' => '1.0.0',
            'category' => 'Platform',
            'icon' => 'box',
            'is_core' => false,
            'is_active' => true,
        ]);
    }

    private function createInstallation(
        Organisation $organisation,
        MarketplaceModule $module
    ): OrganisationModule {
        return $this->tenantContext->withoutTenancy(
            fn () => OrganisationModule::create([
                'organisation_id' => $organisation->id,
                'marketplace_module_id' => $module->id,
                'is_enabled' => true,
                'installed_at' => now(),
            ])
        );
    }
}

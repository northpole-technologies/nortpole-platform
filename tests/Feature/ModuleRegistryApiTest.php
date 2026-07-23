<?php

namespace Tests\Feature;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use App\Models\OrganisationModule;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ModuleRegistryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_modules_can_be_listed(): void
    {
        $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $this->createModule(
            'organisation-manager',
            'Organisation Manager',
            true
        );

        $response = $this->getJson('/api/v1/modules');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'key' => 'santa-buddy',
                'name' => 'SantaBuddy',
            ])
            ->assertJsonFragment([
                'key' => 'organisation-manager',
                'name' => 'Organisation Manager',
            ]);
    }

    public function test_a_marketplace_module_can_be_viewed(): void
    {
        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $response = $this->getJson(
            "/api/v1/modules/{$module->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $module->id)
            ->assertJsonPath('data.key', 'santa-buddy')
            ->assertJsonPath('data.name', 'SantaBuddy')
            ->assertJsonPath('data.version', '1.0.0');
    }

    public function test_a_marketplace_module_can_be_installed_for_the_active_tenant(): void
    {
        [$user, $organisation] = $this->createTenant();

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->postJson(
                "/api/v1/modules/{$module->id}/install"
            );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Module installed successfully.'
            )
            ->assertJsonPath(
                'data.organisation_id',
                $organisation->id
            )
            ->assertJsonPath(
                'data.marketplace_module_id',
                $module->id
            )
            ->assertJsonPath('data.is_enabled', true);

        $this->assertDatabaseHas('organisation_modules', [
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
        ]);
    }

    public function test_an_installed_module_can_be_disabled_for_the_active_tenant(): void
    {
        [$user, $organisation] = $this->createTenant();

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $this->createInstallation(
            $organisation,
            $module,
            true
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->patchJson(
                "/api/v1/modules/{$module->id}/disable"
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Module disabled successfully.'
            )
            ->assertJsonPath('data.is_enabled', false);

        $this->assertDatabaseHas('organisation_modules', [
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => false,
        ]);
    }

    public function test_a_disabled_module_can_be_enabled_for_the_active_tenant(): void
    {
        [$user, $organisation] = $this->createTenant();

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $this->createInstallation(
            $organisation,
            $module,
            false
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->patchJson(
                "/api/v1/modules/{$module->id}/enable"
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Module enabled successfully.'
            )
            ->assertJsonPath('data.is_enabled', true);

        $this->assertDatabaseHas('organisation_modules', [
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
        ]);
    }

    public function test_an_installed_module_can_be_uninstalled_for_the_active_tenant(): void
    {
        [$user, $organisation] = $this->createTenant();

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $this->createInstallation(
            $organisation,
            $module,
            true
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->deleteJson(
                "/api/v1/modules/{$module->id}/uninstall"
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Module uninstalled successfully.'
            );

        $this->assertSoftDeleted('organisation_modules', [
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
        ]);
    }

    public function test_an_uninstalled_module_can_be_reinstalled_for_the_active_tenant(): void
    {
        [$user, $organisation] = $this->createTenant();

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $installation = $this->createInstallation(
            $organisation,
            $module,
            true
        );

        $installation->delete();

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->postJson(
                "/api/v1/modules/{$module->id}/install"
            );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Module installed successfully.'
            )
            ->assertJsonPath('data.is_enabled', true)
            ->assertJsonPath('data.deleted_at', null);

        $this->assertDatabaseHas('organisation_modules', [
            'id' => $installation->id,
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
            'deleted_at' => null,
        ]);

        $this->assertSame(
            1,
            app(TenantContext::class)->withoutTenancy(
                fn (): int => OrganisationModule::withTrashed()
                    ->where(
                        'organisation_id',
                        $organisation->id
                    )
                    ->where(
                        'marketplace_module_id',
                        $module->id
                    )
                    ->count()
            )
        );
    }

    public function test_module_installation_requires_authentication(): void
    {
        $organisation = $this->createOrganisation(
            'NorthPole Technologies',
            'northpole-technologies'
        );

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->postJson(
                "/api/v1/modules/{$module->id}/install"
            );

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('organisation_modules', [
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
        ]);
    }

    public function test_module_installation_requires_a_tenant_header(): void
    {
        $user = User::factory()->create();

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/v1/modules/{$module->id}/install"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'The X-Organisation-Id header is required.',
            ]);

        $this->assertDatabaseCount(
            'organisation_modules',
            0
        );
    }

    public function test_a_user_cannot_install_a_module_for_another_organisation(): void
    {
        [$user, $allowedOrganisation] = $this->createTenant();

        $otherOrganisation = $this->createOrganisation(
            'Other Organisation',
            'other-organisation'
        );

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $otherOrganisation->id
            )
            ->postJson(
                "/api/v1/modules/{$module->id}/install"
            );

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Organisation access was not found.',
            ]);

        $this->assertDatabaseMissing('organisation_modules', [
            'organisation_id' => $otherOrganisation->id,
            'marketplace_module_id' => $module->id,
        ]);

        $this->assertDatabaseMissing('organisation_modules', [
            'organisation_id' => $allowedOrganisation->id,
            'marketplace_module_id' => $module->id,
        ]);
    }

    public function test_request_body_cannot_override_the_active_tenant(): void
    {
        [$user, $allowedOrganisation] = $this->createTenant();

        $otherOrganisation = $this->createOrganisation(
            'Other Organisation',
            'other-organisation'
        );

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $allowedOrganisation->id
            )
            ->postJson(
                "/api/v1/modules/{$module->id}/install",
                [
                    'organisation_id' => $otherOrganisation->id,
                ]
            );

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.organisation_id',
                $allowedOrganisation->id
            );

        $this->assertDatabaseHas('organisation_modules', [
            'organisation_id' => $allowedOrganisation->id,
            'marketplace_module_id' => $module->id,
        ]);

        $this->assertDatabaseMissing('organisation_modules', [
            'organisation_id' => $otherOrganisation->id,
            'marketplace_module_id' => $module->id,
        ]);
    }

    public function test_a_tenant_cannot_modify_another_tenants_installation(): void
    {
        [$user, $allowedOrganisation] = $this->createTenant();

        $otherOrganisation = $this->createOrganisation(
            'Other Organisation',
            'other-organisation'
        );

        $module = $this->createModule(
            'santa-buddy',
            'SantaBuddy'
        );

        $otherInstallation = $this->createInstallation(
            $otherOrganisation,
            $module,
            true
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $allowedOrganisation->id
            )
            ->patchJson(
                "/api/v1/modules/{$module->id}/disable"
            );

        $response->assertNotFound();

        $this->assertDatabaseHas('organisation_modules', [
            'id' => $otherInstallation->id,
            'organisation_id' => $otherOrganisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
        ]);
    }

    private function createTenant(): array
    {
        $user = User::factory()->create();

        $organisation = $this->createOrganisation(
            'NorthPole Technologies',
            'northpole-technologies'
        );

        $organisation->users()->attach(
            $user->id,
            [
                'role' => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        return [
            $user,
            $organisation,
        ];
    }

    private function createOrganisation(
        string $name,
        string $slug
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

    private function createModule(
        string $key,
        string $name,
        bool $isCore = false
    ): MarketplaceModule {
        return MarketplaceModule::create([
            'key' => $key,
            'name' => $name,
            'description' => "{$name} module.",
            'version' => '1.0.0',
            'category' => $isCore
                ? 'Core'
                : 'Lifestyle',
            'icon' => $isCore
                ? 'building'
                : 'gift',
            'is_core' => $isCore,
            'is_active' => true,
        ]);
    }

    private function createInstallation(
        Organisation $organisation,
        MarketplaceModule $module,
        bool $isEnabled
    ): OrganisationModule {
        return app(TenantContext::class)->withoutTenancy(
            fn (): OrganisationModule => OrganisationModule::create([
                'organisation_id' => $organisation->id,
                'marketplace_module_id' => $module->id,
                'is_enabled' => $isEnabled,
                'installed_at' => now(),
            ])
        );
    }
}
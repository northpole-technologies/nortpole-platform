<?php

namespace Tests\Feature;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use App\Models\OrganisationModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleRegistryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_modules_can_be_listed(): void
    {
        MarketplaceModule::create([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'description' => 'Christmas planning and family experience module.',
            'version' => '1.0.0',
            'category' => 'Lifestyle',
            'icon' => 'gift',
            'is_core' => false,
            'is_active' => true,
        ]);

        MarketplaceModule::create([
            'key' => 'organisation-manager',
            'name' => 'Organisation Manager',
            'description' => 'Core organisation management module.',
            'version' => '1.0.0',
            'category' => 'Core',
            'icon' => 'building',
            'is_core' => true,
            'is_active' => true,
        ]);

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
        $module = MarketplaceModule::create([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'description' => 'Christmas planning and family experience module.',
            'version' => '1.0.0',
            'category' => 'Lifestyle',
            'icon' => 'gift',
            'is_core' => false,
            'is_active' => true,
        ]);

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

    public function test_a_marketplace_module_can_be_installed_for_an_organisation(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $module = MarketplaceModule::create([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'description' => 'Christmas planning and family experience module.',
            'version' => '1.0.0',
            'category' => 'Lifestyle',
            'icon' => 'gift',
            'is_core' => false,
            'is_active' => true,
        ]);

        $response = $this->postJson(
            "/api/v1/modules/{$module->id}/install",
            [
                'organisation_id' => $organisation->id,
            ]
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

    public function test_an_installed_module_can_be_disabled(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $module = MarketplaceModule::create([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'description' => 'Christmas planning and family experience module.',
            'version' => '1.0.0',
            'category' => 'Lifestyle',
            'icon' => 'gift',
            'is_core' => false,
            'is_active' => true,
        ]);

        OrganisationModule::create([
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
            'installed_at' => now(),
        ]);

        $response = $this->patchJson(
            "/api/v1/modules/{$module->id}/disable",
            [
                'organisation_id' => $organisation->id,
            ]
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

    public function test_a_disabled_module_can_be_enabled(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $module = MarketplaceModule::create([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'description' => 'Christmas planning and family experience module.',
            'version' => '1.0.0',
            'category' => 'Lifestyle',
            'icon' => 'gift',
            'is_core' => false,
            'is_active' => true,
        ]);

        OrganisationModule::create([
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => false,
            'installed_at' => now(),
        ]);

        $response = $this->patchJson(
            "/api/v1/modules/{$module->id}/enable",
            [
                'organisation_id' => $organisation->id,
            ]
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

    public function test_an_installed_module_can_be_uninstalled(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $module = MarketplaceModule::create([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'description' => 'Christmas planning and family experience module.',
            'version' => '1.0.0',
            'category' => 'Lifestyle',
            'icon' => 'gift',
            'is_core' => false,
            'is_active' => true,
        ]);

        OrganisationModule::create([
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
            'installed_at' => now(),
        ]);

        $response = $this->deleteJson(
            "/api/v1/modules/{$module->id}/uninstall",
            [
                'organisation_id' => $organisation->id,
            ]
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

    public function test_an_uninstalled_module_can_be_reinstalled(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $module = MarketplaceModule::create([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'description' => 'Christmas planning and family experience module.',
            'version' => '1.0.0',
            'category' => 'Lifestyle',
            'icon' => 'gift',
            'is_core' => false,
            'is_active' => true,
        ]);

        $installation = OrganisationModule::create([
            'organisation_id' => $organisation->id,
            'marketplace_module_id' => $module->id,
            'is_enabled' => true,
            'installed_at' => now(),
        ]);

        $installation->delete();

        $response = $this->postJson(
            "/api/v1/modules/{$module->id}/install",
            [
                'organisation_id' => $organisation->id,
            ]
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
            OrganisationModule::withTrashed()
                ->where('organisation_id', $organisation->id)
                ->where('marketplace_module_id', $module->id)
                ->count()
        );
    }
}
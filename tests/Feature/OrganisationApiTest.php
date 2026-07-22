<?php

namespace Tests\Feature;

use App\Models\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_organisations_can_be_listed(): void
    {
        Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        Organisation::create([
            'name' => 'Santa Buddy',
            'slug' => 'santa-buddy',
            'email' => 'hello@santabuddy.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $response = $this->getJson('/api/v1/organisations');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'name' => 'Northpole Technologies',
            ])
            ->assertJsonFragment([
                'name' => 'Santa Buddy',
            ]);
    }

    public function test_an_organisation_can_be_viewed(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $response = $this->getJson(
            "/api/v1/organisations/{$organisation->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $organisation->id)
            ->assertJsonPath('data.name', 'Northpole Technologies')
            ->assertJsonPath('data.slug', 'northpole-technologies');
    }

    public function test_an_organisation_can_be_updated(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $response = $this->patchJson(
            "/api/v1/organisations/{$organisation->id}",
            [
                'name' => 'Northpole Technologies Ireland',
                'slug' => 'northpole-technologies-ireland',
                'email' => 'hello@northpole.ie',
                'country' => 'IE',
                'timezone' => 'Europe/Dublin',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Organisation updated successfully.'
            )
            ->assertJsonPath(
                'data.name',
                'Northpole Technologies Ireland'
            )
            ->assertJsonPath(
                'data.slug',
                'northpole-technologies-ireland'
            );

        $this->assertDatabaseHas('organisations', [
            'id' => $organisation->id,
            'name' => 'Northpole Technologies Ireland',
            'slug' => 'northpole-technologies-ireland',
            'email' => 'hello@northpole.ie',
        ]);
    }

    public function test_an_organisation_can_be_deleted(): void
    {
        $organisation = Organisation::create([
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $response = $this->deleteJson(
            "/api/v1/organisations/{$organisation->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Organisation deleted successfully.'
            );

        $this->assertSoftDeleted('organisations', [
            'id' => $organisation->id,
        ]);
    }

    public function test_an_organisation_can_be_created(): void
    {
        $response = $this->postJson('/api/v1/organisations', [
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Organisation created successfully.'
            )
            ->assertJsonPath(
                'data.slug',
                'northpole-technologies'
            );

        $this->assertDatabaseHas('organisations', [
            'name' => 'Northpole Technologies',
            'slug' => 'northpole-technologies',
            'email' => 'info@northpole.ie',
        ]);
    }

    public function test_name_and_slug_are_required(): void
    {
        $response = $this->postJson('/api/v1/organisations', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'slug',
            ]);
    }

    public function test_an_organisation_slug_must_be_unique(): void
    {
        Organisation::create([
            'name' => 'Existing Organisation',
            'slug' => 'northpole-technologies',
        ]);

        $response = $this->postJson('/api/v1/organisations', [
            'name' => 'Another Organisation',
            'slug' => 'northpole-technologies',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }
}
<?php

namespace Tests\Feature;

use App\Models\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationApiTest extends TestCase
{
    use RefreshDatabase;

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
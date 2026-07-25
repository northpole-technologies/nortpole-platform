<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantContextMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_member_can_resolve_an_active_organisation(): void
    {
        $user = User::factory()->create();

        $organisation = $this->createOrganisation(
            'NorthPole Technologies',
            'northpole-technologies'
        );

        $this->attachUser(
            $user,
            $organisation,
            true
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson('/api/v1/tenant/context');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.organisation.id',
                $organisation->id
            )
            ->assertJsonPath(
                'data.organisation.name',
                'NorthPole Technologies'
            );
    }

    public function test_the_organisation_header_is_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(
            '/api/v1/tenant/context'
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'The X-Organisation-Id header is required.',
            ]);
    }

    public function test_an_unauthenticated_request_is_rejected(): void
    {
        $organisation = $this->createOrganisation(
            'NorthPole Technologies',
            'northpole-technologies'
        );

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson('/api/v1/tenant/context');

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_a_user_cannot_access_another_organisation(): void
    {
        $user = User::factory()->create();

        $allowedOrganisation = $this->createOrganisation(
            'Allowed Organisation',
            'allowed-organisation'
        );

        $forbiddenOrganisation = $this->createOrganisation(
            'Forbidden Organisation',
            'forbidden-organisation'
        );

        $this->attachUser(
            $user,
            $allowedOrganisation,
            true
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $forbiddenOrganisation->id
            )
            ->getJson('/api/v1/tenant/context');

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Organisation access was not found.',
            ]);
    }

    public function test_an_inactive_membership_does_not_grant_access(): void
    {
        $user = User::factory()->create();

        $organisation = $this->createOrganisation(
            'NorthPole Technologies',
            'northpole-technologies'
        );

        $this->attachUser(
            $user,
            $organisation,
            false
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson('/api/v1/tenant/context');

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Organisation access was not found.',
            ]);
    }

    public function test_an_inactive_organisation_cannot_be_resolved(): void
    {
        $user = User::factory()->create();

        $organisation = $this->createOrganisation(
            'Inactive Organisation',
            'inactive-organisation',
            false
        );

        $this->attachUser(
            $user,
            $organisation,
            true
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson('/api/v1/tenant/context');

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Organisation access was not found.',
            ]);
    }

    public function test_an_unknown_organisation_does_not_grant_access(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                '01J00000000000000000000000'
            )
            ->getJson('/api/v1/tenant/context');

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Organisation access was not found.',
            ]);
    }

    private function createOrganisation(
        string $name,
        string $slug,
        bool $active = true
    ): Organisation {
        return Organisation::create([
            'name' => $name,
            'slug' => $slug,
            'active' => $active,
        ]);
    }

    private function attachUser(
        User $user,
        Organisation $organisation,
        bool $isActive
    ): void {
        $organisation->users()->attach(
            $user->id,
            [
                'role' => 'member',
                'is_active' => $isActive,
                'joined_at' => now(),
            ]
        );
    }
}

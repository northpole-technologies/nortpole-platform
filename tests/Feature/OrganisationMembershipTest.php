<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_belong_to_an_organisation(): void
    {
        $user = User::factory()->create();

        $organisation = Organisation::create([
            'name' => 'NorthPole Technologies',
            'slug' => 'northpole-technologies',
            'active' => true,
        ]);

        $organisation->users()->attach($user->id, [
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->assertTrue(
            $user->fresh()->organisations->contains($organisation)
        );

        $this->assertSame(
            'owner',
            $user->fresh()
                ->organisations
                ->first()
                ->pivot
                ->role
        );
    }

    public function test_an_organisation_can_have_multiple_users(): void
    {
        $organisation = Organisation::create([
            'name' => 'NorthPole Technologies',
            'slug' => 'northpole-technologies',
            'active' => true,
        ]);

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $organisation->users()->attach($user->id, [
                'role' => 'member',
                'is_active' => true,
                'joined_at' => now(),
            ]);
        }

        $this->assertCount(
            3,
            $organisation->fresh()->users
        );
    }

    public function test_a_user_can_belong_to_multiple_organisations(): void
    {
        $user = User::factory()->create();

        $firstOrganisation = Organisation::create([
            'name' => 'NorthPole Technologies',
            'slug' => 'northpole-technologies',
            'active' => true,
        ]);

        $secondOrganisation = Organisation::create([
            'name' => 'Santa Operations',
            'slug' => 'santa-operations',
            'active' => true,
        ]);

        $user->organisations()->attach(
            $firstOrganisation->id,
            [
                'role' => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $user->organisations()->attach(
            $secondOrganisation->id,
            [
                'role' => 'member',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $this->assertCount(
            2,
            $user->fresh()->organisations
        );
    }

    public function test_inactive_memberships_are_excluded_from_active_organisations(): void
    {
        $user = User::factory()->create();

        $activeOrganisation = Organisation::create([
            'name' => 'Active Organisation',
            'slug' => 'active-organisation',
            'active' => true,
        ]);

        $inactiveOrganisation = Organisation::create([
            'name' => 'Inactive Membership Organisation',
            'slug' => 'inactive-membership-organisation',
            'active' => true,
        ]);

        $user->organisations()->attach(
            $activeOrganisation->id,
            [
                'role' => 'member',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $user->organisations()->attach(
            $inactiveOrganisation->id,
            [
                'role' => 'member',
                'is_active' => false,
                'joined_at' => now(),
            ]
        );

        $activeOrganisations = $user->fresh()
            ->activeOrganisations()
            ->get();

        $this->assertCount(1, $activeOrganisations);

        $this->assertTrue(
            $activeOrganisations->contains($activeOrganisation)
        );

        $this->assertFalse(
            $activeOrganisations->contains($inactiveOrganisation)
        );
    }

    public function test_belongs_to_organisation_checks_active_membership(): void
    {
        $user = User::factory()->create();

        $organisation = Organisation::create([
            'name' => 'NorthPole Technologies',
            'slug' => 'northpole-technologies',
            'active' => true,
        ]);

        $user->organisations()->attach(
            $organisation->id,
            [
                'role' => 'member',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $this->assertTrue(
            $user->fresh()->belongsToOrganisation($organisation)
        );

        $this->assertTrue(
            $user->fresh()->belongsToOrganisation($organisation->id)
        );
    }

    public function test_inactive_membership_does_not_grant_organisation_access(): void
    {
        $user = User::factory()->create();

        $organisation = Organisation::create([
            'name' => 'NorthPole Technologies',
            'slug' => 'northpole-technologies',
            'active' => true,
        ]);

        $user->organisations()->attach(
            $organisation->id,
            [
                'role' => 'member',
                'is_active' => false,
                'joined_at' => now(),
            ]
        );

        $this->assertFalse(
            $user->fresh()->belongsToOrganisation($organisation)
        );
    }
}

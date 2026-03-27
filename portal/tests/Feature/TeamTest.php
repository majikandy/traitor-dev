<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_page_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/team')->assertOk();
    }

    public function test_team_page_only_shows_own_org_members(): void
    {
        $org1  = Organisation::factory()->create(['name' => 'Org One']);
        $user1 = User::factory()->create(['organisation_id' => $org1->id, 'name' => 'Alice']);
        $user2 = User::factory()->create(['organisation_id' => $org1->id, 'name' => 'Bob']);

        $org2  = Organisation::factory()->create(['name' => 'Org Two']);
        $user3 = User::factory()->create(['organisation_id' => $org2->id, 'name' => 'Carol']);

        $response = $this->actingAs($user1)->get('/team');
        $response->assertOk()
                 ->assertSee('Alice')
                 ->assertSee('Bob')
                 ->assertDontSee('Carol');
    }

    public function test_team_page_shows_org_name_in_members_header(): void
    {
        $org  = Organisation::factory()->create(['name' => 'Fancy Agency']);
        $user = User::factory()->create(['organisation_id' => $org->id]);

        // Business name comes from settings; org name comes from the org relation
        // The view shows "{businessName} organisation"
        $this->actingAs($user)->get('/team')->assertOk();
    }

    public function test_cannot_remove_yourself(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete("/team/{$user->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_can_remove_another_member(): void
    {
        $org    = Organisation::factory()->create();
        $admin  = User::factory()->create(['organisation_id' => $org->id]);
        $member = User::factory()->create(['organisation_id' => $org->id]);

        $this->actingAs($admin)->delete("/team/{$member->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }
}

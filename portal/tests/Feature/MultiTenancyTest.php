<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sites_are_scoped_to_organisation(): void
    {
        $org1  = Organisation::factory()->create();
        $user1 = User::factory()->create(['organisation_id' => $org1->id]);
        Site::factory()->create(['organisation_id' => $org1->id, 'name' => 'Org1 Site']);

        $org2  = Organisation::factory()->create();
        $user2 = User::factory()->create(['organisation_id' => $org2->id]);
        Site::factory()->create(['organisation_id' => $org2->id, 'name' => 'Org2 Site']);

        $this->actingAs($user1)->get('/sites')
            ->assertOk()
            ->assertSee('Org1 Site')
            ->assertDontSee('Org2 Site');

        $this->actingAs($user2)->get('/sites')
            ->assertOk()
            ->assertSee('Org2 Site')
            ->assertDontSee('Org1 Site');
    }

    public function test_cannot_view_another_orgs_site(): void
    {
        $org1  = Organisation::factory()->create();
        $user1 = User::factory()->create(['organisation_id' => $org1->id]);

        $org2  = Organisation::factory()->create();
        $site2 = Site::factory()->create(['organisation_id' => $org2->id]);

        $this->actingAs($user1)->get("/sites/{$site2->id}")->assertNotFound();
    }

    public function test_two_orgs_can_have_sites_with_the_same_slug(): void
    {
        $org1 = Organisation::factory()->create();
        $org2 = Organisation::factory()->create();

        Site::factory()->create(['organisation_id' => $org1->id, 'slug' => 'my-site']);
        Site::factory()->create(['organisation_id' => $org2->id, 'slug' => 'my-site']);

        $this->assertDatabaseCount('sites', 2);
    }

    public function test_dashboard_only_lists_own_sites(): void
    {
        $org1  = Organisation::factory()->create();
        $user1 = User::factory()->create(['organisation_id' => $org1->id]);
        Site::factory()->create(['organisation_id' => $org1->id, 'name' => 'My Site']);

        $org2  = Organisation::factory()->create();
        Site::factory()->create(['organisation_id' => $org2->id, 'name' => 'Other Site']);

        $this->actingAs($user1)->get('/')
            ->assertOk()
            ->assertSee('My Site')
            ->assertDontSee('Other Site');
    }
}

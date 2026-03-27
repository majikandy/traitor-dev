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
        $site1 = Site::factory()->create(['organisation_id' => $org1->id, 'name' => 'Org1 Site']);

        $org2  = Organisation::factory()->create();
        $user2 = User::factory()->create(['organisation_id' => $org2->id]);
        $site2 = Site::factory()->create(['organisation_id' => $org2->id, 'name' => 'Org2 Site']);

        // user1 sees only their site
        $this->actingAs($user1);
        $this->assertCount(1, Site::all());
        $this->assertSame('Org1 Site', Site::first()->name);

        // user2 sees only their site
        $this->actingAs($user2);
        $this->assertCount(1, Site::all());
        $this->assertSame('Org2 Site', Site::first()->name);
    }

    public function test_cannot_view_another_orgs_site(): void
    {
        $org1  = Organisation::factory()->create();
        $user1 = User::factory()->create(['organisation_id' => $org1->id]);

        $org2  = Organisation::factory()->create();
        $site2 = Site::factory()->create(['organisation_id' => $org2->id]);

        $this->actingAs($user1)->get("/sites/{$site2->id}")->assertNotFound();
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

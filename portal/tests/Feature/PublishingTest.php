<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Release;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Publishing lifecycle: create release, promote, rollback, revert to coming soon,
 * maintenance mode, and versioned share links.
 */
class PublishingTest extends TestCase
{
    use RefreshDatabase;

    private string $sitesPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sitesPath = sys_get_temp_dir() . '/traitor-test-' . uniqid();
        config(['sites.path' => $this->sitesPath]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_dir($this->sitesPath)) {
            File::deleteDirectory($this->sitesPath);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUser(): array
    {
        $org  = Organisation::factory()->create();
        $user = User::factory()->create(['organisation_id' => $org->id]);
        return [$org, $user];
    }

    /**
     * Create a site with N releases on disk and in the DB.
     * The last version is optionally set as live.
     */
    private function scaffoldSite(User $user, int $releaseCount = 1, bool $live = true): Site
    {
        $site = Site::factory()->create([
            'organisation_id' => $user->organisation_id,
            'current_release' => $releaseCount,
            'live_release'    => $live ? $releaseCount : null,
        ]);

        $sitePath = $this->sitesPath . '/' . $site->slug;

        // Create all release directories
        for ($v = 1; $v <= $releaseCount; $v++) {
            mkdir($sitePath . '/releases/' . $v . '/public', 0755, true);
            Release::create(['site_id' => $site->id, 'version' => $v, 'created_at' => now()]);
        }

        // Coming-soon page (always needed for revert/maintenance)
        mkdir($sitePath . '/coming-soon/public', 0755, true);
        file_put_contents($sitePath . '/coming-soon/public/index.html', '');

        // Live symlink
        $target = $live
            ? $sitePath . '/releases/' . $releaseCount
            : $sitePath . '/coming-soon';
        symlink($target, $sitePath . '/live');

        return $site->fresh();
    }

    // -------------------------------------------------------------------------
    // Promote / Make Current
    // -------------------------------------------------------------------------

    public function test_promote_release_makes_it_live(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: false);

        $this->actingAs($user)
            ->post("/sites/{$site->id}/releases/1/promote")
            ->assertRedirect();

        $this->assertDatabaseHas('sites', [
            'id'           => $site->id,
            'live_release' => 1,
        ]);
    }

    public function test_promote_turns_off_maintenance_mode(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);
        $site->update(['maintenance_mode' => true]);

        $this->actingAs($user)
            ->post("/sites/{$site->id}/releases/1/promote")
            ->assertRedirect();

        $this->assertDatabaseHas('sites', [
            'id'               => $site->id,
            'maintenance_mode' => false,
        ]);
    }

    public function test_rollback_to_older_version(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 2, live: true); // v2 is live

        $this->actingAs($user)
            ->post("/sites/{$site->id}/releases/1/promote") // roll back to v1
            ->assertRedirect();

        $this->assertDatabaseHas('sites', [
            'id'           => $site->id,
            'live_release' => 1,
        ]);
    }

    public function test_cannot_promote_nonexistent_release(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);

        $this->actingAs($user)
            ->post("/sites/{$site->id}/releases/99/promote")
            ->assertNotFound();
    }

    public function test_cannot_promote_another_orgs_release(): void
    {
        [$org1, $user1] = $this->makeUser();
        [$org2, $user2] = $this->makeUser();
        $site2 = $this->scaffoldSite($user2, releaseCount: 1, live: false);

        $this->actingAs($user1)
            ->post("/sites/{$site2->id}/releases/1/promote")
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Revert to Coming Soon
    // -------------------------------------------------------------------------

    public function test_revert_to_coming_soon_clears_live_release(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);

        $this->actingAs($user)
            ->post("/sites/{$site->id}/revert-to-coming-soon")
            ->assertRedirect();

        $this->assertDatabaseHas('sites', [
            'id'               => $site->id,
            'live_release'     => null,
            'maintenance_mode' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // Maintenance mode
    // -------------------------------------------------------------------------

    public function test_enable_maintenance_mode(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);

        $this->actingAs($user)
            ->post("/sites/{$site->id}/maintenance")
            ->assertRedirect();

        $this->assertDatabaseHas('sites', [
            'id'               => $site->id,
            'maintenance_mode' => true,
        ]);
    }

    public function test_disable_maintenance_mode_restores_live_release(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);
        $site->update(['maintenance_mode' => true]);

        $this->actingAs($user)
            ->post("/sites/{$site->id}/maintenance")
            ->assertRedirect();

        $this->assertDatabaseHas('sites', [
            'id'               => $site->id,
            'maintenance_mode' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // Versioned share links (marker files, not DB)
    // -------------------------------------------------------------------------

    public function test_enable_version_preview_creates_marker_file(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);

        $this->actingAs($user)
            ->post("/sites/{$site->id}/releases/1/version-preview")
            ->assertRedirect();

        $markerFile = $this->sitesPath . '/' . $site->slug . '/releases/1/.preview-enabled';
        $this->assertFileExists($markerFile);
        $this->assertNotEmpty(trim(file_get_contents($markerFile)));
    }

    public function test_regenerate_version_preview_changes_token(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);

        $markerFile = $this->sitesPath . '/' . $site->slug . '/releases/1/.preview-enabled';
        file_put_contents($markerFile, 'original-token');

        $this->actingAs($user)
            ->post("/sites/{$site->id}/releases/1/version-preview/regenerate")
            ->assertRedirect();

        $this->assertNotEquals('original-token', trim(file_get_contents($markerFile)));
    }

    public function test_disable_version_preview_removes_marker_file(): void
    {
        [$org, $user] = $this->makeUser();
        $site = $this->scaffoldSite($user, releaseCount: 1, live: true);

        $markerFile = $this->sitesPath . '/' . $site->slug . '/releases/1/.preview-enabled';
        file_put_contents($markerFile, 'some-token');

        $this->actingAs($user)
            ->delete("/sites/{$site->id}/releases/1/version-preview")
            ->assertRedirect();

        $this->assertFileDoesNotExist($markerFile);
    }
}

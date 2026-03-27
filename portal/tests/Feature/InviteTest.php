<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use App\Notifications\InviteUserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InviteTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $org  = Organisation::factory()->create(['name' => 'Test Co']);
        $user = User::factory()->create(['organisation_id' => $org->id]);
        $this->actingAs($user);
        return $user;
    }

    public function test_invite_creates_pending_user_and_sends_notification(): void
    {
        Notification::fake();
        $admin = $this->actingAsAdmin();

        $this->post('/team', ['name' => 'Bob', 'email' => 'bob@example.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $invited = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($invited);
        $this->assertNull($invited->signed_up_at);
        $this->assertNotNull($invited->invite_token);
        $this->assertSame($admin->organisation_id, $invited->organisation_id);

        Notification::assertSentTo($invited, InviteUserNotification::class);
    }

    public function test_invite_link_logs_user_in_and_redirects_to_setup(): void
    {
        $org   = Organisation::factory()->create();
        $token = 'abc123testtoken';
        $user  = User::factory()->create([
            'organisation_id' => $org->id,
            'has_password'    => false,
            'signed_up_at'    => null,
            'invite_token'    => $token,
        ]);

        $this->get("/invite/{$token}")
            ->assertRedirect('/setup');

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_invite_token_redirects_to_login_with_error(): void
    {
        $this->get('/invite/badtoken')
            ->assertRedirect('/login')
            ->assertSessionHasErrors();
    }

    public function test_resend_invite_generates_new_token_and_sends_notification(): void
    {
        Notification::fake();
        $admin = $this->actingAsAdmin();

        $originalToken = 'originaltoken123';
        $pending = User::factory()->create([
            'organisation_id' => $admin->organisation_id,
            'has_password'    => false,
            'signed_up_at'    => null,
            'invite_token'    => $originalToken,
        ]);

        $this->post("/team/{$pending->id}/resend-invite")
            ->assertRedirect()
            ->assertSessionHas('success');

        $pending->refresh();
        $this->assertNotSame($originalToken, $pending->invite_token);
        Notification::assertSentTo($pending, InviteUserNotification::class);
    }

    public function test_cancel_invite_deletes_pending_user(): void
    {
        $admin = $this->actingAsAdmin();
        $pending = User::factory()->create([
            'organisation_id' => $admin->organisation_id,
            'has_password'    => false,
            'signed_up_at'    => null,
            'invite_token'    => 'sometoken',
        ]);

        $this->delete("/team/{$pending->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $pending->id]);
    }

    public function test_cannot_resend_invite_for_user_in_another_org(): void
    {
        $this->actingAsAdmin();

        $otherOrg  = Organisation::factory()->create();
        $otherUser = User::factory()->create([
            'organisation_id' => $otherOrg->id,
            'signed_up_at'    => null,
            'invite_token'    => 'token',
        ]);

        $this->post("/team/{$otherUser->id}/resend-invite")->assertStatus(403);
    }

    public function test_cannot_cancel_invite_for_user_in_another_org(): void
    {
        $this->actingAsAdmin();

        $otherOrg  = Organisation::factory()->create();
        $otherUser = User::factory()->create(['organisation_id' => $otherOrg->id]);

        $this->delete("/team/{$otherUser->id}")->assertStatus(403);
    }

    public function test_duplicate_email_invite_is_rejected(): void
    {
        $admin = $this->actingAsAdmin();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/team', ['name' => 'Dupe', 'email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');
    }
}

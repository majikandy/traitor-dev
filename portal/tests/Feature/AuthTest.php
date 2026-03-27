<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_login_with_password(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post('/login', ['email' => 'test@example.com', 'password' => 'password'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong'])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_unknown_email_is_rejected(): void
    {
        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_clears_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_without_auth_method_is_gated_to_setup(): void
    {
        $org  = Organisation::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $org->id,
            'has_password'    => false,
            'signed_up_at'    => null,
        ]);

        // Force-login (bypassing password check) to simulate invite acceptance
        $this->actingAs($user)->get('/')->assertRedirect('/setup');
    }

    public function test_setup_page_loads_for_gated_user(): void
    {
        $org  = Organisation::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $org->id,
            'has_password'    => false,
            'signed_up_at'    => null,
        ]);

        $this->actingAs($user)->get('/setup')->assertOk();
    }

    public function test_setup_password_sets_has_password_and_signed_up_at(): void
    {
        $org  = Organisation::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $org->id,
            'has_password'    => false,
            'signed_up_at'    => null,
        ]);

        $this->actingAs($user)->post('/setup/password', [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect('/');

        $user->refresh();
        $this->assertTrue($user->has_password);
        $this->assertNotNull($user->signed_up_at);
    }

    public function test_register_page_loads(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_password_registration_creates_org_and_user(): void
    {
        $this->post('/register', [
            'organisation'          => 'ACME Ltd',
            'name'                  => 'Alice',
            'email'                 => 'alice@acme.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('organisations', ['name' => 'ACME Ltd']);
        $this->assertDatabaseHas('users', ['email' => 'alice@acme.com', 'has_password' => true]);
    }
}

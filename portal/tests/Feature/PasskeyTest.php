<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasskeyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Register options
    // -------------------------------------------------------------------------

    public function test_register_options_requires_auth(): void
    {
        $this->get('/passkeys/register-options')->assertRedirect('/login');
    }

    // -------------------------------------------------------------------------
    // Authenticate — session guard
    // -------------------------------------------------------------------------

    public function test_authenticate_without_session_options_fails(): void
    {
        // No prior call to auth-options so session has no challenge
        $this->postJson('/passkeys/authenticate', ['type' => 'public-key', 'id' => 'fake', 'rawId' => 'fake'])
            ->assertStatus(500); // session key missing → exception
    }

    // -------------------------------------------------------------------------
    // Delete passkey
    // -------------------------------------------------------------------------

    public function test_cannot_delete_another_users_passkey(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $passkey = Passkey::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1)
            ->delete("/passkeys/{$passkey->id}")
            ->assertStatus(403);
    }

    public function test_cannot_delete_last_passkey_without_password(): void
    {
        $org  = Organisation::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $org->id,
            'has_password'    => false,
        ]);
        $passkey = Passkey::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete("/passkeys/{$passkey->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('passkeys', ['id' => $passkey->id]);
    }

    public function test_can_delete_passkey_when_password_exists(): void
    {
        $user    = User::factory()->create(['has_password' => true]);
        $passkey = Passkey::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete("/passkeys/{$passkey->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('passkeys', ['id' => $passkey->id]);
    }

    public function test_can_delete_one_passkey_when_multiple_exist(): void
    {
        $user     = User::factory()->create(['has_password' => false]);
        $passkey1 = Passkey::factory()->create(['user_id' => $user->id]);
        $passkey2 = Passkey::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete("/passkeys/{$passkey1->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('passkeys', ['id' => $passkey1->id]);
        $this->assertDatabaseHas('passkeys', ['id' => $passkey2->id]);
    }
}

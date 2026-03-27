<?php

namespace Database\Factories;

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasskeyFactory extends Factory
{
    protected $model = Passkey::class;

    public function definition(): array
    {
        // Minimal credential source structure that satisfies the model's credentialSource() method.
        $credentialSource = json_encode([
            'publicKeyCredentialId' => base64_encode(random_bytes(32)),
            'type' => 'public-key',
            'transports' => [],
            'attestationType' => 'none',
            'trustPath' => ['type' => 'Webauthn\\TrustPath\\EmptyTrustPath'],
            'aaguid' => '00000000-0000-0000-0000-000000000000',
            'credentialPublicKey' => base64_encode(random_bytes(64)),
            'userHandle' => base64_encode('1'),
            'counter' => 0,
            'otherUI' => null,
            'backupEligible' => false,
            'backupStatus' => false,
            'uvInitialized' => false,
        ]);

        return [
            'user_id'           => User::factory(),
            'name'              => 'Test Passkey',
            'credential_id'     => bin2hex(random_bytes(32)),
            'credential_source' => $credentialSource,
        ];
    }
}

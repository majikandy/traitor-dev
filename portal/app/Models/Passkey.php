<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webauthn\PublicKeyCredentialSource;

class Passkey extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name', 'credential_id', 'credential_source'];

    public function credentialSource(): PublicKeyCredentialSource
    {
        $data = json_decode($this->credential_source, true);

        // Legacy records were double-encoded; decode again if we still have a string
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return PublicKeyCredentialSource::createFromArray($data);
    }
}

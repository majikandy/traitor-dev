<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webauthn\PublicKeyCredentialSource;

class Passkey extends Model
{
    protected $fillable = ['user_id', 'name', 'credential_id', 'credential_source'];

    public function credentialSource(): PublicKeyCredentialSource
    {
        return PublicKeyCredentialSource::createFromArray(
            json_decode($this->credential_source, true)
        );
    }
}

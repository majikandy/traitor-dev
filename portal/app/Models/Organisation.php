<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisation extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'github_installation_id'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function hasGitHub(): bool
    {
        return $this->github_installation_id !== null;
    }

    public function githubInstallationUrl(): string
    {
        return "https://github.com/settings/installations/{$this->github_installation_id}";
    }
}

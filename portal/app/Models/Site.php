<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['name', 'slug', 'domain', 'status', 'current_release'];

    public function releases(): HasMany
    {
        return $this->hasMany(Release::class)->orderByDesc('version');
    }

    public function sitesPath(): string
    {
        return config('sites.path') . '/' . $this->slug;
    }

    public function draftsPath(): string
    {
        return $this->sitesPath() . '/drafts/public';
    }

    public function releasePath(int $version): string
    {
        return $this->sitesPath() . '/releases/' . $version . '/public';
    }

    public function livePath(): string
    {
        return $this->sitesPath() . '/live';
    }

    public function previewUrl(): string
    {
        return $this->slug . '.sites.traitor.dev';
    }
}

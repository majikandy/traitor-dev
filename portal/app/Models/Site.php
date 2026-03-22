<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Site extends Model
{
    protected $fillable = ['name', 'slug', 'preview_token', 'domain', 'status', 'current_release'];

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            $site->preview_token = Str::uuid()->toString();
        });
    }

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
        return url('/preview/' . $this->preview_token);
    }
}

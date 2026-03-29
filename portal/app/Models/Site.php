<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Site extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'organisation_id', 'preview_token', 'domain', 'domain_status', 'maintenance_mode', 'maintenance_page', 'launch_date', 'status', 'current_release', 'live_release', 'github_repo', 'github_repo_path', 'github_branch', 'github_auto_deploy'];

    protected $casts = ['maintenance_mode' => 'boolean', 'github_auto_deploy' => 'boolean', 'launch_date' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            $site->preview_token = Str::uuid()->toString();
        });

        // Scope all queries to the authenticated user's organisation.
        // Skipped when unauthenticated (e.g. preview controller uses token-based access).
        static::addGlobalScope('organisation', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('sites.organisation_id', auth()->user()->organisation_id);
            }
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
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

    public function stagingUrl(): string
    {
        return 'https://' . $this->slug . '.' . config('services.cpanel.staging_domain');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Release extends Model
{
    public $timestamps = false;

    protected $fillable = ['site_id', 'version', 'preview_token', 'preview_shared', 'notes', 'build_error', 'created_at'];

    public function failed(): bool
    {
        return $this->build_error !== null;
    }

    protected $casts = [
        'created_at'     => 'datetime',
        'preview_shared' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Release $release) {
            $release->preview_token = Str::uuid()->toString();
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function previewUrl(): string
    {
        return url('/preview/' . $this->preview_token);
    }
}

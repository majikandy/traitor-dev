<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Release extends Model
{
    public $timestamps = false;

    protected $fillable = ['site_id', 'version', 'preview_token', 'notes', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'organisation_id'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $orgId = auth()->user()?->organisation_id;

        return static::where('key', $key)->where('organisation_id', $orgId)->first()?->value ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        $orgId = auth()->user()?->organisation_id;

        static::updateOrCreate(
            ['key' => $key, 'organisation_id' => $orgId],
            ['value' => $value]
        );
    }
}

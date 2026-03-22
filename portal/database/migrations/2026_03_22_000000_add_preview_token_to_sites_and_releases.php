<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->uuid('preview_token')->unique()->after('slug');
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->uuid('preview_token')->unique()->after('version');
        });

        // Backfill existing rows
        foreach (\App\Models\Site::all() as $site) {
            $site->update(['preview_token' => Str::uuid()->toString()]);
        }

        foreach (\App\Models\Release::all() as $release) {
            $release->update(['preview_token' => Str::uuid()->toString()]);
        }
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('preview_token');
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->dropColumn('preview_token');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->unsignedBigInteger('github_installation_id')->nullable()->after('name');
        });

        // Migrate any existing installation IDs from sites to their org.
        // If multiple sites in the same org had different IDs (shouldn't happen), last one wins.
        if (Schema::hasColumn('sites', 'github_installation_id')) {
            DB::table('sites')
                ->whereNotNull('github_installation_id')
                ->orderBy('id')
                ->each(function ($site) {
                    DB::table('organisations')
                        ->where('id', $site->organisation_id)
                        ->whereNull('github_installation_id')
                        ->update(['github_installation_id' => $site->github_installation_id]);
                });

            Schema::table('sites', function (Blueprint $table) {
                $table->dropColumn('github_installation_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('github_installation_id')->nullable()->after('live_release');
        });

        // Restore installation IDs from org back to all its sites
        DB::table('organisations')
            ->whereNotNull('github_installation_id')
            ->each(function ($org) {
                DB::table('sites')
                    ->where('organisation_id', $org->id)
                    ->update(['github_installation_id' => $org->github_installation_id]);
            });

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('github_installation_id');
        });
    }
};

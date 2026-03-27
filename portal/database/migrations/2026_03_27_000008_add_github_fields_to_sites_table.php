<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('github_installation_id')->nullable()->after('live_release');
            $table->string('github_repo')->nullable()->after('github_installation_id');
            $table->boolean('github_auto_deploy')->default(false)->after('github_repo');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['github_installation_id', 'github_repo', 'github_auto_deploy']);
        });
    }
};

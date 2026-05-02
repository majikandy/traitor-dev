<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('admin_url')->nullable()->after('github_auto_deploy');
            $table->string('admin_token_secret')->nullable()->after('admin_url');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['admin_url', 'admin_token_secret']);
        });
    }
};

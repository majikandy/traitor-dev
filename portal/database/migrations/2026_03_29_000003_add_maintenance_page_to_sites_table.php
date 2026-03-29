<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('maintenance_page')->default('brb')->after('maintenance_mode');
            $table->date('launch_date')->nullable()->after('maintenance_page');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['maintenance_page', 'launch_date']);
        });
    }
};

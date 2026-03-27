<?php

use App\Models\Organisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create a default organisation for all existing data
        $orgId = DB::table('organisations')->insertGetId([
            'name'       => 'Default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
        });
        DB::table('users')->update(['organisation_id' => $orgId]);

        Schema::table('sites', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->cascadeOnDelete();
        });
        DB::table('sites')->update(['organisation_id' => $orgId]);

        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->cascadeOnDelete();
        });
        DB::table('settings')->update(['organisation_id' => $orgId]);
    }

    public function down(): void
    {
        Schema::table('settings', fn(Blueprint $t) => $t->dropForeign(['organisation_id']));
        Schema::table('settings', fn(Blueprint $t) => $t->dropColumn('organisation_id'));

        Schema::table('sites', fn(Blueprint $t) => $t->dropForeign(['organisation_id']));
        Schema::table('sites', fn(Blueprint $t) => $t->dropColumn('organisation_id'));

        Schema::table('users', fn(Blueprint $t) => $t->dropForeign(['organisation_id']));
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn('organisation_id'));
    }
};

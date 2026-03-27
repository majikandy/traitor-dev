<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recreate the settings table with an auto-increment id primary key
        // and a unique constraint on (key, organisation_id).
        // We use a tmp table approach to stay compatible with SQLite (local)
        // and MySQL (production).

        $existing = DB::table('settings')->get();

        Schema::drop('settings');

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['key', 'organisation_id']);
        });

        foreach ($existing as $row) {
            DB::table('settings')->insert((array) $row);
        }
    }

    public function down(): void
    {
        $existing = DB::table('settings')->get();

        Schema::drop('settings');

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        foreach ($existing as $row) {
            $data = (array) $row;
            unset($data['id']);
            DB::table('settings')->insert($data);
        }
    }
};

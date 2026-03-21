<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->enum('status', ['draft', 'live', 'paused'])->default('draft');
            $table->unsignedInteger('current_release')->nullable();
            $table->timestamps();
        });

        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('notes')->nullable();
            $table->timestamp('created_at');

            $table->unique(['site_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
        Schema::dropIfExists('sites');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (\App\Models\Release::with('site')->get() as $release) {
            $tokenFile = $release->site->sitesPath() . '/releases/' . $release->version . '/.preview-token';
            if (!file_exists($tokenFile) && is_dir(dirname($tokenFile))) {
                file_put_contents($tokenFile, $release->preview_token);
            }
        }
    }

    public function down(): void {}
};

<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SiteDbSetup extends Command
{
    protected $signature = 'site:db:setup {slug : The site slug}';
    protected $description = 'Create a MySQL database and user for a site, write shared/.env';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $site = Site::withoutGlobalScopes()->where('slug', $slug)->firstOrFail();

        $dbName = 'traitor_' . str_replace('-', '_', $slug);
        $dbUser = substr('t_' . str_replace('-', '_', $slug), 0, 32);
        $dbPass = Str::random(32);
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $appUrl = $site->domain
            ? 'https://' . $site->domain
            : 'https://' . $site->slug . '.' . config('services.cpanel.staging_domain');

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        DB::statement("CREATE USER IF NOT EXISTS '{$dbUser}'@'localhost' IDENTIFIED BY '{$dbPass}'");
        DB::statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'localhost'");
        DB::statement('FLUSH PRIVILEGES');

        $sharedPath = $site->sitesPath() . '/shared';
        File::ensureDirectoryExists($sharedPath);

        File::put($sharedPath . '/.env', implode("\n", [
            'APP_NAME="' . $site->name . '"',
            'APP_ENV=production',
            'APP_KEY=' . $appKey,
            'APP_DEBUG=false',
            'APP_URL=' . $appUrl,
            '',
            'DB_CONNECTION=mysql',
            'DB_HOST=127.0.0.1',
            'DB_PORT=3306',
            'DB_DATABASE=' . $dbName,
            'DB_USERNAME=' . $dbUser,
            'DB_PASSWORD=' . $dbPass,
            '',
            'LOG_CHANNEL=stack',
            'LOG_LEVEL=error',
            '',
            'CACHE_DRIVER=file',
            'SESSION_DRIVER=file',
            'QUEUE_CONNECTION=sync',
        ]));

        $this->info("Database : {$dbName}");
        $this->info("User     : {$dbUser}");
        $this->info(".env     : {$sharedPath}/.env");
        $this->newLine();
        $this->warn('DB_PASSWORD: ' . $dbPass);
        $this->warn('Save this — it will not be shown again.');

        return Command::SUCCESS;
    }
}

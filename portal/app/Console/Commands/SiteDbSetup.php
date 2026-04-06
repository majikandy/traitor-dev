<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\CpanelService;
use App\Services\SiteService;
use Illuminate\Console\Command;

class SiteDbSetup extends Command
{
    protected $signature = 'site:db:setup {slug : The site slug}';
    protected $description = 'Create a MySQL database and user for a site, write shared/.env';

    public function handle(SiteService $siteService, CpanelService $cpanel): int
    {
        $slug = $this->argument('slug');
        $site = Site::withoutGlobalScopes()->where('slug', $slug)->firstOrFail();

        $creds = $siteService->setupDatabase($site, $cpanel);

        $this->info("Database : {$creds['dbName']}");
        $this->info("User     : {$creds['dbUser']}");
        $this->info(".env     : {$site->sitesPath()}/shared/.env");
        $this->newLine();
        $this->warn('DB_PASSWORD: ' . $creds['dbPass']);
        $this->warn('Save this — it will not be shown again.');

        return Command::SUCCESS;
    }
}

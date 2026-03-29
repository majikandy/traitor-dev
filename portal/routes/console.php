<?php

use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $service = app(SiteService::class);

    Site::withoutGlobalScopes()
        ->where('maintenance_mode', true)
        ->where('maintenance_page', 'countdown')
        ->whereNotNull('launch_date')
        ->where('launch_date', '<=', now())
        ->each(function (Site $site) use ($service) {
            $version = $site->current_release ?? $site->live_release;
            if ($version) {
                $service->promote($site, $version);
            } else {
                $service->disableMaintenance($site);
            }
        });
})->everyMinute()->name('launch-countdown-sites'); // TODO: change to ->hourly() after testing

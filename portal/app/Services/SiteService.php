<?php

namespace App\Services;

use App\Models\Release;
use App\Models\Site;
use App\Services\GitHubService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;

class SiteService
{
    public function create(string $name, string $slug, int $organisationId): Site
    {
        $site = Site::create([
            'name'            => $name,
            'slug'            => $slug,
            'organisation_id' => $organisationId,
        ]);

        $sitePath = $site->sitesPath();
        File::ensureDirectoryExists($site->draftsPath());
        File::ensureDirectoryExists($sitePath . '/releases');

        // Placeholder shown in the preview before any files are uploaded
        File::put($site->draftsPath() . '/index.html', $this->draftPlaceholder($name));

        // Live symlink starts pointing at shared coming-soon until first Go Live
        $this->ensureSharedComingSoon();
        symlink($this->sharedComingSoonPath(), $sitePath . '/live');

        return $site;
    }

    public function upload(Site $site, UploadedFile $file): void
    {
        $this->uploadFromPath($site, $file->getPathname());
    }

    public function uploadFromPath(Site $site, string $zipPath, ?string $subPath = null): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Could not open zip file.');
        }

        $draftsPath = $site->draftsPath();

        File::cleanDirectory($draftsPath);
        $zip->extractTo($draftsPath);
        $zip->close();

        $this->hoistIfWrappedInSingleFolder($draftsPath);

        if ($subPath !== null) {
            $subDir = $draftsPath . '/' . trim($subPath, '/');
            if (!is_dir($subDir)) {
                throw new \RuntimeException("Subfolder '{$subPath}' not found in repository.");
            }
            $tmp = dirname($draftsPath) . '/.tmp_sub_' . uniqid();
            rename($subDir, $tmp);
            File::deleteDirectory($draftsPath);
            rename($tmp, $draftsPath);
        }
    }

    public function createRelease(Site $site, ?string $notes = null): Release
    {
        $nextVersion = $site->current_release + 1;
        $releasePath = $site->releasePath($nextVersion);

        // Snapshot drafts into a new release
        File::copyDirectory($site->draftsPath(), $releasePath);

        $release = Release::create([
            'site_id' => $site->id,
            'version' => $nextVersion,
            'notes' => $notes,
            'created_at' => now(),
        ]);

        // Write token file so the preview dispatcher can validate without a DB lookup
        File::put($releasePath . '/.preview-token', $release->preview_token);

        $site->update(['current_release' => $nextVersion]);

        return $release;
    }

    public function attachDomain(Site $site, string $domain, CpanelService $cpanel): void
    {
        $this->ensureLiveSymlink($site);

        $homeDir  = '/home/' . config('services.cpanel.user');
        $docroot  = ltrim(str_replace($homeDir . '/', '', $site->sitesPath() . '/live/public'), '/');

        $cpanel->createAddonDomain($domain, $docroot);

        $site->update(['domain' => $domain, 'domain_status' => 'pending_dns']);
    }

    public function detachDomain(Site $site, CpanelService $cpanel): void
    {
        $cpanel->removeAddonDomain($site->domain);

        $site->update(['domain' => null, 'domain_status' => null]);
    }

    public function checkDns(Site $site): bool
    {
        $serverIp = config('app.server_ip');
        $resolved = gethostbyname($site->domain);

        return $resolved === $serverIp;
    }

    private function ensureLiveSymlink(Site $site): void
    {
        $livePath = $site->sitesPath() . '/live';

        if (file_exists($livePath) || is_link($livePath)) {
            return;
        }

        if ($site->live_release) {
            $target = $site->sitesPath() . '/releases/' . $site->live_release;
        } else {
            $this->ensureSharedComingSoon();
            $target = $this->sharedComingSoonPath();
        }

        symlink($target, $livePath);
    }

    public function rotatePreviewToken(Site $site, int $version): string
    {
        $release = $site->releases()->where('version', $version)->firstOrFail();
        $token = \Illuminate\Support\Str::uuid()->toString();
        $release->update(['preview_token' => $token]);
        File::put($site->sitesPath() . '/releases/' . $version . '/.preview-token', $token);
        return $token;
    }

    public function promote(Site $site, int $version): void
    {
        $releasePath = $site->sitesPath() . '/releases/' . $version;

        if (!is_dir($releasePath)) {
            throw new \RuntimeException("Release {$version} not found at {$releasePath}.");
        }

        $this->swapLiveSymlink($site, $releasePath);

        $site->update(['live_release' => $version, 'maintenance_mode' => false]);
    }

    public function revertToComingSoon(Site $site): void
    {
        $comingSoon = $site->sitesPath() . '/coming-soon';
        if (!is_dir($comingSoon . '/public')) {
            File::ensureDirectoryExists($comingSoon . '/public');
            File::put($comingSoon . '/public/index.html', $this->comingSoon($site->name));
        }

        $this->swapLiveSymlink($site, $comingSoon);
        $site->update(['live_release' => null, 'maintenance_mode' => false]);
    }

    public function enableMaintenance(Site $site, string $page = 'brb', ?\DateTimeInterface $launchDate = null): void
    {
        if ($page === 'countdown') {
            $target = $site->sitesPath() . '/coming-soon-countdown';
            File::ensureDirectoryExists($target . '/public');
            File::put($target . '/public/index.html', $this->comingSoonCountdown($launchDate));
        } else {
            $this->ensureSharedComingSoon();
            $target = $this->sharedComingSoonPath();
        }

        $this->swapLiveSymlink($site, $target);
        $site->update(['maintenance_mode' => true, 'maintenance_page' => $page, 'launch_date' => $launchDate]);
    }

    public function disableMaintenance(Site $site): void
    {
        $target = $site->live_release
            ? $site->sitesPath() . '/releases/' . $site->live_release
            : $site->sitesPath() . '/drafts';

        $this->swapLiveSymlink($site, $target);
        $site->update(['maintenance_mode' => false]);
    }

    private function swapLiveSymlink(Site $site, string $target): void
    {
        $livePath = $site->sitesPath() . '/live';
        $tmpPath  = $livePath . '_tmp_' . uniqid();

        if (!symlink($target, $tmpPath)) {
            throw new \RuntimeException("symlink() failed: {$tmpPath} → {$target}");
        }

        if (!rename($tmpPath, $livePath)) {
            unlink($tmpPath);
            throw new \RuntimeException("rename() failed: could not swap live symlink at {$livePath}");
        }
    }

    public function delete(Site $site, GitHubService $github): void
    {
        $org = $site->organisation;

        if ($org && $org->github_installation_id) {
            $otherSites = Site::withoutGlobalScopes()
                ->where('organisation_id', $org->id)
                ->where('id', '!=', $site->id)
                ->exists();

            if (!$otherSites) {
                $github->deleteInstallation($org->github_installation_id);
                $org->update(['github_installation_id' => null]);
            }
        }

        File::deleteDirectory($site->sitesPath());
        $site->delete();
    }

    /**
     * Remove junk entries that macOS/Windows zip tools leave behind.
     */
    private function removeZipJunk(string $path): void
    {
        $junk = ['__MACOSX', '.DS_Store', 'Thumbs.db', 'desktop.ini'];

        foreach ($junk as $name) {
            $target = $path . '/' . $name;
            if (File::isDirectory($target)) {
                File::deleteDirectory($target);
            } elseif (File::exists($target)) {
                File::delete($target);
            }
        }
    }

    /**
     * If a directory contains only a single subfolder and no files,
     * hoist everything from that subfolder up one level and remove it.
     * Handles the common case where a zip wraps everything in a root folder.
     */
    private function hoistIfWrappedInSingleFolder(string $path): void
    {
        $this->removeZipJunk($path);

        $dirs = File::directories($path);
        $files = File::files($path);

        if (count($dirs) !== 1 || count($files) !== 0) {
            return;
        }

        $innerDir = $dirs[0];

        // Move all child directories
        foreach (File::directories($innerDir) as $dir) {
            File::moveDirectory($dir, $path . '/' . basename($dir));
        }

        // Move all child files
        foreach (File::files($innerDir) as $file) {
            File::move($file->getPathname(), $path . '/' . $file->getFilename());
        }

        File::deleteDirectory($innerDir);
    }

    private function draftPlaceholder(string $name): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$name}</title>
            <style>
                body { font-family: -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f9fafb; color: #374151; }
                .box { text-align: center; }
                h1 { font-size: 2rem; margin-bottom: 0.5rem; }
                p { color: #6b7280; }
            </style>
        </head>
        <body>
            <div class="box">
                <h1>{$name}</h1>
                <p>Upload your site files to get started.</p>
            </div>
        </body>
        </html>
        HTML;
    }

    private function sharedComingSoonPath(): string
    {
        return config('sites.path') . '/shared/coming-soon';
    }

    private function ensureSharedComingSoon(): void
    {
        $path = $this->sharedComingSoonPath() . '/public';
        if (!is_dir($path)) {
            File::ensureDirectoryExists($path);
            File::put($path . '/index.html', $this->comingSoon());
        }
    }

    private function comingSoonCountdown(?\DateTimeInterface $launchDate): string
    {
        $isoDate = $launchDate ? $launchDate->format('Y-m-d') : '';
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Coming Soon</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Helvetica Neue", sans-serif;
                    display: flex; align-items: center; justify-content: center;
                    min-height: 100vh; background: #000; color: #f5f5f7;
                    -webkit-font-smoothing: antialiased;
                }
                .box { text-align: center; padding: 2rem; max-width: 640px; }
                .label {
                    font-size: 0.85rem;
                    font-weight: 500;
                    color: #6e6e73;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                    margin-bottom: 2rem;
                }
                .countdown {
                    display: flex;
                    gap: 2rem;
                    justify-content: center;
                    margin-bottom: 2rem;
                }
                .unit { display: flex; flex-direction: column; align-items: center; gap: 0.4rem; }
                .num {
                    font-size: clamp(2.5rem, 8vw, 5rem);
                    font-weight: 700;
                    letter-spacing: -0.03em;
                    line-height: 1;
                    background: linear-gradient(135deg, #f5f5f7 0%, #a1a1a6 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    min-width: 2ch;
                    text-align: center;
                }
                .unit-label {
                    font-size: 0.7rem;
                    color: #3d3d3f;
                    letter-spacing: 0.1em;
                    text-transform: uppercase;
                }
                .date-str {
                    font-size: 0.9rem;
                    color: #3d3d3f;
                    letter-spacing: 0.02em;
                }
                #expired { display: none; }
                #expired .brb {
                    font-size: clamp(3.5rem, 10vw, 6rem);
                    font-weight: 700;
                    letter-spacing: -0.03em;
                    background: linear-gradient(135deg, #f5f5f7 0%, #a1a1a6 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                }
            </style>
        </head>
        <body>
            <div class="box">
                <div id="countdown-block">
                    <div class="label">Launching in</div>
                    <div class="countdown">
                        <div class="unit"><span class="num" id="d">--</span><span class="unit-label">days</span></div>
                        <div class="unit"><span class="num" id="h">--</span><span class="unit-label">hours</span></div>
                        <div class="unit"><span class="num" id="m">--</span><span class="unit-label">mins</span></div>
                        <div class="unit"><span class="num" id="s">--</span><span class="unit-label">secs</span></div>
                    </div>
                    <div class="date-str" id="date-str"></div>
                </div>
                <div id="expired">
                    <div class="brb">Be Right Back</div>
                </div>
            </div>
            <script>
                var target = new Date('{$isoDate}T00:00:00');
                var dateStr = target.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                document.getElementById('date-str').textContent = dateStr;

                function tick() {
                    var now = Date.now();
                    var diff = target - now;
                    if (diff <= 0) {
                        document.getElementById('countdown-block').style.display = 'none';
                        document.getElementById('expired').style.display = 'block';
                        return;
                    }
                    var s = Math.floor(diff / 1000);
                    var m = Math.floor(s / 60); s %= 60;
                    var h = Math.floor(m / 60); m %= 60;
                    var d = Math.floor(h / 24); h %= 24;
                    document.getElementById('d').textContent = String(d).padStart(2, '0');
                    document.getElementById('h').textContent = String(h).padStart(2, '0');
                    document.getElementById('m').textContent = String(m).padStart(2, '0');
                    document.getElementById('s').textContent = String(s).padStart(2, '0');
                }
                tick();
                setInterval(tick, 1000);
            </script>
        </body>
        </html>
        HTML;
    }

    private function comingSoon(): string
    {
        return <<<'HTML'
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="refresh" content="30">
            <title>Be Right Back</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Helvetica Neue", sans-serif;
                    display: flex; align-items: center; justify-content: center;
                    min-height: 100vh; background: #000; color: #f5f5f7;
                    -webkit-font-smoothing: antialiased;
                }
                .box { text-align: center; padding: 2rem; max-width: 560px; }
                .brb {
                    font-size: clamp(3.5rem, 10vw, 6rem);
                    font-weight: 700;
                    letter-spacing: -0.03em;
                    line-height: 1;
                    background: linear-gradient(135deg, #f5f5f7 0%, #a1a1a6 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    margin-bottom: 1.25rem;
                }
                .tagline {
                    font-size: 0.9rem;
                    color: #3d3d3f;
                    letter-spacing: 0.02em;
                    text-transform: uppercase;
                }
                .dot { display: inline-block; animation: pulse 2s ease-in-out infinite; }
                .dot:nth-child(2) { animation-delay: 0.3s; }
                .dot:nth-child(3) { animation-delay: 0.6s; }
                @keyframes pulse { 0%, 100% { opacity: 0.2; } 50% { opacity: 1; } }
            </style>
        </head>
        <body>
            <div class="box">
                <div class="brb">Be Right Back</div>
                <div class="tagline">Something exciting is happening<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span></div>
            </div>
        </body>
        </html>
        HTML;
    }
}

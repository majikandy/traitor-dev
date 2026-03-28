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
        File::ensureDirectoryExists($sitePath . '/coming-soon/public');

        // Placeholder shown in the preview before any files are uploaded
        File::put($site->draftsPath() . '/index.html', $this->draftPlaceholder($name));

        // "Coming soon" page served on the real domain until first Go Live
        File::put($sitePath . '/coming-soon/public/index.html', $this->comingSoon($name));

        // Live symlink starts pointing at coming-soon so the preview shows something immediately
        symlink($sitePath . '/coming-soon', $sitePath . '/live');

        // Preview symlink also starts at coming-soon; updated to latest release on each createRelease()
        symlink($sitePath . '/coming-soon', $site->previewSymlinkPath());

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

        $site->update(['current_release' => $nextVersion, 'preview_release' => $nextVersion]);

        // Advance the client preview symlink to the new release automatically
        $this->swapPreviewSymlink($site, $site->sitesPath() . '/releases/' . $nextVersion);

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
            $target = $site->sitesPath() . '/coming-soon';
            if (!is_dir($target . '/public')) {
                File::ensureDirectoryExists($target . '/public');
                File::put($target . '/public/index.html', $this->comingSoon($site->name));
            }
        }

        symlink($target, $livePath);
    }

    public function takeDownPreview(Site $site): void
    {
        $previewPath = $site->previewSymlinkPath();
        if (is_link($previewPath) || file_exists($previewPath)) {
            unlink($previewPath);
        }
    }

    public function restorePreview(Site $site): void
    {
        if (is_link($site->previewSymlinkPath()) || file_exists($site->previewSymlinkPath())) {
            return;
        }

        $target = $site->preview_release
            ? $site->sitesPath() . '/releases/' . $site->preview_release
            : ($site->current_release
                ? $site->sitesPath() . '/releases/' . $site->current_release
                : $site->sitesPath() . '/coming-soon');

        symlink($target, $site->previewSymlinkPath());
    }

    public function setPreview(Site $site, int $version): void
    {
        $releasePath = $site->sitesPath() . '/releases/' . $version;

        if (!is_dir($releasePath)) {
            throw new \RuntimeException("Release {$version} not found at {$releasePath}.");
        }

        $this->swapPreviewSymlink($site, $releasePath);
        $site->update(['preview_release' => $version]);
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
        $this->swapPreviewSymlink($site, $comingSoon);
        $site->update(['live_release' => null, 'preview_release' => null, 'maintenance_mode' => false]);
    }

    public function enableMaintenance(Site $site): void
    {
        $comingSoon = $site->sitesPath() . '/coming-soon';
        if (!is_dir($comingSoon . '/public')) {
            File::ensureDirectoryExists($comingSoon . '/public');
            File::put($comingSoon . '/public/index.html', $this->comingSoon($site->name));
        }

        $this->swapLiveSymlink($site, $comingSoon);
        $site->update(['maintenance_mode' => true]);
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
        symlink($target, $tmpPath);
        rename($tmpPath, $livePath);
    }

    private function swapPreviewSymlink(Site $site, string $target): void
    {
        $previewPath = $site->previewSymlinkPath();
        $tmpPath     = $previewPath . '_tmp_' . uniqid();
        symlink($target, $tmpPath);
        rename($tmpPath, $previewPath);
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

    private function comingSoon(string $name): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="refresh" content="30">
            <title>{$name} — Coming Soon</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                    display: flex; align-items: center; justify-content: center;
                    min-height: 100vh; background: #0f172a; color: #e2e8f0;
                }
                .box { text-align: center; padding: 2rem; max-width: 480px; }
                .label {
                    display: inline-block; font-size: 0.75rem; font-weight: 600;
                    letter-spacing: 0.1em; text-transform: uppercase;
                    color: #6366f1; background: rgba(99,102,241,0.15);
                    padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 1.5rem;
                }
                h1 { font-size: 2.5rem; font-weight: 700; line-height: 1.2; margin-bottom: 1rem; color: #f8fafc; }
                p { font-size: 1.1rem; color: #94a3b8; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="box">
                <span class="label">Coming Soon</span>
                <h1>{$name}</h1>
                <p>Something great is on its way. Check back soon.</p>
            </div>
        </body>
        </html>
        HTML;
    }
}

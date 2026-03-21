<?php

namespace App\Services;

use App\Models\Release;
use App\Models\Site;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;

class SiteService
{
    public function create(string $name, string $slug): Site
    {
        $site = Site::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        $sitePath = $site->sitesPath();
        File::ensureDirectoryExists($site->draftsPath());
        File::ensureDirectoryExists($sitePath . '/releases');

        // Drop a placeholder index so the preview works immediately
        File::put($site->draftsPath() . '/index.html', $this->placeholder($name));

        return $site;
    }

    public function upload(Site $site, UploadedFile $file): void
    {
        $zip = new ZipArchive();
        $tempPath = $file->getPathname();

        if ($zip->open($tempPath) !== true) {
            throw new \RuntimeException('Could not open zip file.');
        }

        $draftsPath = $site->draftsPath();

        // Clear existing drafts/public and extract fresh
        File::cleanDirectory($draftsPath);
        $zip->extractTo($draftsPath);
        $zip->close();

        // If the zip contained a single root directory, hoist its contents up
        $items = File::directories($draftsPath);
        $files = File::files($draftsPath);

        if (count($items) === 1 && count($files) === 0) {
            $innerDir = $items[0];
            foreach (File::allFiles($innerDir) as $f) {
                $relative = str_replace($innerDir . '/', '', $f->getPathname());
                $dest = $draftsPath . '/' . $relative;
                File::ensureDirectoryExists(dirname($dest));
                File::move($f->getPathname(), $dest);
            }
            File::deleteDirectory($innerDir);
        }
    }

    public function publish(Site $site, ?string $notes = null): Release
    {
        $nextVersion = $site->current_release + 1;
        $releasePath = $site->releasePath($nextVersion);

        // Copy drafts to new release
        File::copyDirectory($site->draftsPath(), $releasePath);

        // Swap the symlink atomically
        $livePath = $site->livePath();
        $tempLink = $livePath . '.tmp';

        // Point temp symlink at new release's public dir
        $target = $site->sitesPath() . '/releases/' . $nextVersion . '/public';
        symlink($target, $tempLink);
        rename($tempLink, $livePath);

        // Record the release
        $release = Release::create([
            'site_id' => $site->id,
            'version' => $nextVersion,
            'notes' => $notes,
            'created_at' => now(),
        ]);

        $site->update([
            'current_release' => $nextVersion,
            'status' => 'live',
        ]);

        return $release;
    }

    public function rollback(Site $site, ?int $version = null): Release
    {
        $target = $version
            ? $site->releases()->where('version', $version)->firstOrFail()
            : $site->releases()->where('version', '<', $site->current_release)->firstOrFail();

        $livePath = $site->livePath();
        $tempLink = $livePath . '.tmp';
        $relTarget = $site->sitesPath() . '/releases/' . $target->version . '/public';

        symlink($relTarget, $tempLink);
        rename($tempLink, $livePath);

        $site->update(['current_release' => $target->version]);

        return $target;
    }

    public function delete(Site $site): void
    {
        File::deleteDirectory($site->sitesPath());
        $site->delete();
    }

    private function placeholder(string $name): string
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
}

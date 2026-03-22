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

        $this->hoistIfWrappedInSingleFolder($draftsPath);
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

        $site->update(['current_release' => $nextVersion]);

        return $release;
    }

    public function delete(Site $site): void
    {
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

<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Site;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    public function __invoke(string $token, string $path = 'index.php'): Response
    {
        [$rootPath, $label] = $this->resolve($token);

        // Resolve the root (must exist), then build candidate path without symlink/.. resolution yet
        $resolvedRoot = realpath($rootPath);
        if ($resolvedRoot === false) {
            abort(404);
        }

        $candidate = $resolvedRoot . '/' . ltrim($path, '/');

        // Try candidate as-is, then with .html fallback if .php was requested
        $filePath = $this->findFile($candidate);

        if ($filePath === null) {
            abort(404);
        }

        // Guard against directory traversal after resolving symlinks
        if (!str_starts_with($filePath, $resolvedRoot)) {
            abort(404);
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'php') {
            // chdir to the file's directory so relative require/include paths inside the
            // site's PHP files resolve correctly (PHP uses CWD, not the included file's dir).
            $origDir = getcwd();
            chdir(dirname($filePath));
            ob_start();
            include $filePath;
            $html = ob_get_clean();
            chdir($origDir);
            return response($this->injectPreview($html, $token, $label, $path))->header('Content-Type', 'text/html');
        }

        if ($ext === 'html' || $ext === 'htm') {
            $html = file_get_contents($filePath);
            return response($this->injectPreview($html, $token, $label, $path))->header('Content-Type', 'text/html');
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', $this->mimeType($filePath));

        return $response;
    }

    private function injectPreview(string $html, string $token, string $label, string $path = 'index.php'): string
    {
        $previewBase = '/preview/' . $token . '/';

        // Base for resolving relative links — includes the page's subdirectory if any.
        // e.g. path="sightings/index.php" → dirBase="/preview/{token}/sightings/"
        $dir = dirname($path);
        $dirBase = ($dir === '.' || $dir === '')
            ? $previewBase
            : $previewBase . trim($dir, '/') . '/';

        // Rewrite root-relative URLs: href="/foo" → href="/preview/{token}/foo"
        $html = preg_replace_callback(
            '/((?:href|src|action)=["\'])(\\/(?!\\/)[^"\']*?)(["\'])/i',
            fn($m) => $m[1] . $previewBase . ltrim($m[2], '/') . $m[3],
            $html
        );

        // Rewrite relative URLs using directory context.
        // Skips: anchors (#), scheme URIs (mailto:, https:, etc.), query-only (?foo), root-relative (/).
        // ./foo links use previewBase (they are site-root-relative from the $root PHP variable).
        // Plain relative links (submit.php) use dirBase so they stay in their subdirectory.
        $html = preg_replace_callback(
            '/((?:href|src|action)=["\'])([^"\'#\/][^"\']*?)(["\'])/i',
            function ($m) use ($previewBase, $dirBase) {
                if ($m[2][0] === '?') {
                    return $m[0]; // query-string-only: browser resolves relative to current URL
                }
                if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $m[2])) {
                    return $m[0]; // leave mailto:, tel:, https:, javascript:, data: alone
                }
                if (str_starts_with($m[2], './') || $m[2] === '.') {
                    return $m[1] . $previewBase . $m[2] . $m[3]; // ./foo: site-root-relative
                }
                return $m[1] . $dirBase . $m[2] . $m[3]; // plain relative: directory-relative
            },
            $html
        );

        $banner = $this->banner($label);

        $html = str_contains($html, '</body>')
            ? str_replace('</body>', $banner . '</body>', $html)
            : $html . $banner;

        return $html;
    }

    private function findFile(string $candidate): ?string
    {
        if (is_dir($candidate)) {
            foreach (['index.php', 'index.html', 'index.htm'] as $index) {
                if (file_exists($candidate . '/' . $index)) {
                    return realpath($candidate . '/' . $index) ?: null;
                }
            }
            return null;
        }

        if (file_exists($candidate)) {
            return realpath($candidate) ?: null;
        }

        // If .php was requested but doesn't exist, try the .html equivalent
        if (str_ends_with($candidate, '.php')) {
            $html = substr($candidate, 0, -4) . '.html';
            if (file_exists($html)) {
                return realpath($html) ?: null;
            }
        }

        return null;
    }

    /** @return array{string, string} */
    private function resolve(string $token): array
    {
        $site = Site::where('preview_token', $token)->first();
        if ($site) {
            $label = $site->maintenance_mode ? $site->name . ' — Maintenance mode' : $site->name . ' (live)';
            return [$site->livePath() . '/public', $label];
        }

        $release = Release::where('preview_token', $token)->first();
        if ($release) {
            $site = $release->site;
            return [$site->releasePath($release->version), $site->name . ' v' . $release->version];
        }

        abort(404);
    }

    private function banner(string $label): string
    {
        $escaped = e($label);

        return <<<HTML
        <div style="position:fixed;bottom:0;left:0;right:0;background:#1f2937;color:#fff;text-align:center;padding:8px;font-size:13px;font-family:sans-serif;z-index:9999">
            Preview: {$escaped}
        </div>
        HTML;
    }

    private function mimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'html', 'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'pdf' => 'application/pdf',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };
    }
}

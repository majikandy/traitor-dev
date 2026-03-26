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
            ob_start();
            include $filePath;
            $html = ob_get_clean();
            return response($this->injectPreview($html, $token, $label))->header('Content-Type', 'text/html');
        }

        if ($ext === 'html' || $ext === 'htm') {
            $html = file_get_contents($filePath);
            return response($this->injectPreview($html, $token, $label))->header('Content-Type', 'text/html');
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', $this->mimeType($filePath));

        return $response;
    }

    private function injectPreview(string $html, string $token, string $label): string
    {
        $previewBase = '/preview/' . $token . '/';

        // Rewrite root-relative URLs (href="/...", src="/...") to go through the preview route.
        // Skips protocol-relative (//), absolute (https://), anchors (#), and data: URIs.
        $html = preg_replace_callback(
            '/((?:href|src|action)=")(\\/(?!\\/)[^"]*?)(")/i',
            fn($m) => $m[1] . $previewBase . ltrim($m[2], '/') . $m[3],
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
            return [$site->draftsPath(), $site->name . ' (draft)'];
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

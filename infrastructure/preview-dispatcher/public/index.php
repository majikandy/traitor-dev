<?php
/**
 * Wildcard preview dispatcher.
 *
 * Every {slug}.preview.traitor.dev request lands here.
 * The slug maps to /home/traitor8921/sites/{slug}/preview/public.
 */

define('PREVIEW_BASE_DOMAIN', 'preview.traitor.dev');
define('SITES_PATH', '/home/traitor8921/sites');

// Normalize host: lowercase, strip port
$host = strtolower(preg_replace('/:\d+$/', '', trim($_SERVER['HTTP_HOST'] ?? '')));

// Extract slug from host
if ($host === PREVIEW_BASE_DOMAIN) {
    http_response_code(403);
    exit('Root domain not allowed');
}

if (!str_ends_with($host, '.' . PREVIEW_BASE_DOMAIN)) {
    http_response_code(403);
    exit('Not allowed');
}

$slug = substr($host, 0, -strlen('.' . PREVIEW_BASE_DOMAIN));

// Reject nested subdomains (e.g. foo.bar.preview.traitor.dev)
if (str_contains($slug, '.')) {
    http_response_code(403);
    exit('Nested subdomains not allowed');
}

// Slug must be lowercase alphanumeric + dashes only
if (!preg_match('/^[a-z0-9][a-z0-9\-]*$/', $slug)) {
    http_response_code(404);
    exit('Not found');
}

$docroot     = SITES_PATH . '/' . $slug . '/preview/public';
$realDocroot = realpath($docroot);

if ($realDocroot === false || !is_dir($realDocroot)) {
    http_response_code(404);
    exit('Site not found');
}

// Resolve path and block traversal
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri      = '/' . ltrim($uri, '/');
$realFile = realpath($realDocroot . $uri);

if ($realFile === false || (strpos($realFile, $realDocroot . '/') !== 0 && $realFile !== $realDocroot)) {
    http_response_code(403);
    exit('Forbidden');
}

// Resolve directory to index file
if (is_dir($realFile)) {
    foreach (['index.php', 'index.html'] as $index) {
        $candidate = rtrim($realFile, '/') . '/' . $index;
        if (file_exists($candidate)) {
            $realFile = $candidate;
            break;
        }
    }
}

if (is_dir($realFile) || !file_exists($realFile)) {
    http_response_code(404);
    exit('Not found');
}

$ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));

if ($ext === 'php') {
    // chdir so relative filesystem calls inside the site file work.
    // __DIR__ inside the included file still resolves to its own location,
    // so include __DIR__ . '/../includes/header.php' patterns work correctly.
    chdir(dirname($realFile));
    $_SERVER['DOCUMENT_ROOT']   = $realDocroot;
    $_SERVER['SCRIPT_FILENAME'] = $realFile;
    include $realFile;
    exit;
}

$mimes = [
    'html'  => 'text/html; charset=utf-8',
    'htm'   => 'text/html; charset=utf-8',
    'css'   => 'text/css',
    'js'    => 'application/javascript',
    'json'  => 'application/json',
    'xml'   => 'application/xml',
    'svg'   => 'image/svg+xml',
    'png'   => 'image/png',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'gif'   => 'image/gif',
    'webp'  => 'image/webp',
    'ico'   => 'image/x-icon',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'pdf'   => 'application/pdf',
    'txt'   => 'text/plain',
];

header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($realFile));
readfile($realFile);

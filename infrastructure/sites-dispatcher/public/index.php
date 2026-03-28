<?php
/**
 * Wildcard staging dispatcher.
 *
 * Every {slug}.sites.traitor.dev request lands here.
 * Serves from sites/{slug}/live/public — identical to what a real attached domain serves.
 * Maintenance mode, coming soon, live release — all reflected accurately.
 */

define('SITES_BASE_DOMAIN', 'sites.traitor.dev');
define('SITES_PATH', '/home/traitor8921/sites');

// Normalize host: lowercase, strip port
$host = strtolower(preg_replace('/:\d+$/', '', trim($_SERVER['HTTP_HOST'] ?? '')));

if ($host === SITES_BASE_DOMAIN) {
    http_response_code(403);
    exit('Root domain not allowed');
}

if (!str_ends_with($host, '.' . SITES_BASE_DOMAIN)) {
    http_response_code(403);
    exit('Not allowed');
}

$slug = substr($host, 0, -strlen('.' . SITES_BASE_DOMAIN));

if (str_contains($slug, '.')) {
    http_response_code(403);
    exit('Nested subdomains not allowed');
}

if (!preg_match('/^[a-z0-9][a-z0-9\-]*$/', $slug)) {
    http_response_code(404);
    exit('Not found');
}

$docroot     = SITES_PATH . '/' . $slug . '/live/public';
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

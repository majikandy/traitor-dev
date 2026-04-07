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

// Detect versioned slug: awesome-jawsome-v3 → site=awesome-jawsome, release=3
if (preg_match('/^(.+)-v(\d+)$/', $slug, $m)) {
    $siteSlug    = $m[1];
    $releaseDir  = SITES_PATH . '/' . $siteSlug . '/releases/' . $m[2];
    $tokenFile   = $releaseDir . '/.preview-token';

    $badToken = function() {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        exit('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access denied</title>
        <style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f9fafb;color:#374151}
        .box{text-align:center;max-width:420px;padding:2rem}h1{font-size:1.5rem;font-weight:700;margin-bottom:.5rem}p{color:#6b7280}</style></head>
        <body><div class="box"><h1>Access denied</h1><p>This link is invalid or has been revoked.</p></div></body></html>');
    };

    if (!file_exists($tokenFile)) {
        $badToken();
    }

    $storedToken = trim(file_get_contents($tokenFile));
    $cookieName  = 'pt_' . md5($host);

    // Token in URL — validate, set cookie, redirect to clean URL
    if (isset($_GET['token'])) {
        if (!hash_equals($storedToken, $_GET['token'])) {
            $badToken();
        }
        setcookie($cookieName, $storedToken, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?') ?: '/';
        header('Location: ' . $cleanUrl);
        exit;
    }

    // Cookie present — validate
    if (!isset($_COOKIE[$cookieName]) || !hash_equals($storedToken, $_COOKIE[$cookieName])) {
        $badToken();
    }

    $docroot = $releaseDir . '/public';
} else {
    $docroot = SITES_PATH . '/' . $slug . '/preview/public';
}

$realDocroot = realpath($docroot);

if ($realDocroot === false || !is_dir($realDocroot)) {
    http_response_code(404);
    exit('Site not found');
}

// Resolve path and block traversal
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri      = '/' . ltrim($uri, '/');
$realFile = realpath($realDocroot . $uri);

// File not found on disk — fall back to index.php (supports Laravel / front-controller apps)
if ($realFile === false) {
    $indexPhp = $realDocroot . '/index.php';
    if (file_exists($indexPhp)) {
        chdir($realDocroot);
        $_SERVER['DOCUMENT_ROOT']   = $realDocroot;
        $_SERVER['SCRIPT_FILENAME'] = $indexPhp;
        include $indexPhp;
        exit;
    }
    http_response_code(404);
    exit('Not found');
}

if (strpos($realFile, $realDocroot . '/') !== 0 && $realFile !== $realDocroot) {
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

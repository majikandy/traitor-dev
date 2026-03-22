<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
if ($currentPage === 'index.php') $currentPage = '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? $config['meta_description']) ?>">
    <title><?= htmlspecialchars(($pageTitle ?? $config['site_name']) . ($currentPage !== '/' ? ' | ' . $config['site_name'] : '')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="site-logo">
                <span class="logo-icon">&#x1F35E;</span>
                <span class="logo-text"><?= htmlspecialchars($config['site_name']) ?></span>
            </a>
            <nav class="site-nav">
                <button class="nav-toggle" aria-label="Toggle navigation">&#9776;</button>
                <ul class="nav-list">
                    <?php foreach ($config['nav'] as $url => $label): ?>
                        <li>
                            <a href="<?= $url ?>"<?= ($currentPage === ltrim($url, '/') || $currentPage === $url) ? ' class="active"' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="site-main">

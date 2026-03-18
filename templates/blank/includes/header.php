<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
if ($currentPage === 'index.php') $currentPage = '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($config['meta_description']) ?>">
    <title><?= htmlspecialchars($config['site_name']) ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="site-logo"><?= htmlspecialchars($config['site_name']) ?></a>
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
        <div class="container">

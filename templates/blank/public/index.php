<?php $config = require __DIR__ . '/../includes/config.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="hero">
    <h1>Welcome to <?= htmlspecialchars($config['site_name']) ?></h1>
    <p><?= htmlspecialchars($config['meta_description']) ?></p>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

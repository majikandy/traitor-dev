<?php $config = require __DIR__ . '/../includes/config.php'; ?>
<?php $recipes = require __DIR__ . '/../includes/recipes.php'; ?>
<?php
$activeCategory = $_GET['cat'] ?? null;
$pageTitle = $activeCategory && isset($config['categories'][$activeCategory])
    ? $config['categories'][$activeCategory] . ' Recipes'
    : 'All Recipes';
$pageDescription = 'Browse our collection of creative toast recipes — from sweet to savoury, quick bites to brunch showstoppers.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p class="page-intro">Every great meal starts with great toast. Pick your vibe and get cooking.</p>
    </div>
</section>

<section class="recipe-filters">
    <div class="container">
        <div class="filter-bar">
            <a href="/recipes.php" class="filter-chip <?= !$activeCategory ? 'active' : '' ?>">All</a>
            <?php foreach ($config['categories'] as $slug => $name): ?>
                <a href="/recipes.php?cat=<?= $slug ?>" class="filter-chip <?= $activeCategory === $slug ? 'active' : '' ?>">
                    <?= htmlspecialchars($name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="recipes-listing">
    <div class="container">
        <div class="recipe-grid">
            <?php
            $emojis = ['smashed-avo-chilli' => '&#x1F951;', 'ricotta-honey-fig' => '&#x1F95B;', 'peanut-butter-banana' => '&#x1F95C;', 'garlic-mushroom-thyme' => '&#x1F344;', 'burrata-tomato-basil' => '&#x1F345;', 'cinnamon-french-toast' => '&#x1F36F;'];
            foreach ($recipes as $slug => $recipe):
                if ($activeCategory && $recipe['category'] !== $activeCategory) continue;
            ?>
                <a href="/recipes/<?= $slug ?>.php" class="recipe-card">
                    <div class="recipe-card-image" style="background-color: <?= $recipe['hero_color'] ?>">
                        <span class="recipe-card-emoji"><?= $emojis[$slug] ?? '&#x1F35E;' ?></span>
                    </div>
                    <div class="recipe-card-body">
                        <span class="recipe-card-category"><?= htmlspecialchars($config['categories'][$recipe['category']]) ?></span>
                        <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                        <p><?= htmlspecialchars(mb_strimwidth($recipe['description'], 0, 120, '...')) ?></p>
                        <div class="recipe-card-meta">
                            <span><?= $recipe['time'] ?></span>
                            <span><?= $recipe['difficulty'] ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

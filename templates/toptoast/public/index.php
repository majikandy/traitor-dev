<?php $config = require __DIR__ . '/../includes/config.php'; ?>
<?php $recipes = require __DIR__ . '/../includes/recipes.php'; ?>
<?php $pageTitle = 'Top Toast — Toast. Elevated.'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="hero">
    <div class="container">
        <p class="hero-tag">Toast. Elevated.</p>
        <h1>Recipes that make toast<br>the <span class="highlight">main character</span></h1>
        <p class="hero-sub">Creative, delicious toast recipes for people who believe breakfast should never be boring.</p>
        <a href="/recipes.php" class="btn btn-primary">Browse Recipes</a>
    </div>
</section>

<section class="featured-section">
    <div class="container">
        <div class="section-header">
            <h2>Latest Recipes</h2>
            <a href="/recipes.php" class="view-all">View all &rarr;</a>
        </div>
        <div class="recipe-grid">
            <?php
            $featured = array_slice($recipes, 0, 3, true);
            foreach ($featured as $slug => $recipe): ?>
                <a href="/recipes/<?= $slug ?>.php" class="recipe-card">
                    <div class="recipe-card-image" style="background-color: <?= $recipe['hero_color'] ?>">
                        <span class="recipe-card-emoji"><?php
                            $emojis = ['smashed-avo-chilli' => '&#x1F951;', 'ricotta-honey-fig' => '&#x1F95B;', 'peanut-butter-banana' => '&#x1F95C;', 'garlic-mushroom-thyme' => '&#x1F344;', 'burrata-tomato-basil' => '&#x1F345;', 'cinnamon-french-toast' => '&#x1F36F;'];
                            echo $emojis[$slug] ?? '&#x1F35E;';
                        ?></span>
                    </div>
                    <div class="recipe-card-body">
                        <span class="recipe-card-category"><?= htmlspecialchars($config['categories'][$recipe['category']]) ?></span>
                        <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                        <p><?= htmlspecialchars(mb_strimwidth($recipe['description'], 0, 100, '...')) ?></p>
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

<section class="categories-section">
    <div class="container">
        <h2>Browse by Vibe</h2>
        <div class="category-grid">
            <?php
            $catEmojis = ['sweet' => '&#x1F36F;', 'savoury' => '&#x1F9C0;', 'brunch' => '&#x1F373;', 'quick' => '&#x26A1;'];
            $catDescriptions = ['sweet' => 'Honey, fruit, chocolate — the sweet side of toast.', 'savoury' => 'Cheese, mushrooms, eggs — proper meals on bread.', 'brunch' => 'Impress your mates without leaving the kitchen.', 'quick' => 'From toaster to table in under 5 minutes.'];
            foreach ($config['categories'] as $slug => $name): ?>
                <a href="/recipes.php?cat=<?= $slug ?>" class="category-card">
                    <span class="category-emoji"><?= $catEmojis[$slug] ?></span>
                    <h3><?= htmlspecialchars($name) ?></h3>
                    <p><?= $catDescriptions[$slug] ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-box">
            <h2>More recipes landing soon</h2>
            <p>We're cooking up new toast creations every week. Keep checking back for fresh inspiration.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

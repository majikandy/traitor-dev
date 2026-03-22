<?php
$emojis = ['smashed-avo-chilli' => '&#x1F951;', 'ricotta-honey-fig' => '&#x1F95B;', 'peanut-butter-banana' => '&#x1F95C;', 'garlic-mushroom-thyme' => '&#x1F344;', 'burrata-tomato-basil' => '&#x1F345;', 'cinnamon-french-toast' => '&#x1F36F;'];
$emoji = $emojis[$recipe['slug']] ?? '&#x1F35E;';
?>

<article class="recipe-page">
    <section class="recipe-hero" style="background-color: <?= $recipe['hero_color'] ?>">
        <div class="container">
            <a href="/recipes.php" class="recipe-back">&larr; All Recipes</a>
            <span class="recipe-hero-emoji"><?= $emoji ?></span>
            <span class="recipe-category-badge"><?= htmlspecialchars($config['categories'][$recipe['category']]) ?></span>
            <h1><?= htmlspecialchars($recipe['title']) ?></h1>
            <p class="recipe-hero-desc"><?= htmlspecialchars($recipe['description']) ?></p>
            <div class="recipe-hero-meta">
                <span class="meta-item">&#x23F1; <?= $recipe['time'] ?></span>
                <span class="meta-item">&#x1F4AA; <?= $recipe['difficulty'] ?></span>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="recipe-content">
            <aside class="recipe-ingredients">
                <h2>Ingredients</h2>
                <ul>
                    <?php foreach ($recipe['ingredients'] as $ingredient): ?>
                        <li>
                            <label class="ingredient-check">
                                <input type="checkbox">
                                <span><?= htmlspecialchars($ingredient) ?></span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="recipe-method">
                <h2>Method</h2>
                <ol class="method-steps">
                    <?php foreach ($recipe['steps'] as $i => $step): ?>
                        <li>
                            <span class="step-num"><?= $i + 1 ?></span>
                            <p><?= htmlspecialchars($step) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <?php if (!empty($recipe['tip'])): ?>
                    <div class="recipe-tip">
                        <span class="tip-icon">&#x1F4A1;</span>
                        <div>
                            <strong>Top Tip</strong>
                            <p><?= htmlspecialchars($recipe['tip']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="recipe-nav-bottom">
        <div class="container">
            <h2>More recipes to try</h2>
            <div class="recipe-grid recipe-grid-small">
                <?php
                $others = array_diff_key($recipes, [$recipe['slug'] => true]);
                $suggestions = array_slice($others, 0, 3, true);
                foreach ($suggestions as $slug => $r): ?>
                    <a href="/recipes/<?= $slug ?>.php" class="recipe-card">
                        <div class="recipe-card-image" style="background-color: <?= $r['hero_color'] ?>">
                            <span class="recipe-card-emoji"><?= $emojis[$slug] ?? '&#x1F35E;' ?></span>
                        </div>
                        <div class="recipe-card-body">
                            <span class="recipe-card-category"><?= htmlspecialchars($config['categories'][$r['category']]) ?></span>
                            <h3><?= htmlspecialchars($r['title']) ?></h3>
                            <div class="recipe-card-meta">
                                <span><?= $r['time'] ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</article>

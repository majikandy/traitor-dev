<?php $config = require __DIR__ . '/../includes/config.php'; ?>
<?php $pageTitle = 'About Top Toast'; ?>
<?php $pageDescription = 'The story behind Top Toast — why we started a whole website dedicated to toast recipes.'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="page-header">
    <div class="container">
        <h1>About Top Toast</h1>
        <p class="page-intro">Yes, it's a whole website about toast. And honestly? It's about time.</p>
    </div>
</section>

<section class="about-content">
    <div class="container">
        <div class="about-block">
            <h2>The Origin Story</h2>
            <p>It started with a simple observation: toast is the most underrated meal format in existence. Everyone makes it, nobody talks about it, and most people are eating it wrong.</p>
            <p>We'd been making toast recipes for years — first as an iPhone app, then as a subscription box sending out curated toppings and artisan breads. Along the way we learned something important: people are <em>really</em> into toast when you show them what's possible.</p>
        </div>

        <div class="about-block">
            <h2>The Mission</h2>
            <p>Top Toast exists to prove that the simplest food can be the most exciting. We're not trying to reinvent cooking — we're trying to make breakfast the best part of your day.</p>
            <p>Every recipe on here is tested, tasted, and actually delicious. No filler recipes, no "toast with butter" padding the numbers. Just genuinely great things to put on bread.</p>
        </div>

        <div class="about-block">
            <h2>The Rules</h2>
            <div class="rules-list">
                <div class="rule">
                    <span class="rule-number">01</span>
                    <div>
                        <h3>Toast must be toasted properly</h3>
                        <p>Golden brown, with a crunch that you can hear. Pale and floppy is warm bread, not toast.</p>
                    </div>
                </div>
                <div class="rule">
                    <span class="rule-number">02</span>
                    <div>
                        <h3>Good bread makes good toast</h3>
                        <p>You can't polish a bad loaf. Start with proper bread — sourdough, ciabatta, brioche, whatever suits the recipe.</p>
                    </div>
                </div>
                <div class="rule">
                    <span class="rule-number">03</span>
                    <div>
                        <h3>Toppings should be generous</h3>
                        <p>A thin scrape of avocado is a garnish, not a meal. Load it up. This isn't a diet website.</p>
                    </div>
                </div>
                <div class="rule">
                    <span class="rule-number">04</span>
                    <div>
                        <h3>Salt is mandatory</h3>
                        <p>Flaky sea salt on everything. Sweet toast, savoury toast, all toast. It makes everything better.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-cta">
            <p>Enough reading. Go make some toast.</p>
            <a href="/recipes.php" class="btn btn-primary">Browse Recipes</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

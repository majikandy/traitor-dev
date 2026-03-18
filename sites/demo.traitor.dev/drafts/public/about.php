<?php $config = require __DIR__ . '/../includes/config.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h1>How Traitor.dev Works</h1>

<section class="about-section">
    <h2>The Problem</h2>
    <p>Most small business websites are over-engineered. WordPress with 30 plugins, page builders that generate 2MB of CSS, databases that get hacked because nobody updates them. All for a site that's basically 5 pages of text and a contact form.</p>
</section>

<section class="about-section">
    <h2>Our Approach</h2>
    <p>Your site is plain HTML, CSS, and a tiny bit of PHP for shared headers and footers. No database. No login page to hack. No plugins to update. It just works.</p>

    <div class="step-list">
        <div class="step">
            <span class="step-number">1</span>
            <div>
                <h3>Tell Us What You Need</h3>
                <p>Describe your business and what you want your site to look like. Our AI builds it from scratch.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-number">2</span>
            <div>
                <h3>Preview Before Going Live</h3>
                <p>Every site gets a free preview URL at <code>yoursite.sites.traitor.dev</code>. Check everything looks right before pointing your domain.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-number">3</span>
            <div>
                <h3>Point Your Domain</h3>
                <p>Add a simple DNS record and your site is live. We handle SSL certificates automatically.</p>
            </div>
        </div>
        <div class="step">
            <span class="step-number">4</span>
            <div>
                <h3>Request Changes Anytime</h3>
                <p>Need something updated? Just ask. Our AI edits your site, you approve, and it's live — with instant rollback if needed.</p>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <h2>What You Get</h2>
    <ul class="feature-list">
        <li>Blazing fast page loads (no database, no framework overhead)</li>
        <li>Free preview URL before you connect your domain</li>
        <li>Free SSL certificate (auto-provisioned via Let's Encrypt)</li>
        <li>Version history with instant rollback</li>
        <li>Mobile-responsive design out of the box</li>
        <li>AI-powered updates — describe what you want, we make it happen</li>
    </ul>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

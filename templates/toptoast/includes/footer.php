    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <span class="logo-icon">&#x1F35E;</span>
                    <span class="logo-text"><?= htmlspecialchars($config['site_name']) ?></span>
                    <p>Making toast the main character since forever.</p>
                </div>
                <div class="footer-links">
                    <h4>Recipes</h4>
                    <ul>
                        <?php foreach ($config['categories'] as $slug => $name): ?>
                            <li><a href="/recipes.php?cat=<?= $slug ?>"><?= htmlspecialchars($name) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Top Toast</h4>
                    <ul>
                        <li><a href="/about.php">About</a></li>
                        <li><a href="mailto:<?= htmlspecialchars($config['contact_email']) ?>">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($config['site_name']) ?>. All rights reserved. Made with bread &amp; butter.</p>
            </div>
        </div>
    </footer>
    <script src="/js/main.js"></script>
</body>
</html>

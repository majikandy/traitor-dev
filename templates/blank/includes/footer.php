        </div>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($config['site_name']) ?>. All rights reserved.</p>
            <?php if (!empty($config['contact_email'])): ?>
                <p><a href="mailto:<?= htmlspecialchars($config['contact_email']) ?>"><?= htmlspecialchars($config['contact_email']) ?></a></p>
            <?php endif; ?>
        </div>
    </footer>
    <script src="/js/main.js"></script>
</body>
</html>

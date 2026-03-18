<?php $config = require __DIR__ . '/../includes/config.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h1>Get In Touch</h1>
<p>Want a site built? Got a question? Drop us a message.</p>

<?php if (!empty($config['contact_email'])): ?>
    <p>Or email us directly at <a href="mailto:<?= htmlspecialchars($config['contact_email']) ?>"><?= htmlspecialchars($config['contact_email']) ?></a></p>
<?php endif; ?>

<form method="post" class="contact-form">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="5" required placeholder="Tell us about your business and what you need..."></textarea>
    </div>
    <button type="submit">Send Message</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>

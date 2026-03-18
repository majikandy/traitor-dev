<?php
/**
 * DNS Instructions Template
 *
 * Rendered by the portal when a user adds a custom domain to their site.
 * Variables available:
 *   $domain       - The custom domain (e.g. "bobtheleicesterbuilder.com")
 *   $server_ip    - The server's public IP address
 *   $preview_url  - The current preview URL (e.g. "bobtheleicesterbuilder-com.sites.traitor.dev")
 *   $dns_verified - Whether DNS is currently pointing correctly (bool)
 */
?>

<div class="dns-instructions">
    <h2>Connect Your Domain</h2>

    <?php if ($dns_verified): ?>
        <div class="dns-status dns-status-ok">
            <strong>DNS is configured correctly.</strong> Your site is live at
            <a href="https://<?= htmlspecialchars($domain) ?>"><?= htmlspecialchars($domain) ?></a>
        </div>
    <?php else: ?>
        <div class="dns-status dns-status-pending">
            <strong>DNS not yet pointing to us.</strong> Follow the steps below to connect your domain.
            Your site is available in the meantime at
            <a href="http://<?= htmlspecialchars($preview_url) ?>"><?= htmlspecialchars($preview_url) ?></a>
        </div>
    <?php endif; ?>

    <h3>Step 1: Add DNS Records</h3>
    <p>Log in to your domain registrar (GoDaddy, Namecheap, Cloudflare, etc.) and set these DNS records:</p>

    <table class="dns-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Name / Host</th>
                <th>Value / Points To</th>
                <th>TTL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>A</code></td>
                <td><code>@</code></td>
                <td><code><?= htmlspecialchars($server_ip) ?></code></td>
                <td>300</td>
            </tr>
            <tr>
                <td><code>A</code></td>
                <td><code>www</code></td>
                <td><code><?= htmlspecialchars($server_ip) ?></code></td>
                <td>300</td>
            </tr>
        </tbody>
    </table>

    <div class="dns-note">
        <strong>Note:</strong> Some registrars use <code>@</code> for the root domain, others want you to leave the Name field blank. Both mean the same thing — the root domain (<code><?= htmlspecialchars($domain) ?></code>).
    </div>

    <h3>Step 2: Wait for Propagation</h3>
    <p>DNS changes can take anywhere from a few minutes to 48 hours to propagate, though most update within 5-30 minutes. We'll check automatically and notify you when it's ready.</p>

    <h3>Step 3: SSL Certificate (Automatic)</h3>
    <p>Once DNS is pointing to us, we'll automatically provision a free SSL certificate via Let's Encrypt. Your site will be served over HTTPS with no action needed from you.</p>

    <h3>Check DNS Status</h3>
    <p>You can verify your DNS is correct by running this command in your terminal:</p>
    <pre><code>dig +short <?= htmlspecialchars($domain) ?></code></pre>
    <p>If it returns <code><?= htmlspecialchars($server_ip) ?></code>, you're all set.</p>

    <?php if (!$dns_verified): ?>
        <div class="dns-actions">
            <button type="button" class="btn btn-primary" onclick="checkDns()">Check DNS Now</button>
            <span class="dns-check-result" id="dns-check-result"></span>
        </div>

        <script>
        function checkDns() {
            const btn = event.target;
            const result = document.getElementById('dns-check-result');
            btn.disabled = true;
            btn.textContent = 'Checking...';
            result.textContent = '';

            fetch('/api/sites/<?= htmlspecialchars($domain) ?>/check-dns', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.verified) {
                        result.textContent = 'DNS verified! SSL is being provisioned...';
                        result.style.color = '#059669';
                        setTimeout(() => location.reload(), 3000);
                    } else {
                        result.textContent = 'Not yet. DNS is still pointing to ' + (data.current_ip || 'unknown') + '. It may take a few more minutes.';
                        result.style.color = '#d97706';
                    }
                })
                .catch(() => {
                    result.textContent = 'Could not check. Try again in a moment.';
                    result.style.color = '#dc2626';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = 'Check DNS Now';
                });
        }
        </script>
    <?php endif; ?>
</div>

<style>
.dns-instructions { max-width: 700px; }
.dns-instructions h2 { margin-bottom: 1.5rem; }
.dns-instructions h3 { margin-top: 2rem; margin-bottom: 0.75rem; }

.dns-status {
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    font-size: 0.95rem;
}
.dns-status-ok {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.dns-status-pending {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
}

.dns-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    font-size: 0.95rem;
}
.dns-table th,
.dns-table td {
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    text-align: left;
}
.dns-table th {
    background: #f9fafb;
    font-weight: 600;
}
.dns-table code {
    background: #f3f4f6;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.9rem;
}

.dns-note {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    color: #0c4a6e;
    margin: 1rem 0;
}

pre {
    background: #1f2937;
    color: #f9fafb;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    font-size: 0.9rem;
    margin: 0.5rem 0;
}

.dns-actions {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.dns-check-result { font-size: 0.9rem; }
</style>

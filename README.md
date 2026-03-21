# Traitor.dev

> Traitor to WordPress — Simple, fast websites managed by AI.

Traitor.dev is a website hosting platform where sites are plain PHP (or optionally Astro), managed through a Laravel portal, and published with zero-downtime atomic symlink deploys.

## How It Works

1. **Create** a site from a template
2. **Edit** files in the drafts directory
3. **Publish** — drafts get snapshotted to an immutable release and go live instantly
4. **Rollback** — one command to revert to any previous release

No databases, no heavy frameworks, no build steps (unless you want them). Just fast, simple websites.

## Repo Structure

```
portal/          → Laravel app — dashboard, publishing, rollback, site management
templates/       → Starter templates (blank, business, portfolio, landing, demo)
infrastructure/  → Nginx configs and server scripts
```

Customer sites live on the server filesystem (not in this repo) with this layout:

```
/var/www/sites/{domain}/
├── drafts/          ← Working copy (edit here)
├── releases/        ← Immutable snapshots
└── live -> releases/N  ← Symlink to active release
```

## Quick Start

```bash
# Create a new site
php artisan site:create example.com --template=business

# Edit files in drafts/...

# Publish
php artisan site:publish example.com --notes="Initial launch"

# Rollback if needed
php artisan site:rollback example.com
```

## Preview URLs

Every site gets a free preview at `{slug}.sites.traitor.dev` — no DNS setup required. The slug is the domain with dots replaced by dashes.

## Custom Domains

Point your domain's A records to the server IP. SSL is provisioned automatically via Let's Encrypt once DNS propagates.

## Templates

| Template | Description |
|----------|-------------|
| `blank` | Minimal starter |
| `business` | Professional business site |
| `portfolio` | Portfolio / showcase |
| `landing` | Single-page landing |
| `demo` | Full demo site |

## License

Proprietary.

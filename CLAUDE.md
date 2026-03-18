# Traitor.dev - LLM Operating Manual

> "Traitor to WordPress" - Simple, fast websites managed by AI.

## What This Project Is

Traitor.dev is a website hosting platform. This monorepo contains:
- `portal/` - A Laravel app that manages all customer sites (dashboard, publish, rollback)
- `sites/` - Customer website files, one folder per domain
- `templates/` - Starter templates for new sites
- `infrastructure/` - Nginx config templates and server scripts

## How Sites Work

Each site lives in `sites/{domain}/` with this structure:

```
sites/{domain}/
├── drafts/                    # The working copy - edit files HERE
│   ├── public/                # Web-accessible: index.php, about.php, css/, js/, images/
│   └── includes/              # Shared partials: header.php, footer.php, config.php
├── releases/                  # Immutable snapshots - NEVER edit these
│   ├── 1/
│   └── 2/
└── live -> releases/2         # Symlink to active release - NEVER modify manually
```

Sites are plain PHP. No heavy frameworks. Fast, simple, effective.

---

## Creating a New Site

When asked to create a site for a domain (e.g. "create me a site for bobtheleicesterbuilder.com"):

### Step 1: Scaffold
```bash
php artisan site:create bobtheleicesterbuilder.com --template=business
```
Templates available: `blank`, `business`, `portfolio`, `landing`

### Step 2: Configure
Edit `sites/bobtheleicesterbuilder.com/drafts/includes/config.php`:
```php
<?php
return [
    'site_name' => 'Bob The Leicester Builder',
    'domain' => 'bobtheleicesterbuilder.com',
    'meta_description' => 'Professional building services in Leicester',
    'contact_email' => 'bob@bobtheleicesterbuilder.com',
    'phone' => '0116 XXX XXXX',
    'nav' => [
        '/' => 'Home',
        '/about.php' => 'About',
        '/services.php' => 'Services',
        '/contact.php' => 'Contact',
    ],
];
```

### Step 3: Build the pages
Edit files in `sites/{domain}/drafts/public/` — every page follows this pattern:
```php
<?php $config = require __DIR__ . '/../includes/config.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page content here -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### Step 4: Publish
```bash
php artisan site:publish bobtheleicesterbuilder.com --notes="Initial launch"
```

---

## Editing an Existing Site

1. **ALWAYS** edit files in `sites/{domain}/drafts/` — never in `releases/` or `live/`
2. **Read `config.php` first** to understand the site's structure and settings
3. After making changes, publish: `php artisan site:publish {domain}`
4. To undo: `php artisan site:rollback {domain}`

---

## Site File Conventions

### config.php (READ THIS FIRST)
Location: `drafts/includes/config.php`
Contains: site_name, domain, meta_description, nav structure, contact details.
This is the single source of truth for a site.

### header.php
- Outputs `<!DOCTYPE html>`, `<head>` with meta tags from config
- Opens `<body>`, renders navigation from `$config['nav']`

### footer.php
- Closes `</body></html>`, includes footer content

### CSS
- Location: `drafts/public/css/style.css`
- Mobile-first, responsive
- No CSS frameworks unless specifically requested

### JavaScript
- Location: `drafts/public/js/main.js`
- Vanilla JS preferred, minimal dependencies

---

## Style Guidelines

- Modern, semantic HTML5
- Mobile-first responsive CSS (no frameworks unless requested)
- Minimal JavaScript — vanilla JS preferred
- Fast load times — optimize images, minimal dependencies
- Accessible: proper alt tags, ARIA labels, semantic elements
- Clean, professional design with good typography and spacing

---

## Portal Commands Reference

```bash
php artisan site:create {domain} [--template=blank]    # Create new site
php artisan site:publish {domain} [--notes="desc"]      # Publish draft to live
php artisan site:rollback {domain} [--version=N]        # Rollback to previous release
php artisan site:list                                    # List all sites and status
php artisan site:status {domain}                         # Show site details + releases
```

---

## Important Rules

1. **NEVER** edit files inside `releases/` or the `live` symlink target
2. **ALWAYS** work in the `drafts/` directory
3. **ALWAYS** read `config.php` before editing a site
4. After finishing edits, remind the user to publish (or publish if asked)
5. Keep sites simple and fast — no heavy frameworks unless asked
6. When creating a new site, always set up `config.php` first

---

## Deployment Architecture

### How Publishing Works
1. Copy `drafts/` → `releases/{N}/` (next version number)
2. Swap symlink: `live` → `releases/{N}` (atomic, zero-downtime)
3. Record release in portal database

### How Rollback Works
1. Re-point symlink: `live` → `releases/{previous}` (instant)
2. Drafts remain unchanged (they represent the "next" state)

### Nginx
- Each site gets its own nginx config generated from `infrastructure/nginx/site.conf.template`
- Root points to `sites/{domain}/live/public/`
- Portal manages config generation and `nginx -s reload`

### DNS
- Customer points their domain A record to the server IP
- Portal generates nginx config for the domain
- Certbot provisions SSL automatically

---

## Astro Sites (Optional)

Sites can optionally use Astro for static generation. Set `type: astro` in the site record.

```
sites/{domain}/
├── drafts/
│   ├── src/pages/          # Astro source
│   ├── src/layouts/
│   ├── public/             # Static assets
│   ├── astro.config.mjs
│   └── package.json
├── releases/
│   └── 1/public/           # Built output
└── live -> releases/1
```

Publish step: `npm run build` in drafts, copy `dist/` to `releases/{N}/public/`.

---

## Project Structure Quick Reference

```
traitordev/
├── CLAUDE.md              ← You are here
├── portal/                ← Laravel management app
├── sites/                 ← Customer sites (one folder per domain)
├── templates/             ← Starter templates
└── infrastructure/        ← Nginx configs, server scripts
```

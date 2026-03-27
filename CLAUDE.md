# Traitor.dev - LLM Operating Manual

> "Traitor to WordPress" - Simple, fast websites managed by AI.

## Versioning

A `portal/VERSION` file tracks the current build version (format: `YYYYMMDD.HHMM`).

A Claude Code hook in `.claude/settings.json` automatically updates it before every `git commit`. After any `git commit`, **always tell the user the version that was set** — it appears in the hook output as `Version set to: YYYYMMDD.HHMM`.

A second hook runs before `git push` as a safety net: if VERSION wasn't updated today, it bumps it and commits automatically.

---

## Cardinal Rules of Traitor.dev

### The Preview Must Be Transparent
The preview system must work for any PHP site without modifications to the site's files. Never require changes to a customer site to make it work in preview. If the preview breaks something, fix the preview — not the site.

---

## Ground Rules

### No Fallbacks. Ever.
Never use fallback values, default alternatives, or silent recovery. If something is missing or wrong, **break loudly**. Fallbacks hide bugs until they surface as mysterious behaviour weeks later. A crash in development is a gift — it forces an immediate, permanent fix.

This means:
- No `?? 'default'` or `?:` fallbacks for config values that should exist
- No `try/catch` that silently swallows errors and continues
- No "graceful degradation" that masks a broken dependency
- If a required env var is missing, crash. Don't guess.

### SOLID Principles
Follow SOLID. Single responsibility, open/closed, Liskov substitution, interface segregation, dependency inversion. Classes should do one thing well.

### YAGNI
Don't build it until you need it. No speculative features, no "we might need this later" abstractions, no configurability for scenarios that don't exist yet.

### Never Swallow Errors
Never catch exceptions and silently continue. If something fails, let it fail visibly. No empty catch blocks, no `catch (\Exception $e) { // ignore }`, no logging-and-continuing when the operation was critical. If you catch an exception, it must be to add context before re-throwing or to handle a specific, expected case.

### KISS
Keep it simple. The simplest solution that works is the right one. Don't over-engineer, don't add layers of indirection without clear benefit.

### DRY — But Not Obsessively
Repetition is fine until a pattern is clearly established. Duplicate code 2-3 times before extracting. Premature abstraction is worse than a bit of repetition. Only pull out shared code when it's genuinely the same concept, not just coincidentally similar.

---

## What This Project Is

Traitor.dev is a website hosting platform. This repo contains the **platform** — not the customer sites themselves.

- `portal/` - A Laravel app that manages all customer sites (dashboard, publish, rollback)
- `templates/` - Starter templates for new sites (blank, demo, etc.)
- `infrastructure/` - Nginx config templates and server scripts

**Customer sites live outside this repo** on the server's filesystem at a configurable path (`SITES_PATH` in portal `.env`, default `/var/www/sites`). They are versioned by the release system (drafts → releases → symlink), not by git. Git-backing sites could be added as a future feature but is not part of the core.

## How Sites Work

Each site lives in `{SITES_PATH}/{domain}/` on the server with this structure:

```
{SITES_PATH}/{domain}/
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
Templates available: `blank`, `demo`, `business`, `portfolio`, `landing`

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

## Preview Sites (No Domain Required)

Every site gets a **free preview URL** automatically:

```
{slug}.sites.traitor.dev
```

The slug is the domain with dots replaced by dashes:
- `bobtheleicesterbuilder.com` → `bobtheleicesterbuilder-com.sites.traitor.dev`
- `demo.traitor.dev` → `demo-traitor-dev.sites.traitor.dev`

### How it works
- **DNS:** A single wildcard record `*.sites.traitor.dev → SERVER_IP` handles all previews
- **Nginx:** `infrastructure/nginx/preview.conf` uses a wildcard `server_name` with a `map` to route slugs to site folders
- **Map file:** Auto-generated by `infrastructure/scripts/generate-preview-map.sh` — run it after adding/removing sites
- **Serves live/ if published**, falls back to drafts/ for unpublished sites
- A small "Preview" banner is injected at the bottom of every page

### When a site is created
The preview URL works immediately (once the map is regenerated). No per-site nginx config needed for previews.

---

## Connecting a Custom Domain

When a customer wants to use their own domain:

### What we tell them
Show the DNS instructions page (`templates/dns-instructions.php`) which displays:

1. **Add two A records** at their registrar:
   - `@` → `SERVER_IP` (root domain)
   - `www` → `SERVER_IP` (www subdomain)
2. **Wait for propagation** (usually 5-30 minutes)
3. **SSL is automatic** — we provision via Let's Encrypt once DNS resolves

### What happens on our side
1. Portal generates a per-domain nginx config from `infrastructure/nginx/site.conf.template`
2. Portal runs `nginx -t && nginx -s reload`
3. Portal periodically checks DNS (or user clicks "Check DNS Now")
4. Once DNS verified, run `infrastructure/scripts/certbot-hook.sh {domain}` for SSL
5. Uncomment the HTTPS block in the site's nginx config, reload again

### The flow
```
Site created → Preview URL works immediately
                ↓
Customer adds domain → DNS instructions shown
                ↓
DNS propagates → We detect it, provision SSL
                ↓
Custom domain live with HTTPS
```

The preview URL continues to work alongside the custom domain.

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
traitordev/                        ← This repo (the platform)
├── CLAUDE.md                      ← You are here
├── portal/                        ← Laravel management app
├── templates/                     ← Starter templates + DNS instructions
│   ├── blank/                     ← Minimal starter
│   ├── demo/                      ← Full demo site (traitor.dev showcase)
│   └── dns-instructions.php       ← DNS setup page template
└── infrastructure/                ← Nginx configs, server scripts
    ├── nginx/
    │   ├── site.conf.template     ← Per-domain nginx config template
    │   ├── preview.conf           ← Wildcard preview subdomain config
    │   └── portal.conf            ← Portal dashboard config
    └── scripts/
        ├── generate-preview-map.sh  ← Regenerate preview routing
        └── certbot-hook.sh          ← SSL provisioning

/var/www/sites/                    ← Customer sites (on server, NOT in git)
├── bobtheleicesterbuilder.com/
│   ├── drafts/                    ← Working copy
│   ├── releases/                  ← Immutable snapshots
│   └── live -> releases/2         ← Symlink to active release
└── anotherdomain.com/
    └── ...
```

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
marketing/       → traitor.dev marketing site (static HTML)
```

Customer sites live on the server filesystem (not in this repo) with this layout:

```
~/sites/{domain}/
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

## Local Development

```bash
make dev     # Start everything (Docker — PHP 8.3, MySQL, Nginx)
make stop    # Stop containers
make shell   # Drop into PHP container
make migrate # Run migrations
make fresh   # Nuclear reset — wipe volumes and rebuild
```

Portal runs at `http://localhost:8080`.

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

## Deployment

Deploys automatically on push to `main` via GitHub Actions. Zero-downtime rsync + symlink strategy:

1. New code rsynced to `releases/{timestamp}/` (unchanged files hardlinked from `live` — fast)
2. Shared `.env` and `storage/` symlinked in
3. `php artisan migrate`, `config:cache`, `route:cache`, `view:cache` run in the new release
4. `live` symlink atomically swapped
5. Releases beyond 5 pruned automatically

### Server layout

```
~/
├── portal/
│   ├── shared/
│   │   ├── .env          ← single .env, never overwritten by deploys
│   │   └── storage/      ← logs, sessions, cache — persisted across releases
│   ├── releases/
│   │   ├── 20260326120000/
│   │   └── 20260326130000/
│   └── live -> releases/20260326130000  ← symlink, swapped atomically
└── public_html/          ← marketing site (traitor.dev)
```

cPanel subdomains:
- `traitor.dev` → `~/public_html`
- `portal.traitor.dev` → `~/portal/live/public`

### First-time server setup

Run once after provisioning (requires shell access):

```bash
mkdir -p ~/portal/shared/storage/logs
mkdir -p ~/portal/shared/storage/app/public
mkdir -p ~/portal/shared/storage/framework/{cache/data,sessions,views}
# Upload .env.production as ~/portal/shared/.env
```

### SSH deploy key setup

**1. Generate the deploy key:**
```bash
ssh-keygen -t ed25519 -C "traitor.dev deploy" -f ~/.ssh/traitordev_deploy -N ""
```

**2. Add the public key to cPanel:**
- cPanel → SSH Access → Manage SSH Keys → Import Key
- Paste the contents of `~/.ssh/traitordev_deploy.pub`
- Click **Authorize** next to the imported key
- Ensure shell access is enabled (WHM → Manage Shell Access → Normal Shell)

**3. Add GitHub Secrets** (repo → Settings → Secrets → Actions):

| Secret | Value |
|---|---|
| `SSH_PRIVATE_KEY` | `cat ~/.ssh/traitordev_deploy` |
| `SSH_KNOWN_HOSTS` | `ssh-keyscan grh17.myukcloud.com` |
| `SSH_HOST` | `grh17.myukcloud.com` |
| `SSH_USER` | `traitor8921` |
| `DEPLOY_PATH` | `~/portal` |
| `MARKETING_PATH` | `~/public_html` |

**4. Trigger a deploy** — push to `main` or Actions → Deploy → Run workflow.

### .env setup

Copy `portal/.env.production` to `~/portal/shared/.env` on the server. Never commit `.env` to git.

## License

Proprietary.

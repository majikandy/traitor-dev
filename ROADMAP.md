# Roadmap & Ideas

> Traitor to WordPress — Simple, fast websites managed by AI.

## In progress / next up

- **LLM site builder** — describe changes in a text box, AI edits draft files directly and creates a release. The whole premise of the product.
- **Deploy from GitHub** — connect a repo as an alternative to zip uploads; auto-create a release on push to main.
- **Preview subdomains** — `{slug}.preview.traitor.dev` per site via cPanel SubDomain API (same pattern as custom domain attachment). One-time server setup: add `preview.traitor.dev` as a cPanel addon domain. Portal calls API on site create/delete. AutoSSL handles SSL automatically. Path-based `/preview/{token}/` stays for version-specific links.

## Backlog

### AI
- **AI-generated release notes** — auto-summarise what changed between releases by diffing files
- **AI site audit** — spot broken links, missing alt text, slow images, suggest improvements

### Workflow
- **Scheduled go-live** — pick a date/time for a release to go live automatically (great for launches)
- **Compare releases** — visual diff between two releases in the preview (split or toggle)

### Visibility
- **Analytics** — page view counts per site, server-side log parsing, no JS, no cookie banners
- **Uptime monitoring** — ping each live domain, show a status dot, alert on downtime
- **Form submissions** — catch `<form>` POSTs from customer sites, store and show in the portal

### Collaboration
- **Client portal** — read-only view for the end client to approve a release before it goes live
- **Activity log per site** — who uploaded, who promoted, when — full audit trail

### Technical
- ~~**Deploy from GitHub**~~ — moved to next up
- **Custom 404 / error pages** — per-site, uploaded or edited in the portal
- **Custom domain SSL automation** — after DNS verified, portal triggers AutoSSL and tracks `domain_status` through `pending_dns → dns_verified → ssl_active` without manual certbot steps
- **GitHub auto-deploy toggle** — `github_auto_deploy` column exists, no UI yet
- **Maintenance mode UI** — `maintenance_mode` column exists, not wired to a toggle in the dashboard

## Done

- [x] Rollback with one tap — Rollback button on older releases with confirmation
- [x] Desktop + mobile preview with expand / fullscreen
- [x] Passkey-first auth (register + login)
- [x] Multi-tenancy — separate organisations, full data isolation
- [x] Maintenance mode — coming soon page, one-tap toggle
- [x] Zero-downtime atomic deploys via symlink
- [x] Custom domain attachment + DNS verification + AutoSSL

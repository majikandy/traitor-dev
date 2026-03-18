#!/bin/bash
# Generate the nginx map for preview subdomain routing
#
# Scans sites/ directory and outputs slug → domain mappings:
#   bobtheleicesterbuilder-com  bobtheleicesterbuilder.com;
#   demo-traitor-dev            demo.traitor.dev;
#
# Run after creating/removing a site. The portal calls this automatically.
# Usage: ./generate-preview-map.sh [--reload]

set -euo pipefail

SITES_DIR="/home/user/traitordev/sites"
MAP_FILE="/etc/nginx/traitor-preview-map.conf"
TEMP_FILE=$(mktemp)

echo "# Auto-generated — do not edit manually" > "$TEMP_FILE"
echo "# Run: infrastructure/scripts/generate-preview-map.sh" >> "$TEMP_FILE"
echo "# Generated: $(date -u +%Y-%m-%dT%H:%M:%SZ)" >> "$TEMP_FILE"
echo "" >> "$TEMP_FILE"

count=0
for site_dir in "$SITES_DIR"/*/; do
    [ -d "$site_dir" ] || continue

    domain=$(basename "$site_dir")
    [[ "$domain" == .* ]] && continue

    # Must have either live/ or drafts/ to be serveable
    if [ ! -d "$site_dir/live" ] && [ ! -L "$site_dir/live" ] && [ ! -d "$site_dir/drafts/public" ]; then
        continue
    fi

    # dots → dashes for slug
    slug=$(echo "$domain" | tr '.' '-')

    echo "${slug} ${domain};" >> "$TEMP_FILE"
    count=$((count + 1))
done

sudo cp "$TEMP_FILE" "$MAP_FILE"
rm "$TEMP_FILE"

echo "Preview map updated: ${count} sites"

# Optionally reload nginx
if [[ "${1:-}" == "--reload" ]]; then
    sudo nginx -t && sudo nginx -s reload
    echo "Nginx reloaded"
fi

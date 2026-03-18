#!/bin/bash
# Provision SSL certificate for a domain using certbot
# Usage: ./certbot-hook.sh <domain>

set -euo pipefail

DOMAIN="${1:?Usage: $0 <domain>}"

echo "Provisioning SSL for ${DOMAIN}..."

certbot certonly \
    --nginx \
    -d "${DOMAIN}" \
    -d "www.${DOMAIN}" \
    --non-interactive \
    --agree-tos \
    --email admin@traitor.dev

echo "SSL provisioned for ${DOMAIN}"
echo "Remember to uncomment the HTTPS block in /etc/nginx/sites-enabled/${DOMAIN}.conf"
echo "Then reload nginx: sudo nginx -s reload"

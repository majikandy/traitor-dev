#!/bin/bash
set -e

echo ""
echo "  traitor.dev — new site setup"
echo "  ----------------------------"
echo ""

read -p "  Domain: " domain

if [ -z "$domain" ]; then
    echo "  Domain is required."
    exit 1
fi

echo ""
echo "  Templates: blank, business, portfolio, landing, demo"
read -p "  Template: " template

if [ -z "$template" ]; then
    echo "  Template is required."
    exit 1
fi

echo ""
echo "  Creating $domain with '$template' template..."
echo ""

php artisan site:create "$domain" --template="$template"

echo ""
echo "  Done! Next steps:"
echo "    make publish domain=$domain"
echo ""

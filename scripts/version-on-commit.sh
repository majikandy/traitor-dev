#!/bin/bash
REPO=/Users/majikandy/Dev/traitordev
VERSION=$(date +"%Y%m%d.%H%M")
printf "%s" "$VERSION" > "$REPO/portal/VERSION"
git -C "$REPO" add portal/VERSION
echo "Version set to: $VERSION"

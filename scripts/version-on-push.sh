#!/bin/bash
REPO=/Users/majikandy/Dev/traitordev
TODAY=$(date +"%Y%m%d")
COMMITTED=$(git -C "$REPO" show HEAD:portal/VERSION 2>/dev/null || echo "")
if [[ "$COMMITTED" != "$TODAY"* ]]; then
    VERSION=$(date +"%Y%m%d.%H%M")
    printf "%s" "$VERSION" > "$REPO/portal/VERSION"
    git -C "$REPO" add portal/VERSION
    git -C "$REPO" commit -m "Bump version to $VERSION"
    echo "Version bumped to: $VERSION (pre-push fix)"
else
    echo "Version already current: $COMMITTED"
fi

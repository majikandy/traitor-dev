#!/bin/bash
# PreToolUse hook — reads stdin JSON to check if this is a git commit command.
INPUT=$(cat)
COMMAND=$(echo "$INPUT" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('tool_input',{}).get('command',''))" 2>/dev/null || echo "")

# Only act on git commit calls
if [[ "$COMMAND" != *"git commit"* ]]; then
    exit 0
fi

REPO=/Users/majikandy/Dev/traitordev

# Run tests — block the commit if they fail
cd "$REPO/portal" && php artisan test --no-ansi 2>&1
if [ $? -ne 0 ]; then
    echo '{"continue": false, "stopReason": "Tests failed — fix before committing."}'
    exit 1
fi

VERSION=$(date +"%Y%m%d.%H%M")
printf "%s" "$VERSION" > "$REPO/portal/VERSION"
git -C "$REPO" add portal/VERSION
echo "Version set to: $VERSION"

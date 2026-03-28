#!/usr/bin/env bash
set -euo pipefail

SSH_KEY=~/.ssh/traitordev_deploy
SSH_USER=traitor8921
SSH_HOST=traitor.dev
DEPLOY_PATH=/home/traitor8921/portal
SSH="ssh -i $SSH_KEY -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST"
RSYNC_SSH="ssh -i $SSH_KEY -o StrictHostKeyChecking=no"

RELEASE=$(date +%Y%m%d%H%M%S)
START=$(date +%s%N)

echo "→ Deploying release $RELEASE"

# Check if vendor needs uploading
SERVER_LOCK=$($SSH "md5sum $DEPLOY_PATH/live/composer.lock 2>/dev/null | cut -d' ' -f1" || echo "none")
LOCAL_LOCK=$(md5sum portal/composer.lock | cut -d' ' -f1)
[ "$SERVER_LOCK" = "$LOCAL_LOCK" ] && VENDOR_EXCLUDE="--exclude=vendor/" || VENDOR_EXCLUDE=""

$SSH "mkdir -p $DEPLOY_PATH/releases/$RELEASE"

# Rsync marketing + portal + preview dispatcher in parallel
rsync -az --delete \
    -e "$RSYNC_SSH" \
    marketing/ \
    $SSH_USER@$SSH_HOST:/home/$SSH_USER/public_html/ &

rsync -az \
    -e "$RSYNC_SSH" \
    --exclude='.git' --exclude='.github' --exclude='.env' \
    --exclude='storage' --exclude='bootstrap/cache/*' \
    $VENDOR_EXCLUDE \
    --link-dest="$DEPLOY_PATH/live" \
    portal/ \
    $SSH_USER@$SSH_HOST:$DEPLOY_PATH/releases/$RELEASE/ &

rsync -az --delete \
    -e "$RSYNC_SSH" \
    infrastructure/preview-dispatcher/ \
    $SSH_USER@$SSH_HOST:/home/$SSH_USER/preview-dispatcher/ &

wait

# Single SSH connection for everything else
$SSH "
    set -e
    if [ ! -d '$DEPLOY_PATH/releases/$RELEASE/vendor' ]; then
        cp -rl \$(readlink -f $DEPLOY_PATH/live)/vendor $DEPLOY_PATH/releases/$RELEASE/vendor
    fi
    ln -sfn $DEPLOY_PATH/shared/.env $DEPLOY_PATH/releases/$RELEASE/.env
    ln -sfn $DEPLOY_PATH/shared/storage $DEPLOY_PATH/releases/$RELEASE/storage
    mkdir -p $DEPLOY_PATH/releases/$RELEASE/bootstrap/cache
    cd $DEPLOY_PATH/releases/$RELEASE
    php artisan migrate --force --quiet
    php artisan config:cache --quiet
    php artisan route:cache --quiet
    php artisan view:cache --quiet
    ln -sfn $DEPLOY_PATH/releases/$RELEASE $DEPLOY_PATH/live
    ls -1dt $DEPLOY_PATH/releases/*/ | tail -n +6 | xargs rm -rf
"

END=$(date +%s%N)
echo "✓ Done in $(( (END - START) / 1000000 ))ms"

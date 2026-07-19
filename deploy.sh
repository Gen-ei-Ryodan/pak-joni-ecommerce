#!/bin/bash

# Deploy entire main branch (standard Laravel structure) to hosting
# Target: /home/alurelab/jomotocenter.com/

DEPLOY_DIR="/home/alurelab/jomotocenter.com"
REPO_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "==> Deploying main branch to $DEPLOY_DIR"

# 1. Sync all files (exclude .git, .env, storage, vendor)
rsync -a --delete \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/framework/cache/data/*' \
  --exclude='storage/logs/*' \
  --exclude='storage/debugbar/*' \
  "$REPO_DIR/" "$DEPLOY_DIR/"

# 2. Set permissions
chmod -R 755 "$DEPLOY_DIR/storage"
chmod -R 755 "$DEPLOY_DIR/bootstrap/cache"
chmod 644 "$DEPLOY_DIR/.env"

# 3. Install composer dependencies (if composer available)
if command -v composer &> /dev/null; then
    cd "$DEPLOY_DIR" && composer install --no-dev --optimize-autoloader
fi

# 4. Laravel optimizations
cd "$DEPLOY_DIR"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Run migrations
php artisan migrate --force

echo "==> Deploy complete!"

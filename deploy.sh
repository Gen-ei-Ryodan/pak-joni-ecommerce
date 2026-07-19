#!/bin/bash

# Deploy entire main branch (standard Laravel structure) to hosting
# Target: /home/alurelab/jomotocenter.com/

DEPLOY_DIR="/home/alurelab/jomotocenter.com"
REPO_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "==> Deploying main branch from $REPO_DIR to $DEPLOY_DIR"

# 1. Create target directory if not exists
mkdir -p "$DEPLOY_DIR"

# 2. Sync all files using cp (rsync not available on hosting)
# Exclude .git, .env, vendor, node_modules
cd "$REPO_DIR"
for item in * .[^.]*; do
    [ "$item" = ".git" ] && continue
    [ "$item" = ".env" ] && continue
    [ "$item" = "vendor" ] && continue
    [ "$item" = "node_modules" ] && continue
    [ "$item" = "." ] && continue
    [ "$item" = ".." ] && continue
    cp -r "$item" "$DEPLOY_DIR/"
done

# 3. Set permissions
mkdir -p "$DEPLOY_DIR/storage"
mkdir -p "$DEPLOY_DIR/bootstrap/cache"
chmod -R 755 "$DEPLOY_DIR/storage"
chmod -R 755 "$DEPLOY_DIR/bootstrap/cache"
if [ -f "$DEPLOY_DIR/.env" ]; then
    chmod 644 "$DEPLOY_DIR/.env"
fi

# 4. Install composer dependencies (if composer available)
cd "$DEPLOY_DIR"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
fi

# 5. Laravel optimizations
if [ -f "artisan" ]; then
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    # 6. Run migrations
    php artisan migrate --force
else
    echo "WARNING: artisan not found in $DEPLOY_DIR"
fi

echo "==> Deploy complete!"

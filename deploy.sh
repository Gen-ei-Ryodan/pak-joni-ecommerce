#!/bin/bash
set -e

# Deploy to hosting via tar+ssh
# Target: alurelab@emerald.hidden-server.net:/home/alurelab/jomotocenter.com/

SSH_HOST="alurelab"
SSH_PORT="31988"
REMOTE_DIR="/home/alurelab/jomotocenter.com"
TAR_FILE="/tmp/deploy-$(date +%s).tar.gz"

echo "==> Deploying to $SSH_HOST:$REMOTE_DIR"

# Build tar locally first (avoid pipe issues)
echo "==> Creating archive..."
tar czf "$TAR_FILE" \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage' \
  --exclude='deploy.sh' \
  --exclude="$TAR_FILE" \
  -C "$(dirname "$0")" .

# Copy tar to server
echo "==> Transferring archive..."
scp -P "$SSH_PORT" "$TAR_FILE" "$SSH_HOST:$REMOTE_DIR/deploy.tar.gz"

# Extract and run artisan commands
echo "==> Extracting & running post-deploy tasks..."
ssh -n -p "$SSH_PORT" "$SSH_HOST" "
  export PATH=\$PATH:/usr/local/cpanel/3rdparty/lib/path-bin:/opt/alt/php84/usr/bin
  cd '$REMOTE_DIR'
  tar xzf deploy.tar.gz --overwrite
  rm -f deploy.tar.gz
  chmod -R 755 storage bootstrap/cache 2>/dev/null
  if [ -f '.env' ]; then chmod 644 .env; fi
  if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
  fi
  if [ -f 'artisan' ]; then
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan migrate --force
  fi
"

# Clean up local tar
rm -f "$TAR_FILE"

echo "==> Deploy complete!"

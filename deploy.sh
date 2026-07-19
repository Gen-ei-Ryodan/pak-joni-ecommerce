#!/bin/bash
set -e

# Deploy to hosting via tar+ssh
# Target: alurelab@emerald.hidden-server.net:/home/alurelab/jomotocenter.com/

SSH_HOST="alurelab"
SSH_PORT="31988"
REMOTE_DIR="/home/alurelab/jomotocenter.com"

echo "==> Deploying to $SSH_HOST:$REMOTE_DIR"

# Transfer files via tar over ssh
tar czf - \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage' \
  --exclude='deploy.sh' \
  -C "$(dirname "$0")" . | \
  ssh -n -p "$SSH_PORT" "$SSH_HOST" "
    mkdir -p '$REMOTE_DIR'
    cd '$REMOTE_DIR'
    tar xzf - --overwrite 2>/dev/null
    chmod -R 755 storage bootstrap/cache 2>/dev/null
  "

# Run artisan commands separately (avoid pipe stdin conflict)
ssh -n -p "$SSH_PORT" "$SSH_HOST" "
  export PATH=\$PATH:/usr/local/cpanel/3rdparty/lib/path-bin:/opt/alt/php84/usr/bin
  cd '$REMOTE_DIR'
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

echo "==> Deploy complete!"

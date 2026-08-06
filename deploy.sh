#!/bin/bash
set -e

# Deploy to hosting via git pull + cp
# Target: alurelab@emerald.hidden-server.net
# Repository: /home/alurelab/repositories/pak-joni-ecommerce
# Public: /home/alurelab/jomotocenter.com

SSH_HOST="alurelab"
SSH_PORT="31988"
REPO_DIR="/home/alurelab/repositories/pak-joni-ecommerce"
PUBLIC_DIR="/home/alurelab/jomotocenter.com"

echo "==> Deploying to $SSH_HOST"

# SSH commands
ssh -n -p "$SSH_PORT" "$SSH_HOST" "
  export PATH=\$PATH:/usr/local/cpanel/3rdparty/lib/path-bin:/opt/alt/php84/usr/bin
  
  echo '==> Pulling latest code from GitHub...'
  cd '$REPO_DIR'
  git fetch origin main
  git reset --hard origin/main
  
  echo '==> Syncing to public directory...'
  cd '$REPO_DIR'
  
  # Copy app files
  cp -r app '$PUBLIC_DIR/'
  cp -r bootstrap '$PUBLIC_DIR/'
  cp -r config '$PUBLIC_DIR/'
  cp -r database '$PUBLIC_DIR/'
  cp -r public '$PUBLIC_DIR/'
  cp -r resources '$PUBLIC_DIR/'
  cp -r routes '$PUBLIC_DIR/'
  
  # Copy individual files
  cp artisan '$PUBLIC_DIR/'
  cp composer.json '$PUBLIC_DIR/'
  cp composer.lock '$PUBLIC_DIR/'
  cp package.json '$PUBLIC_DIR/' 2>/dev/null || true
  cp vite.config.js '$PUBLIC_DIR/' 2>/dev/null || true
  
  echo '==> Setting permissions...'
  cd '$PUBLIC_DIR'
  chmod -R 755 storage bootstrap/cache 2>/dev/null || true
  
  echo '==> Running post-deploy tasks...'
  rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
  
  if [ -f '.env' ]; then chmod 644 .env; fi
  
  if command -v composer &> /dev/null; then
    echo '==> Installing dependencies...'
    composer install --no-dev --optimize-autoloader --no-interaction
  fi
  
  if [ -f 'artisan' ]; then
    echo '==> Running artisan commands...'
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan migrate --force
  fi
"

echo "==> Deploy complete!"

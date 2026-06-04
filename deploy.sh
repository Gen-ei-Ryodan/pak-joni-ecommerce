#!/bin/bash
#
# Deploy MOTOMART ke shared hosting (cPanel)
# Struktur:
#   ~/public_html/jomoto.solusisurabaya.com/  → web root (public/)
#   ~/jomoto/                                  → kode Laravel (hidden)
#
# Usage: bash deploy.sh

set -e

APP_DIR="$HOME/jomoto"
WEB_DIR="$HOME/public_html/jomoto.solusisurabaya.com"

echo "=== MOTOMART DEPLOY ==="
echo "Web:  $WEB_DIR"
echo "App:  $APP_DIR"
echo ""

# ── 1. Copy app code ────────────────────────────────────────────
echo "[1/6] Copying application code..."
rsync -av --delete \
    --exclude='public/' \
    --exclude='.git/' \
    --exclude='.env.example' \
    --exclude='node_modules/' \
    --exclude='.gitignore' \
    --exclude='*.md' \
    --exclude='tests/' \
    ./ "$APP_DIR/"

# ── 2. Copy public assets ───────────────────────────────────────
echo "[2/6] Copying public assets..."
rsync -av --delete \
    public/ "$WEB_DIR/"

# ── 3. Fix index.php path ───────────────────────────────────────
echo "[3/6] Fixing index.php paths..."
sed -i '' "s|__DIR__.'/../vendor/autoload.php'|'$APP_DIR/vendor/autoload.php'|" "$WEB_DIR/index.php"
sed -i '' "s|__DIR__.'/../bootstrap/app.php'|'$APP_DIR/bootstrap/app.php'|" "$WEB_DIR/index.php"

# ── 4. Install dependencies ─────────────────────────────────────
echo "[4/6] Running composer install..."
cd "$APP_DIR"
/usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

# ── 5. Laravel setup ────────────────────────────────────────────
echo "[5/6] Laravel setup..."
cd "$APP_DIR"
php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ── 6. Fix permissions ──────────────────────────────────────────
echo "[6/6] Fixing permissions..."
find "$APP_DIR/storage" -type d -exec chmod 775 {} \;
find "$APP_DIR/storage" -type f -exec chmod 664 {} \;
find "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;

echo ""
echo "=== DEPLOY COMPLETE ==="
echo "URL: https://jomoto.solusisurabaya.com"
echo ""
echo "⚠️  Pastikan .env sudah ada di: $APP_DIR/.env"
echo "⚠️  Pastikan APP_URL di .env: https://jomoto.solusisurabaya.com"

#!/bin/bash
# deploy.sh — thuchocvn.vn production deploy
# Chạy thủ công: bash deploy.sh
# Chạy từ GitHub Actions: SKIP_MIGRATIONS=false bash deploy.sh
set -euo pipefail

APP_DIR="/var/www/minhan"
PHP="/usr/bin/php8.5"
BRANCH="main"
SKIP_MIGRATIONS="${SKIP_MIGRATIONS:-false}"

log() { echo "[$(date '+%H:%M:%S')] $*"; }
ok()  { echo "[$(date '+%H:%M:%S')] ✓ $*"; }
err() { echo "[$(date '+%H:%M:%S')] ✗ $*" >&2; }

log "═══════════════════════════════════════"
log "  Deploy thuchocvn.vn — $(date '+%Y-%m-%d %H:%M:%S')"
log "  Branch: $BRANCH | Skip migrations: $SKIP_MIGRATIONS"
log "═══════════════════════════════════════"

cd "$APP_DIR"

# ── 1. Kéo code mới ─────────────────────────────────────────
log "[1/7] Pulling latest code..."
git fetch origin
git reset --hard "origin/$BRANCH"
ok "Code updated → $(git log --oneline -1)"

# ── 2. PHP dependencies ──────────────────────────────────────
log "[2/7] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet
ok "Composer done"

# ── 3. Frontend build ────────────────────────────────────────
log "[3/7] Building frontend assets..."
# Đảm bảo deploy user có quyền xóa/ghi build artifacts từ lần deploy trước
sudo chown -R deploy:www-data "$APP_DIR/public/build" 2>/dev/null || true
npm ci --prefer-offline --silent
npm run build --silent
ok "Frontend built → public/build/"

# ── 4. Maintenance mode ──────────────────────────────────────
log "[4/7] Enabling maintenance mode..."
$PHP artisan down --retry=10
trap '$PHP artisan up; err "Deploy failed — maintenance mode disabled"' ERR

# ── 5. Migration ─────────────────────────────────────────────
if [ "$SKIP_MIGRATIONS" = "true" ]; then
    log "[5/7] Skipping migrations (SKIP_MIGRATIONS=true)"
else
    log "[5/7] Running database migrations..."
    $PHP artisan migrate --force
    ok "Migrations done"
fi

# ── 6. Rebuild cache ─────────────────────────────────────────
log "[6/7] Rebuilding application cache..."
$PHP artisan config:clear  && $PHP artisan config:cache
$PHP artisan route:clear   && $PHP artisan route:cache
$PHP artisan view:clear    && $PHP artisan view:cache
$PHP artisan event:clear   && $PHP artisan event:cache
ok "Cache rebuilt"

# ── 7. Khởi động lại workers ─────────────────────────────────
log "[7/7] Restarting workers..."
$PHP artisan up
trap - ERR   # bỏ trap sau khi up thành công

sudo supervisorctl restart minhan-horizon  > /dev/null
sudo supervisorctl restart minhan-reverb   > /dev/null
ok "Horizon + Reverb restarted"

log ""
log "✅ Deploy hoàn tất — $(date '+%Y-%m-%d %H:%M:%S')"
log "   Commit: $(git log --oneline -1)"

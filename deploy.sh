#!/bin/bash
# deploy.sh — thuchocvn.vn production deploy
# Chạy thủ công: bash deploy.sh
# Chạy migrate (chỉ khi quản trị chủ động muốn): SKIP_MIGRATIONS=false bash deploy.sh
set -euo pipefail

APP_DIR="/var/www/minhan"
PHP="/usr/bin/php8.5"
BRANCH="main"
# Mặc định KHÔNG chạy migrate trong deploy tự động — DB schema được quản trị
# chủ động kiểm soát qua `php artisan migration:generate --fresh` (local/staging)
# rồi áp dụng tay lên production. Tự động migrate từng làm vỡ deploy nhiều lần
# do migrations table lệch so với schema thực tế trên production.
SKIP_MIGRATIONS="${SKIP_MIGRATIONS:-true}"

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
# Bảo vệ .env khỏi bị git reset --hard ghi đè
[ -f "$APP_DIR/.env" ] && cp "$APP_DIR/.env" /tmp/.env.deploy.bak
git fetch origin
git reset --hard "origin/$BRANCH"
[ -f /tmp/.env.deploy.bak ] && mv /tmp/.env.deploy.bak "$APP_DIR/.env"
ok "Code updated → $(git log --oneline -1)"

# ── 2. PHP dependencies ──────────────────────────────────────
log "[2/7] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet
ok "Composer done"

# ── 3. Frontend build ────────────────────────────────────────
log "[3/7] Building frontend assets..."
# Đảm bảo deploy user có quyền xóa/ghi build artifacts từ lần deploy trước
sudo /usr/local/bin/fix-minhan-build 2>/dev/null || true
npm ci --prefer-offline --silent
npm run build --silent
ok "Frontend built → public/build/"

# ── 4. Maintenance mode ──────────────────────────────────────
log "[4/7] Enabling maintenance mode..."
# Fix storage permissions trước khi artisan chạy
sudo /usr/local/bin/fix-minhan-build 2>/dev/null || true
# Xóa config cache cũ — bắt buộc trước migrate để artisan đọc đúng .env
$PHP artisan config:clear
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

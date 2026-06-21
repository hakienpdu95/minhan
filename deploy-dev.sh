#!/bin/bash
# deploy-dev.sh — thuchocvn.vn (dev/staging) deploy
# Dựa trên deploy.sh (production), điều chỉnh cho môi trường dev: auto-migrate
# + auto-build bật mặc định (ngược với production) vì dev cần lặp nhanh, và
# production chỉ tắt 2 thứ này do lịch sử riêng (migrations table lệch DB,
# nhầm vite config). Devminhan là deploy mới, không có lý do thừa hưởng vấn đề đó.
#
# Chạy thủ công: bash deploy-dev.sh
# Tắt migrate cho 1 lần chạy cụ thể: bash deploy-dev.sh --skip-migrations
# Tắt build cho 1 lần chạy cụ thể:   bash deploy-dev.sh --skip-build
set -euo pipefail

APP_DIR="/var/www/devminhan"
PHP="/usr/bin/php8.5"
BRANCH="dev"

cd "$APP_DIR"

# ── 0. Kéo code mới + re-exec ─────────────────────────────────
# Giống deploy.sh: script tự git reset đè lên chính file đang chạy, nên phải
# re-exec để đảm bảo phần còn lại luôn chạy đúng phiên bản mới nhất trên đĩa
# (xem deploy.sh để biết chi tiết tại sao việc này bắt buộc).
if [ -z "${DEPLOY_REEXEC:-}" ]; then
    echo "[$(date '+%H:%M:%S')] [0/7] Pulling latest code (branch: $BRANCH)..."
    [ -f "$APP_DIR/.env" ] && cp "$APP_DIR/.env" /tmp/.env.devminhan-deploy.bak
    git fetch origin
    git reset --hard "origin/$BRANCH"
    [ -f /tmp/.env.devminhan-deploy.bak ] && mv /tmp/.env.devminhan-deploy.bak "$APP_DIR/.env"
    echo "[$(date '+%H:%M:%S')] ✓ Code updated → $(git log --oneline -1)"

    export DEPLOY_REEXEC=1
    exec bash "$APP_DIR/deploy-dev.sh" "$@"
fi

# ── Flags ──────────────────────────────────────────────────────
SKIP_MIGRATIONS=false
SKIP_BUILD=false
for arg in "$@"; do
    case "$arg" in
        --skip-migrations) SKIP_MIGRATIONS=true ;;
        --skip-build)      SKIP_BUILD=true ;;
    esac
done

log() { echo "[$(date '+%H:%M:%S')] $*"; }
ok()  { echo "[$(date '+%H:%M:%S')] ✓ $*"; }
err() { echo "[$(date '+%H:%M:%S')] ✗ $*" >&2; }

log "═══════════════════════════════════════"
log "  Deploy thuchocvn.vn (dev) — $(date '+%Y-%m-%d %H:%M:%S')"
log "  Branch: $BRANCH | Commit: $(git log --oneline -1) | Skip migrations: $SKIP_MIGRATIONS | Skip build: $SKIP_BUILD"
log "═══════════════════════════════════════"

# ── 1. PHP dependencies ────────────────────────────────────────
log "[1/7] Installing PHP dependencies..."
composer install --no-interaction --quiet
ok "Composer done"

# ── 2. Frontend build ──────────────────────────────────────────
# QUAN TRỌNG: dùng đúng vite.config.backend.js — toàn bộ view trong app dùng
# group 'build/backend' (public/build/backend/manifest.json), KHÔNG phải
# `npm run build` mặc định (xem deploy.sh để biết lý do production từng vỡ
# vì lẫn lộn 2 config này — public/build/backend/ bị xoá nhầm khi chạy
# `npm run build` do Vite emptyOutDir=true).
if [ "$SKIP_BUILD" = "true" ]; then
    log "[2/7] Skipping frontend build (--skip-build)"
else
    log "[2/7] Building frontend assets (vite.config.backend.js)..."
    sudo /usr/local/bin/fix-devminhan-build 2>/dev/null || true
    npm ci --prefer-offline --silent
    npx vite build --config vite.config.backend.js
    ok "Frontend built → public/build/backend/"
fi

# ── 3. Maintenance mode ────────────────────────────────────────
log "[3/7] Enabling maintenance mode..."
sudo /usr/local/bin/fix-devminhan-build 2>/dev/null || true
$PHP artisan config:clear
$PHP artisan down --retry=10
trap '$PHP artisan up; err "Deploy failed — maintenance mode disabled"' ERR

# ── 4. Migration ────────────────────────────────────────────────
if [ "$SKIP_MIGRATIONS" = "true" ]; then
    log "[4/7] Skipping migrations (--skip-migrations)"
else
    log "[4/7] Running database migrations..."
    $PHP artisan migrate --force
    ok "Migrations done"
fi

# ── 5. Rebuild cache ────────────────────────────────────────────
log "[5/7] Rebuilding application cache..."
$PHP artisan config:clear  && $PHP artisan config:cache
$PHP artisan route:clear   && $PHP artisan route:cache
$PHP artisan view:clear    && $PHP artisan view:cache
$PHP artisan event:clear   && $PHP artisan event:cache
ok "Cache rebuilt"

# ── 6. Reload PHP-FPM (xóa opcache) ──────────────────────────────
log "[6/7] Reloading PHP-FPM..."
if sudo systemctl reload php8.5-fpm 2>/dev/null; then
    ok "PHP-FPM reloaded (opcache cleared)"
else
    err "Không reload được PHP-FPM — cần cấu hình sudoers cho lệnh: systemctl reload php8.5-fpm"
fi

$PHP artisan up
trap - ERR

# ── 7. Khởi động lại workers ──────────────────────────────────────
log "[7/7] Restarting workers..."
sudo supervisorctl restart devminhan-horizon  > /dev/null 2>&1 || err "Không restart được devminhan-horizon — cần cấu hình supervisor + sudoers trên VPS"
sudo supervisorctl restart devminhan-reverb   > /dev/null 2>&1 || err "Không restart được devminhan-reverb — cần cấu hình supervisor + sudoers trên VPS"
ok "Workers restarted"

log ""
log "✅ Deploy hoàn tất — $(date '+%Y-%m-%d %H:%M:%S')"
log "   Commit: $(git log --oneline -1)"

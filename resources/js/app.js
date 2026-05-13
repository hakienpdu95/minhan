/**
 * resources/js/app.js
 * ────────────────────────────────────────────────────────────────────
 * CORE entry point — tải trên MỌI trang backend.
 *
 * Thứ tự khởi tạo quan trọng:
 *  1. jQuery     → window.$ / window.jQuery (cần trước Alpine & mọi script)
 *  2. Alpine.js  → window.Alpine (cần start() TRƯỚC DOMContentLoaded)
 *  3. iconify    → web component tự register
 *  4. admin-shell→ sidebar, dropdown, theme, shortcuts
 * ────────────────────────────────────────────────────────────────────
 */

/* ── 1. jQuery → global ─────────────────────────────────────────────
 * Expose $ và jQuery lên window để:
 *  · Script inline trong blade dùng được ngay
 *  · jQuery plugins (DataTables, etc.) tìm thấy jQuery
 */
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

/* ── 2. Alpine.js ───────────────────────────────────────────────────
 *
 * Pattern chuẩn Alpine v3:
 *  - Expose window.Alpine TRƯỚC khi start() để blade có thể gọi
 *    Alpine.data(), Alpine.store(), Alpine.directive() ở script inline
 *    mà chạy TRƯỚC DOMContentLoaded
 *  - alpine:init event để đăng ký data/store/directive an toàn
 *  - start() KHÔNG gọi trong DOMContentLoaded — Alpine tự quản lý
 *
 * Không dùng @alpinejs/collapse, focus, persist... vì không có trong
 * package.json — thêm sau nếu cần bằng npm install @alpinejs/...
 */
import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Hook: đăng ký Alpine components/stores ở đây hoặc ở blade script
 * chạy trước window.Alpine.start().
 *
 * Ví dụ trong blade:
 *   <script>
 *     document.addEventListener('alpine:init', () => {
 *       Alpine.data('dropdown', () => ({ open: false }))
 *     })
 *   </script>
 */
document.addEventListener('alpine:init', () => {
    /* ── Global Alpine stores ── */
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('ap_sidebar_collapsed') === '1',
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('ap_sidebar_collapsed', this.collapsed ? '1' : '0');
        },
    });

    Alpine.store('theme', {
        dark: localStorage.getItem('ap_theme') === 'dark',
        toggle() {
            this.dark = !this.dark;
            document.documentElement.setAttribute('data-theme', this.dark ? 'dark' : 'light');
            localStorage.setItem('ap_theme', this.dark ? 'dark' : 'light');
        },
        init() {
            document.documentElement.setAttribute('data-theme', this.dark ? 'dark' : 'light');
        },
    });

    /* ── Global Alpine data components ── */
    Alpine.data('dropdown', (options = {}) => ({
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; },
        /* đóng khi click outside — dùng @click.away="close()" trong blade */
    }));

    Alpine.data('confirmDelete', (options = {}) => ({
        open: false,
        targetUrl: '',
        message: options.message ?? 'Bạn có chắc muốn xoá mục này?',
        show(url) { this.targetUrl = url; this.open = true; },
        hide() { this.open = false; this.targetUrl = ''; },
        confirm() {
            if (this.targetUrl) {
                /* Gửi DELETE form — hoặc fetch tuỳ logic */
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = this.targetUrl;
                form.innerHTML = `
                    <input type="hidden" name="_token"  value="${document.querySelector('meta[name=csrf-token]')?.content}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        },
    }));
});

/* Alpine.start() phải gọi SAU khi đăng ký tất cả plugins/components */
Alpine.start();

/* ── 3. Iconify web component ───────────────────────────────────────
 * Self-registers as <iconify-icon> custom element.
 * Tải icon on-demand qua CDN API — không cần bundle icon files.
 *
 * Dùng trong blade:
 *   <iconify-icon icon="mdi:home"></iconify-icon>
 *   <iconify-icon icon="heroicons:user" width="20"></iconify-icon>
 */
import 'iconify-icon';

/* ── 4. Admin shell ─────────────────────────────────────────────────
 * Sidebar collapse/mobile, dropdown fallback (JS thuần — Alpine
 * là opt-in, không bắt buộc cho mọi element), theme, shortcuts.
 */
import { initAdminShell } from './admin-shell.js';

document.addEventListener('DOMContentLoaded', () => {
    initAdminShell();
});

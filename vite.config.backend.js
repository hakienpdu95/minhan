/**
 * vite.config.backend.js
 *
 * Laravel 13 | Vite 8 | Tailwind 4 | DaisyUI 5 | Alpine 3
 * ─────────────────────────────────────────────────────────────────────
 *
 * CHIẾN LƯỢC BUNDLE
 * ┌────────────────────────────────────────────────────────────────┐
 * │ CORE  (tải trên MỌI trang backend)                             │
 * │  · app.css  → Tailwind 4 + DaisyUI 5 + layout admin CSS       │
 * │  · app.js   → jQuery global, Alpine 3, iconify, admin shell   │
 * ├────────────────────────────────────────────────────────────────┤
 * │ MODULES  (tải riêng theo trang — @vite() trong blade)          │
 * │  · toastify    toast noti          (nhẹ, nhiều trang)          │
 * │  · datatables  DataTables.net v2   (trang bảng dữ liệu)       │
 * │  · tabulator   Tabulator v6        (bảng nâng cao)             │
 * │  · filepond    FilePond + plugins  (trang upload)              │
 * │  · flatpickr   date/time picker    (form)                      │
 * │  · jodit       rich-text editor    (~500 KB, lazy)             │
 * │  · tom-select  select/autocomplete (form)                      │
 * │  · swiper      carousel/slider                                 │
 * │  · qrcode      QR code generator                               │
 * └────────────────────────────────────────────────────────────────┘
 *
 * LỆNH:
 *   npm run dev    → vite --config vite.config.backend.js
 *   npm run build  → vite build --config vite.config.backend.js
 *
 * BLADE:
 *   @vite(['resources/css/app.css', 'resources/js/app.js'])
 *   @vite(['resources/js/modules/datatables.js'])
 */

import { defineConfig } from 'vite';
import laravel          from 'laravel-vite-plugin';
import tailwindcss      from '@tailwindcss/vite';
import path             from 'path';

/* ─── Tên file JS output (entry points) ─── */
const JS_OUTPUT = {
  'app':        'assets/app.[hash].js',
  'toastify':   'assets/toastify.[hash].js',
  'datatables': 'assets/datatables.[hash].js',
  'tabulator':  'assets/tabulator.[hash].js',
  'filepond':   'assets/filepond.[hash].js',
  'flatpickr':  'assets/flatpickr.[hash].js',
  'jodit':      'assets/jodit.[hash].js',
  'tom-select': 'assets/tom-select.[hash].js',
  'swiper':     'assets/swiper.[hash].js',
  'qrcode':     'assets/qrcode.[hash].js',
};

/* ─── Tên file CSS output ─── */
const CSS_OUTPUT = {
  'app.css':                       'assets/app.[hash].css',
  'filepond.css':                  'assets/filepond.[hash].css',
  'flatpickr.css':                 'assets/flatpickr.[hash].css',
  'jodit.min.css':                 'assets/jodit.[hash].css',
  'tom-select.css':                'assets/tom-select.[hash].css',
  'swiper.css':                    'assets/swiper.[hash].css',
  'dataTables.dataTables.min.css': 'assets/datatables.[hash].css',
};

export default defineConfig(({ mode }) => {
  const isProd = mode === 'production';

  return {

    /* ── Base URL ──────────────────────────────────────── */
    base: isProd ? '/build/backend/' : '/',

    /* ── Plugins ───────────────────────────────────────── */
    plugins: [
      /*
       * Tailwind CSS v4 (vite-native, không cần postcss/tailwind.config.js).
       * Đặt TRƯỚC laravel() để CSS pipeline đúng thứ tự.
       */
      tailwindcss(),

      /* Laravel Vite Plugin */
      laravel({
        input: [
          /* CORE */
          'resources/css/app.css',
          'resources/js/app.js',
          /* MODULES */
          'resources/js/modules/toastify.js',
          'resources/js/modules/datatables.js',
          'resources/js/modules/tabulator.js',
          'resources/js/modules/filepond.js',
          'resources/js/modules/flatpickr.js',
          'resources/js/modules/jodit.js',
          'resources/js/modules/tom-select.js',
          'resources/js/modules/swiper.js',
          'resources/js/modules/qrcode.js',
        ],
        refresh: [
          'resources/views/**/*.blade.php',
          'resources/css/**/*.css',
          'resources/js/**/*.js',
          'routes/**/*.php',
        ],
        buildDirectory: 'build/backend',
        modulePreload:  { polyfill: true },
      }),

      /*
       * Jodit side-effects — tắt tree-shaking để không lỗi runtime.
       */
      {
        name: 'no-treeshake-jodit',
        transform(_code, id) {
          if (id.includes('node_modules/jodit')) {
            return { moduleSideEffects: 'no-treeshake' };
          }
        },
      },
    ],

    /* ── Aliases ───────────────────────────────────────── */
    resolve: {
      alias: {
        '@':        path.resolve(__dirname, 'resources'),
        '@css':     path.resolve(__dirname, 'resources/css'),
        '@js':      path.resolve(__dirname, 'resources/js'),
        '@modules': path.resolve(__dirname, 'resources/js/modules'),
        '@fonts':   path.resolve(__dirname, 'resources/webfonts'),
      },
    },

    /* ── Build ─────────────────────────────────────────── */
    build: {
      outDir:               'public/build/backend',
      manifest:             'manifest.json',
      emptyOutDir:          true,
      sourcemap:            false,
      reportCompressedSize: true,
      chunkSizeWarningLimit: 500,

      /*
       * Vite 8 dùng rolldown + oxc làm bundler/minifier mặc định.
       *  · 'oxc'    = built-in Vite 8, nhanh, không cần cài thêm  ← DÙNG
       *  · 'esbuild'= deprecated Vite 8, gây lỗi transformWithEsbuild  ✗
       *  · 'terser' = optional, cần: npm install -D terser             ✗
       */
      minify:    'oxc',
      cssMinify: 'oxc',
      cssCodeSplit: true,

      rollupOptions: {
        output: {

          /* Entry file names */
          entryFileNames: (chunk) =>
            JS_OUTPUT[chunk.name] ?? `assets/${chunk.name}.[hash].js`,

          /* Shared chunk names */
          chunkFileNames: (chunk) => {
            const map = {
              'vendor-jquery':     'assets/vendor-jquery.[hash].js',
              'vendor-alpine':     'assets/vendor-alpine.[hash].js',
              'vendor-daisyui':    'assets/vendor-daisyui.[hash].js',
              'vendor-iconify':    'assets/vendor-iconify.[hash].js',
              'vendor-jodit':      'assets/vendor-jodit.[hash].js',
              'vendor-swiper':     'assets/vendor-swiper.[hash].js',
              'vendor-datatables': 'assets/vendor-datatables.[hash].js',
              'vendor-tabulator':  'assets/vendor-tabulator.[hash].js',
              'vendor-filepond':   'assets/vendor-filepond.[hash].js',
              'vendor-tom-select': 'assets/vendor-tom-select.[hash].js',
              'vendor-flatpickr':  'assets/vendor-flatpickr.[hash].js',
              'vendor-toastify':   'assets/vendor-toastify.[hash].js',
              'vendor-qrcode':     'assets/vendor-qrcode.[hash].js',
            };
            return map[chunk.name] ?? `assets/chunk-${chunk.name}.[hash].js`;
          },

          /* CSS + fonts + images */
          assetFileNames: (asset) => {
            const name = asset.name ?? '';
            if (/\.(woff2?|ttf|eot)$/.test(name))
              return 'assets/fonts/[name].[hash].[ext]';
            if (/\.svg$/.test(name) && /font|icon/i.test(name))
              return 'assets/fonts/[name].[hash].[ext]';
            if (/\.(png|jpe?g|gif|webp|avif|ico)$/.test(name))
              return 'assets/images/[name].[hash].[ext]';
            return CSS_OUTPUT[name] ?? 'assets/[name].[hash].[ext]';
          },

          /*
           * manualChunks: mỗi vendor thư viện → 1 chunk riêng.
           * → Browser cache độc lập từng thư viện.
           * → Chỉ thay đổi app code → vendor chunks KHÔNG re-download.
           */
          manualChunks(id) {
            if (id.includes('node_modules/alpinejs'))           return 'vendor-alpine';
            if (id.includes('node_modules/jquery'))             return 'vendor-jquery';
            if (id.includes('node_modules/daisyui'))            return 'vendor-daisyui';
            if (id.includes('node_modules/iconify-icon'))       return 'vendor-iconify';
            if (id.includes('node_modules/jodit'))              return 'vendor-jodit';
            if (id.includes('node_modules/swiper'))             return 'vendor-swiper';
            if (id.includes('node_modules/datatables.net'))     return 'vendor-datatables';
            if (id.includes('node_modules/tabulator-tables'))   return 'vendor-tabulator';
            if (id.includes('node_modules/filepond'))           return 'vendor-filepond';
            if (id.includes('node_modules/tom-select'))         return 'vendor-tom-select';
            if (id.includes('node_modules/flatpickr'))          return 'vendor-flatpickr';
            if (id.includes('node_modules/toastify-js'))        return 'vendor-toastify';
            if (id.includes('node_modules/qrcode-generator'))   return 'vendor-qrcode';
          },
        },
      },
    },

    /* ── Dev server ────────────────────────────────────── */
    server: {
      /*
       * Dùng port 5174 để tránh conflict với Vite frontend (5173).
       * laravel-vite-plugin tự detect port này và inject đúng vào blade.
       * Nếu port bị chiếm: đổi thành 5175, 5176...
       */
      port:        5174,
      strictPort:  true,
      hmr:  { host: 'localhost' },
      watch:{ usePolling: false },
    },

    /* ── optimizeDeps (pre-bundle trong dev) ───────────── */
    optimizeDeps: {
      /* Pre-bundle: chỉ CORE packages → dev start nhanh */
      include: [
        'jquery',
        'alpinejs',
        'toastify-js',
        'iconify-icon',
      ],
      /* Không pre-bundle: load lazy per-page */
      exclude: [
        'jodit',
        'swiper',
        'tabulator-tables',
        'datatables.net',
        'datatables.net-dt',
        'filepond',
        'filepond-plugin-image-preview',
        'filepond-plugin-file-validate-size',
        'filepond-plugin-file-rename',
        'filepond-plugin-image-exif-orientation',
        'flatpickr',
        'tom-select',
        'qrcode-generator',
      ],
    },

  };
});

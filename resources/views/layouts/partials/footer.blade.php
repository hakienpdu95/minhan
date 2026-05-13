{{-- ================================================================
     FOOTER  — resources/views/layouts/partials/footer.blade.php
     ================================================================ --}}
<footer class="footer footer-center bg-base-100 border-t border-base-300 px-6 py-3">
    <div class="flex flex-col sm:flex-row items-center justify-between w-full gap-1">
        <p class="text-xs text-base-content/50">
            &copy; {{ date('Y') }} <strong>{{ config('app.name', 'AdminPanel') }}</strong>. All rights reserved.
        </p>
        <p class="text-xs text-base-content/40">
            Laravel {{ app()->version() }} &bull; PHP {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }}
        </p>
    </div>
</footer>

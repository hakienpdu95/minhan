<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'AdminPanel') }}</title>

    {{-- Core: Tailwind + DaisyUI + layout CSS + app JS --}}
    @viteBackend(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Module CSS/JS theo trang (khai báo trong child view) --}}
    {{-- Ví dụ trang create product: --}}
    {{-- @viteBackend(['resources/css/filepond.css', 'resources/js/modules/filepond.js']) --}}

    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-shell">
    @include('layouts.partials.sidebar')
    <div class="main-area" id="mainArea">
        @include('layouts.partials.header')
        <main class="page-content">
            @hasSection('breadcrumb')
                @yield('breadcrumb')
            @endif
            @yield('content')
        </main>
        @include('layouts.partials.footer')
    </div>
</div>

<div id="toast-container" class="toast-container"></div>
@stack('scripts')
</body>
</html>

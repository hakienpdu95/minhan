{{-- resources/views/layouts/partials/header.blade.php --}}
<header class="topbar">
    <div class="topbar-left">
        {{-- Mobile hamburger --}}
        <button class="icon-btn" id="mobileSidebarBtn" title="Menu" style="display:none">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        {{-- Desktop collapse --}}
        <button class="icon-btn" id="desktopCollapseBtn" title="Thu gọn sidebar">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div class="topbar-center">
        <div class="search-box">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#94a3b8;flex-shrink:0"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" placeholder="Tìm kiếm...">
            <kbd>⌘K</kbd>
        </div>
    </div>

    <div class="topbar-right">

        {{-- Theme --}}
        <button class="icon-btn" id="themeBtn" title="Đổi giao diện">
            <svg id="iconSun" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M18.66 5.34l1.41-1.41"/></svg>
            <svg id="iconMoon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        </button>

        {{-- Notifications --}}
        <div class="dd-wrap">
            <button class="icon-btn" id="notifBtn" title="Thông báo">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="badge-dot"></span>
            </button>
            <div class="dd-panel notif-panel" id="notifPanel">
                <div class="notif-hdr"><span>Thông báo</span><a href="#">Đọc tất cả</a></div>
                <a class="notif-item" href="#">
                    <div class="notif-icon" style="background:#3b82f6"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <div class="notif-txt"><p>Đơn hàng mới #1024</p><small>2 phút trước</small></div>
                </a>
                <a class="notif-item" href="#">
                    <div class="notif-icon" style="background:#f59e0b"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/></svg></div>
                    <div class="notif-txt"><p>Sản phẩm sắp hết hàng</p><small>1 giờ trước</small></div>
                </a>
                <a class="notif-item" href="#">
                    <div class="notif-icon" style="background:#22c55e"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    <div class="notif-txt"><p>Thanh toán thành công</p><small>3 giờ trước</small></div>
                </a>
            </div>
        </div>

        {{-- User --}}
        <div class="dd-wrap">
            <button class="avatar-btn" id="userBtn">
                <img src="https://api.dicebear.com/9.x/initials/svg?seed={{ urlencode(auth()->user()->name ?? 'Admin') }}&backgroundColor=6366f1&fontFamily=Arial&fontSize=40&fontWeight=700" alt="Avatar">
                <span class="av-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                <svg style="width:12px;height:12px;color:#94a3b8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="dd-panel user-panel" id="userPanel">
                <div class="user-email">{{ auth()->user()->email ?? 'admin@example.com' }}</div>
                <a href="#" class="u-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Hồ sơ cá nhân</a>
                <a href="#" class="u-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Cài đặt</a>
                <div class="u-divider"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="u-item danger"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Đăng xuất</button>
                </form>
            </div>
        </div>

    </div>
</header>

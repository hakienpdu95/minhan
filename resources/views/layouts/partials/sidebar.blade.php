<aside class="sidebar" id="sidebar">

    <div class="brand">
        <div class="brand-logo">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
        </div>
        <span class="brand-name">{{ config('app.name', 'AdminPanel') }}</span>
    </div>

    <nav class="nav-wrap">
        <p class="section-title">Chính</p>
        <div class="nav-group">

            <a href="{{ route('backend.dashboard') }}"
               class="nav-link {{ request()->routeIs('backend.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="nav-label">Dashboard</span>
            </a>

            <details {{ request()->routeIs('backend.products.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.products.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/></svg>
                    <span class="nav-label">Sản phẩm</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.products.index') }}" class="sub-link {{ request()->routeIs('backend.products.index') ? 'active' : '' }}">Danh sách sản phẩm</a>
                    <a href="{{ route('backend.products.create') }}" class="sub-link {{ request()->routeIs('backend.products.create') ? 'active' : '' }}">Thêm sản phẩm</a>
                    <a href="#" class="sub-link">Thùng rác</a>
                </div>
            </details>

            <details {{ request()->routeIs('backend.orders.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.orders.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="nav-label">Đơn hàng</span>
                    <span class="nav-badge">12</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.orders.index') }}" class="sub-link {{ request()->routeIs('backend.orders.index') ? 'active' : '' }}">Tất cả đơn hàng</a>
                    <a href="#" class="sub-link">Chờ xử lý <span class="sub-badge">5</span></a>
                    <a href="#" class="sub-link">Đang giao hàng</a>
                    <a href="#" class="sub-link">Hoàn thành</a>
                </div>
            </details>

            <details {{ request()->routeIs('backend.customers.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.customers.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="nav-label">Khách hàng</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.customers.index') }}" class="sub-link {{ request()->routeIs('backend.customers.index') ? 'active' : '' }}">Danh sách khách hàng</a>
                    <a href="{{ route('backend.customers.create') }}" class="sub-link {{ request()->routeIs('backend.customers.create') ? 'active' : '' }}">Thêm khách hàng</a>
                    <a href="#" class="sub-link">Nhóm khách hàng</a>
                </div>
            </details>

            <details {{ request()->routeIs('backend.categories.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.categories.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span class="nav-label">Danh mục</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.categories.index') }}" class="sub-link {{ request()->routeIs('backend.categories.index') ? 'active' : '' }}">Danh sách danh mục</a>
                    <a href="{{ route('backend.categories.create') }}" class="sub-link {{ request()->routeIs('backend.categories.create') ? 'active' : '' }}">Thêm danh mục</a>
                </div>
            </details>

        </div>

        @can('survey.view')
        <details {{ request()->routeIs('backend.surveys.*') ? 'open' : '' }}>
            <summary class="nav-summary {{ request()->routeIs('backend.surveys.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span class="nav-label">Khảo sát</span>
                <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
            </summary>
            <div class="sub-menu">
                <a href="{{ route('backend.surveys.index') }}" class="sub-link {{ request()->routeIs('backend.surveys.index') ? 'active' : '' }}">Danh sách khảo sát</a>
                @can('survey.create')
                <a href="{{ route('backend.surveys.create') }}" class="sub-link {{ request()->routeIs('backend.surveys.create') ? 'active' : '' }}">Tạo khảo sát</a>
                @endcan
            </div>
        </details>
        @endcan

        @if(auth()->user()?->hasAnyPermission(['assessment.view','assessment.config','assessment.results']))
        <details {{ request()->routeIs('assessments.*') ? 'open' : '' }}>
            <summary class="nav-summary {{ request()->routeIs('assessments.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="nav-label">Chấm điểm</span>
                <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
            </summary>
            <div class="sub-menu">
                @can('assessment.view')
                <a href="{{ route('assessments.index') }}"
                   class="sub-link {{ request()->routeIs('assessments.index') ? 'active' : '' }}">
                    Danh sách Assessment
                </a>
                @endcan
                @can('assessment.config')
                <a href="{{ route('assessments.create') }}"
                   class="sub-link {{ request()->routeIs('assessments.create') ? 'active' : '' }}">
                    Tạo Assessment mới
                </a>
                @endcan
            </div>
        </details>
        @endif

        @if(auth()->user()?->hasAnyPermission(['leads.view_all','leads.view_assigned','leads.view_source']))
        <p class="section-title" style="margin-top:16px;">CRM</p>
        <div class="nav-group">

            <details {{ request()->routeIs('lead.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('lead.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span class="nav-label">Cơ hội (Lead)</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('lead.index') }}" class="sub-link {{ request()->routeIs('lead.index') ? 'active' : '' }}">Danh sách cơ hội</a>
                    @can('leads.create')
                    <a href="{{ route('lead.create') }}" class="sub-link {{ request()->routeIs('lead.create') ? 'active' : '' }}">Thêm cơ hội</a>
                    @endcan
                    @can('leads.manage_pipeline')
                    <a href="{{ route('lead-pipeline-stage.index') }}" class="sub-link {{ request()->routeIs('lead-pipeline-stage.*') ? 'active' : '' }}">Pipeline stages</a>
                    @endcan
                    @can('leads.manage_sources')
                    <a href="{{ route('lead-source.index') }}" class="sub-link {{ request()->routeIs('lead-source.*') ? 'active' : '' }}">Nguồn cơ hội</a>
                    @endcan
                    @can('leads.manage_tags')
                    <a href="{{ route('lead.tags.index') }}" class="sub-link {{ request()->routeIs('lead.tags.*') ? 'active' : '' }}">Tags</a>
                    @endcan
                </div>
            </details>

        </div>
        @endif

        <p class="section-title" style="margin-top:16px;">Tổ chức</p>
        <div class="nav-group">

            <details {{ request()->routeIs('backend.organizations.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.organizations.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="nav-label">Tổ chức</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.organizations.index') }}" class="sub-link {{ request()->routeIs('backend.organizations.index') ? 'active' : '' }}">Danh sách tổ chức</a>
                    <a href="{{ route('backend.organizations.create') }}" class="sub-link {{ request()->routeIs('backend.organizations.create') ? 'active' : '' }}">Thêm tổ chức</a>
                </div>
            </details>

            @can('viewAny', \Modules\Branch\Models\Branch::class)
            <details {{ request()->routeIs('backend.branches.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.branches.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="nav-label">Chi nhánh</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.branches.index') }}" class="sub-link {{ request()->routeIs('backend.branches.index') ? 'active' : '' }}">Danh sách chi nhánh</a>
                    @can('create', \Modules\Branch\Models\Branch::class)
                    <a href="{{ route('backend.branches.create') }}" class="sub-link {{ request()->routeIs('backend.branches.create') ? 'active' : '' }}">Thêm chi nhánh</a>
                    @endcan
                </div>
            </details>
            @endcan

            @can('viewAny', \Modules\Department\Models\Department::class)
            <details {{ request()->routeIs('backend.departments.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.departments.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span class="nav-label">Phòng ban</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.departments.index') }}" class="sub-link {{ request()->routeIs('backend.departments.index') ? 'active' : '' }}">Danh sách phòng ban</a>
                    @can('create', \Modules\Department\Models\Department::class)
                    <a href="{{ route('backend.departments.create') }}" class="sub-link {{ request()->routeIs('backend.departments.create') ? 'active' : '' }}">Thêm phòng ban</a>
                    @endcan
                </div>
            </details>
            @endcan

            @can('viewAny', \Modules\JobTitle\Models\JobTitle::class)
            <details {{ request()->routeIs('backend.job-titles.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.job-titles.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    <span class="nav-label">Chức danh</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.job-titles.index') }}" class="sub-link {{ request()->routeIs('backend.job-titles.index') ? 'active' : '' }}">Danh sách chức danh</a>
                    @can('create', \Modules\JobTitle\Models\JobTitle::class)
                    <a href="{{ route('backend.job-titles.create') }}" class="sub-link {{ request()->routeIs('backend.job-titles.create') ? 'active' : '' }}">Thêm chức danh</a>
                    @endcan
                </div>
            </details>
            @endcan

            @can('viewAny', \Modules\Employee\Models\Employee::class)
            <details {{ request()->routeIs('backend.employees.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.employees.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="nav-label">Nhân viên</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.employees.index') }}" class="sub-link {{ request()->routeIs('backend.employees.index') ? 'active' : '' }}">Danh sách nhân viên</a>
                    @can('create', \Modules\Employee\Models\Employee::class)
                    <a href="{{ route('backend.employees.create') }}" class="sub-link {{ request()->routeIs('backend.employees.create') ? 'active' : '' }}">Thêm nhân viên</a>
                    @endcan
                </div>
            </details>
            @endcan

            @can('viewAny', \Modules\RoleScope\Models\UserRoleScope::class)
            <details {{ request()->routeIs('backend.role-scopes.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.role-scopes.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span class="nav-label">Phân quyền phạm vi</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.role-scopes.index') }}" class="sub-link {{ request()->routeIs('backend.role-scopes.index') ? 'active' : '' }}">Danh sách phân quyền</a>
                    @can('create', \Modules\RoleScope\Models\UserRoleScope::class)
                    <a href="{{ route('backend.role-scopes.create') }}" class="sub-link {{ request()->routeIs('backend.role-scopes.create') ? 'active' : '' }}">Cấp quyền mới</a>
                    @endcan
                </div>
            </details>
            @endcan

        </div>

        <p class="section-title" style="margin-top:16px;">Tài khoản</p>
        <div class="nav-group">

            <details {{ request()->routeIs('backend.users.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.users.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="nav-label">Tài khoản</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('backend.users.index') }}" class="sub-link {{ request()->routeIs('backend.users.index') ? 'active' : '' }}">Danh sách tài khoản</a>
                    <a href="{{ route('backend.users.create') }}" class="sub-link {{ request()->routeIs('backend.users.create') ? 'active' : '' }}">Thêm tài khoản</a>
                </div>
            </details>

        </div>

        <p class="section-title" style="margin-top:16px;">Hệ thống</p>
        <div class="nav-group">

            @can('activitylog.view')
            <details {{ request()->routeIs('activitylog.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('activitylog.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="nav-label">Activity Log</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('activitylog.index') }}"
                       class="sub-link {{ request()->routeIs('activitylog.index') ? 'active' : '' }}">
                       Danh sách log
                    </a>
                    @can('activitylog.manage_alerts')
                    <a href="{{ route('activitylog.alert-rules.index') }}"
                       class="sub-link {{ request()->routeIs('activitylog.alert-rules.*') ? 'active' : '' }}">
                       Alert Rules
                    </a>
                    @endcan
                </div>
            </details>
            @endcan

            @can(\App\Enums\PermissionEnum::WORKFLOW_MONITOR->value)
            <details {{ request()->routeIs('workflows.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('workflows.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="nav-label">Workflow</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="{{ route('workflows.index') }}"
                       class="sub-link {{ request()->routeIs('workflows.index') ? 'active' : '' }}">
                        Danh sách workflow
                    </a>
                    @can(\App\Enums\PermissionEnum::WORKFLOW_EDIT->value)
                    <a href="{{ route('workflows.create') }}"
                       class="sub-link {{ request()->routeIs('workflows.create') ? 'active' : '' }}">
                        Tạo workflow mới
                    </a>
                    @endcan
                </div>
            </details>
            @endcan

            <details {{ request()->routeIs('backend.settings.*') ? 'open' : '' }}>
                <summary class="nav-summary {{ request()->routeIs('backend.settings.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="nav-label">Cài đặt</span>
                    <svg class="nav-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="sub-menu">
                    <a href="#" class="sub-link">Chung</a>
                    <a href="#" class="sub-link">Thanh toán</a>
                    <a href="#" class="sub-link">Vận chuyển</a>
                    <a href="#" class="sub-link">Email</a>
                </div>
            </details>

            <a href="#" class="nav-link {{ request()->routeIs('backend.reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="nav-label">Báo cáo</span>
            </a>

        </div>
    </nav>

    <div class="user-card">
        <div class="user-card-inner">
            <img src="https://api.dicebear.com/9.x/initials/svg?seed={{ urlencode(auth()->user()->name ?? 'Admin') }}&backgroundColor=6366f1&fontFamily=Arial&fontSize=40&fontWeight=700" alt="Avatar">
            <div class="user-info">
                <p>{{ auth()->user()->name ?? 'Admin User' }}</p>
                <small>{{ auth()->user()->email ?? 'admin@example.com' }}</small>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="user-logout" title="Đăng xuất">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>

</aside>

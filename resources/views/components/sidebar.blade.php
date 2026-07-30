{{-- ═══════════════════════════════════════════════════════════════════════
     SIDEBAR — Full-height collapsible panel
     Toggle via: document.body.classList.toggle('sidebar-collapsed')
═══════════════════════════════════════════════════════════════════════ --}}
<aside class="app-sidebar" id="appSidebar">

    {{-- ── Brand Header ──────────────────────────────────────────────── --}}
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="sidebar-brand-link">
            <div class="sidebar-brand-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <span class="sidebar-brand-text">AccessHub</span>
        </a>
    </div>

    {{-- ── Nav Scroll Area ───────────────────────────────────────────── --}}
    <div class="sidebar-nav">

    @if(!Auth::user()->requires_onboarding)

    {{-- MAIN --}}
    <div class="sidebar-section-label">Main</div>

    <a href="{{ route('dashboard') }}"
       class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
       data-sidebar-tooltip="Dashboard">
        <i class="bi bi-grid-fill sidebar-icon"></i>
        <span class="sidebar-item-text">Dashboard</span>
    </a>

    {{-- MODULES --}}
    <div class="sidebar-section-label">Modules</div>

    {{-- UAM Collapse --}}
    <a href="#uamCollapse"
       data-bs-toggle="collapse"
       class="sidebar-nav-item {{ request()->routeIs('access-matrix.*') ? 'active' : 'collapsed' }}"
       role="button"
       aria-expanded="{{ request()->routeIs('access-matrix.*') ? 'true' : 'false' }}"
       aria-controls="uamCollapse"
       data-sidebar-tooltip="User Access Matrix">
        <i class="bi bi-table sidebar-icon"></i>
        <span class="sidebar-item-text" style="flex:1;line-height:1.2;">User Access Matrix</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </a>

    <div class="collapse {{ request()->routeIs('access-matrix.*') ? 'show' : '' }}" id="uamCollapse">
        <div class="sidebar-sub-group">
            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'pic_ao')
            <a href="{{ route('access-matrix.request.index') }}"
               class="sidebar-sub-item {{ request()->routeIs('access-matrix.request.*') || (request()->routeIs('access-matrix.sap') && request('source') == 'request') ? 'active' : '' }}">
                Request Matrix
            </a>
            @endif
            @if(Auth::user()->role === 'manager' || Auth::user()->isAdmin())
            <a href="{{ route('access-matrix.uam-request.index') }}"
               class="sidebar-sub-item {{ request()->routeIs('access-matrix.uam-request.*') || (request()->routeIs('access-matrix.sap') && request('source') == 'approval') ? 'active' : '' }}">
                Accept
            </a>
            @endif
            @if(Auth::user()->role === 'ao' || Auth::user()->isAdmin())
            <a href="{{ route('access-matrix.approval.index') }}"
               class="sidebar-sub-item {{ request()->routeIs('access-matrix.approval.*') || (request()->routeIs('access-matrix.sap') && request('source') == 'stage2') ? 'active' : '' }}">
                Approval Matrix
            </a>
            @endif
        </div>
    </div>

    {{-- UAR Collapse --}}
    <a href="#uarCollapse"
       data-bs-toggle="collapse"
       class="sidebar-nav-item collapsed"
       role="button"
       aria-expanded="false"
       aria-controls="uarCollapse"
       data-sidebar-tooltip="User Access Review">
        <i class="bi bi-clipboard2-check-fill sidebar-icon"></i>
        <span class="sidebar-item-text" style="flex:1;line-height:1.2;">User Access Review</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </a>
    <div class="collapse" id="uarCollapse">
        <div class="sidebar-sub-group">
            <span class="sidebar-sub-item" style="cursor:default;opacity:.5;">
                <i class="bi bi-clock-history" style="font-size:.75rem;margin-right:.3rem;"></i>Coming Soon
            </span>
        </div>
    </div>

    @if(Auth::user()->isAdmin())
    {{-- ADMINISTRATION --}}
    <div class="sidebar-section-label">Administration</div>

    {{-- Master Data Collapse --}}
    <a href="#masterDataCollapse"
       data-bs-toggle="collapse"
       class="sidebar-nav-item {{ request()->routeIs('master-data.*') ? 'active' : 'collapsed' }}"
       role="button"
       aria-expanded="{{ request()->routeIs('master-data.*') ? 'true' : 'false' }}"
       aria-controls="masterDataCollapse"
       data-sidebar-tooltip="Master Data">
        <i class="bi bi-database-fill sidebar-icon"></i>
        <span class="sidebar-item-text" style="flex:1;line-height:1.2;">Master Data</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </a>
    <div class="collapse {{ request()->routeIs('master-data.*') ? 'show' : '' }}" id="masterDataCollapse">
        <div class="sidebar-sub-group">
            <a href="{{ route('master-data.bpo') }}"
               class="sidebar-sub-item {{ request()->routeIs('master-data.bpo') ? 'active' : '' }}">
                BPO
            </a>
            <a href="{{ route('master-data.unit') }}"
               class="sidebar-sub-item {{ request()->routeIs('master-data.unit') ? 'active' : '' }}">
                Unit
            </a>
            <a href="{{ route('master-data.user') }}"
               class="sidebar-sub-item {{ request()->routeIs('master-data.user') ? 'active' : '' }}">
                User
            </a>
        </div>
    </div>

    <a href="{{ route('users.index') }}"
       class="sidebar-nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
       data-sidebar-tooltip="User Management">
        <i class="bi bi-people-fill sidebar-icon"></i>
        <span class="sidebar-item-text">User Management</span>
    </a>
    @endif

    @endif

    </div>{{-- /.sidebar-nav --}}

    {{-- ── Sidebar Footer (user info) ─────────────────────────────────── --}}
    <div class="sidebar-footer">
        <div class="sidebar-footer-inner">
            <div class="sidebar-user-avatar">
                @if(Auth::user()->profile_photo_path)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="">
                @else
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                <div class="sidebar-user-role">{{ Auth::user()->role }}</div>
            </div>
        </div>
    </div>

</aside>

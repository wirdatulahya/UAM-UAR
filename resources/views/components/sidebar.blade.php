<aside class="sidebar d-none d-lg-block" style="width:230px;flex-shrink:0;">

    @if(!Auth::user()->requires_onboarding)
    <div class="sidebar-section-label">Main</div>
    <a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-fill"></i>
        Dashboard
    </a>

    <a href="#uamCollapse" data-bs-toggle="collapse" class="sidebar-nav-item {{ request()->routeIs('access-matrix.*') ? 'active' : 'collapsed' }}" role="button" aria-expanded="{{ request()->routeIs('access-matrix.*') ? 'true' : 'false' }}" aria-controls="uamCollapse">
        <i class="bi bi-table"></i>
        <span class="d-flex align-items-center w-100">
            User Access Matrix
            <i class="bi bi-chevron-down ms-auto" style="font-size:.7rem; transition: transform var(--transition);"></i>
        </span>
    </a>
    <div class="collapse {{ request()->routeIs('access-matrix.*') ? 'show' : '' }}" id="uamCollapse">
        <div style="padding: .25rem 0; background: var(--bg);">
            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'pic_ao')
            <a href="{{ route('access-matrix.request.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.request.*') || (request()->routeIs('access-matrix.sap') && request('source') == 'request') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                Request Access Matrix
            </a>
            @endif
            @if(Auth::user()->role === 'manager')
            <a href="{{ route('access-matrix.uam-request.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.uam-request.*') || (request()->routeIs('access-matrix.sap') && request('source') == 'approval') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                Accept
            </a>
            @endif
            @if(Auth::user()->role === 'ao')
            <a href="{{ route('access-matrix.approval.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.approval.*') || (request()->routeIs('access-matrix.sap') && request('source') == 'stage2') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                Approval Access Matrix
            </a>
            @endif
        </div>
    </div>
    
    <a href="#" class="sidebar-nav-item" aria-disabled="true">
        <i class="bi bi-clipboard2-check-fill"></i>
        Access Review
        <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
    </a>

    @if(Auth::user()->isAdmin())
    <div class="sidebar-section-label mt-4" style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;padding:0 1.25rem;margin-bottom:.75rem;">Administration</div>
    <a href="{{ route('users.index') }}" class="sidebar-nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <i class="bi bi-people-fill"></i>
        User Management
    </a>
    @endif
    @endif

</aside>

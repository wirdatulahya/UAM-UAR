@extends('layouts.app')

@section('title', 'User Access Matrix Modules')

@push('styles')
<style>
    .module-landing-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .module-landing-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-shadow);
        border-color: var(--secondary);
    }

    .module-landing-card:hover .btn-enter i {
        transform: translateX(4px);
    }

    .module-landing-card:hover .btn-enter {
        color: var(--primary-dark) !important;
    }

    .module-landing-card-disabled {
        background: rgba(255,255,255,0.7);
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        opacity: 0.8;
    }
</style>
@endpush

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">

            {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="navbar-brand-wrapper">
                <div class="brand-dot">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <div class="brand-text-main">AccessHub</div>
                    <div class="brand-text-sub">PT Telkom Infrastruktur Indonesia</div>
                </div>
            </a>

            {{-- Right — Profile Dropdown --}}
            <div class="position-relative" id="profileDropdownWrapper">
                <button id="profileDropdownBtn" type="button"
                    style="background:none;border:1.5px solid var(--border);border-radius:40px;padding:.35rem .75rem .35rem .45rem;display:flex;align-items:center;gap:.6rem;cursor:pointer;transition:all var(--transition);">
                    <div style="width:32px;height:32px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-person-fill" style="color:#fff;font-size:.9rem;"></i>
                    </div>
                    <div class="d-none d-sm-block" style="line-height:1.2;text-align:left;">
                        <div style="font-size:.82rem;font-weight:700;color:var(--text);">{{ Auth::user()->name }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted);">{{ '@' . Auth::user()->username }}</div>
                    </div>
                    <i class="bi bi-chevron-down d-none d-sm-block" id="profileChevron" style="font-size:.65rem;color:var(--text-muted);transition:transform var(--transition);"></i>
                </button>

                {{-- Dropdown Menu --}}
                <div id="profileDropdownMenu"
                    style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:200px;background:#fff;border:1.5px solid var(--border);border-radius:14px;box-shadow:0 8px 32px rgba(11,46,109,.13);z-index:200;overflow:hidden;">

                    <div style="padding:.85rem 1rem .75rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                        <div style="font-size:.8rem;font-weight:700;color:var(--secondary);">{{ Auth::user()->name }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted);">{{ Auth::user()->email }}</div>
                    </div>

                    <a href="{{ route('password.change') }}"
                        style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--text);text-decoration:none;transition:background var(--transition);"
                        onmouseenter="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';"
                        onmouseleave="this.style.background='';this.style.color='var(--text)';">
                        <i class="bi bi-gear-fill" style="font-size:.9rem;color:var(--text-muted);"></i>
                        Change Password
                    </a>

                    <div style="height:1px;background:var(--border);"></div>

                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <button type="submit" id="logoutBtn"
                            style="display:flex;align-items:center;gap:.65rem;width:100%;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:#c0392b;background:none;border:none;cursor:pointer;transition:background var(--transition);"
                            onmouseenter="this.style.background='#fde8e9';"
                            onmouseleave="this.style.background='';">
                            <i class="bi bi-box-arrow-right" style="font-size:.9rem;"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</nav>

{{-- ─── App Shell ──────────────────────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <aside class="sidebar d-none d-lg-block" style="width:230px;flex-shrink:0;">

        <div class="sidebar-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="sidebar-nav-item">
            <i class="bi bi-grid-fill"></i>
            Dashboard
        </a>

        <div class="sidebar-section-label">Modules</div>
        <a href="#uamCollapse" data-bs-toggle="collapse" class="sidebar-nav-item {{ request()->routeIs('access-matrix.*') ? 'active' : 'collapsed' }}" role="button" aria-expanded="{{ request()->routeIs('access-matrix.*') ? 'true' : 'false' }}" aria-controls="uamCollapse">
            <i class="bi bi-table"></i>
            <span class="d-flex align-items-center w-100">
                User Access Matrix
                <i class="bi bi-chevron-down ms-auto" style="font-size:.7rem; transition: transform var(--transition);"></i>
            </span>
        </a>
        <div class="collapse {{ request()->routeIs('access-matrix.*') ? 'show' : '' }}" id="uamCollapse">
            <div style="padding: .25rem 0; background: var(--bg);">
                <a href="{{ route('access-matrix.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Request Access Matrix
                </a>
                <a href="#" class="sidebar-nav-item" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Approval Access Matrix
                </a>
            </div>
        </div>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-clipboard2-check-fill"></i>
            Access Review
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-graph-up-arrow"></i>
            Monitoring
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
            Reports
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>

    </aside>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

        {{-- ── Breadcrumbs ── --}}
        <nav aria-label="breadcrumb" class="animate-in" style="margin-bottom:.4rem;">
            <ol class="breadcrumb" style="background:none;padding:0;margin:0;font-size:.78rem;font-weight:500;display:flex;gap:.35rem;list-style:none;">
                <li class="breadcrumb-item d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;transition:color var(--transition);"
                       onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">Dashboard</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item active" style="color:var(--secondary);font-weight:600;margin-left:.35rem;" aria-current="page">User Access Matrix</li>
            </ol>
        </nav>

        {{-- ── Page Header ── --}}
        <div class="mb-4 animate-in">
            <h1 style="font-size:1.45rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                <i class="bi bi-table me-2" style="color:var(--primary);"></i>User Access Matrix Dashboard
            </h1>
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                Select a target system module to manage permissions, mapping configs, and compliance guidelines.
            </p>
        </div>

        {{-- ── Modules Grid ── --}}
        <div class="row g-4 animate-in animate-in-delay-1">

            {{-- 1. UAM SAP Module Card (Active) --}}
            <div class="col-12 col-md-6 col-xl-4">
                <a href="{{ route('access-matrix.sap') }}" class="module-landing-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:52px;height:52px;background:var(--secondary-light);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-pc-display-horizontal" style="font-size:1.5rem;color:var(--secondary);"></i>
                        </div>
                        <div>
                            <h2 style="font-size:1.15rem;font-weight:800;color:var(--secondary);margin:0;">UAM SAP</h2>
                            <span style="display:inline-flex;align-items:center;gap:.25rem;background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:.15rem .55rem;font-size:.65rem;font-weight:700;margin-top:.15rem;">
                                <i class="bi bi-check-circle-fill" style="font-size:.6rem;"></i> Active
                            </span>
                        </div>
                    </div>


                    {{-- Dynamic stats --}}
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div style="background:var(--bg); border: 1px solid var(--border); padding:.45rem; border-radius:10px; text-align:center;">
                                <div style="font-size:1.1rem; font-weight:800; color:var(--secondary);">{{ number_format($totalRecords) }}</div>
                                <div style="font-size:.6rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing: 0.2px;">Records</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background:var(--bg); border: 1px solid var(--border); padding:.45rem; border-radius:10px; text-align:center;">
                                <div style="font-size:1.1rem; font-weight:800; color:var(--secondary);">{{ number_format($totalRoles) }}</div>
                                <div style="font-size:.6rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing: 0.2px;">Roles</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background:var(--bg); border: 1px solid var(--border); padding:.45rem; border-radius:10px; text-align:center;">
                                <div style="font-size:1.1rem; font-weight:800; color:var(--secondary);">{{ number_format($totalTcodes) }}</div>
                                <div style="font-size:.6rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing: 0.2px;">TCODEs</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3" style="border-top:1px solid var(--border); margin-top:auto;">
                        <span style="font-size:.7rem;color:var(--text-muted);">
                            <i class="bi bi-clock-history me-1"></i> {{ $lastUpdated ? 'Updated ' . $lastUpdated->diffForHumans() : 'No updates' }}
                        </span>
                        <span class="btn-enter" style="font-size:.8rem;font-weight:700;color:var(--primary);display:inline-flex;align-items:center;gap:.25rem;">
                            Manage Matrix <i class="bi bi-arrow-right" style="font-size:.9rem; transition: transform 0.2s;"></i>
                        </span>
                    </div>
                </a>
            </div>



        </div>
    </main>

</div>

@endsection

@push('scripts')
<script>
    // Profile dropdown toggle
    const profileBtn  = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    const chevron     = document.getElementById('profileChevron');

    profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = profileMenu.style.display === 'block';
        profileMenu.style.display = isOpen ? 'none' : 'block';
        chevron.style.transform   = isOpen ? '' : 'rotate(180deg)';
        profileBtn.style.borderColor = isOpen ? 'var(--border)' : 'var(--secondary)';
    });

    document.addEventListener('click', function () {
        profileMenu.style.display = 'none';
        chevron.style.transform   = '';
        profileBtn.style.borderColor = 'var(--border)';
    });

    // Logout loading state
    document.getElementById('logoutForm').addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out…';
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Dashboard')

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

                    {{-- User Info Header --}}
                    <div style="padding:.85rem 1rem .75rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                        <div style="font-size:.8rem;font-weight:700;color:var(--secondary);">{{ Auth::user()->name }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted);">{{ Auth::user()->email }}</div>
                    </div>

                    {{-- Settings --}}
                    <a href="{{ route('password.change') }}"
                        style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--text);text-decoration:none;transition:background var(--transition);"
                        onmouseenter="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';"
                        onmouseleave="this.style.background='';this.style.color='var(--text)';">
                        <i class="bi bi-gear-fill" style="font-size:.9rem;color:var(--text-muted);"></i>
                        Change Password
                    </a>

                    <div style="height:1px;background:var(--border);"></div>

                    {{-- Logout --}}
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

{{-- ─── App Shell (Sidebar + Main) ────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <aside class="sidebar d-none d-lg-block" style="width:230px;flex-shrink:0;">

        <div class="sidebar-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="sidebar-nav-item active">
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
                <a href="{{ route('access-matrix.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    User Access Request
                </a>
                <a href="#" class="sidebar-nav-item" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    User Access Review
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

        {{-- Success/Error flash --}}
        @if (session('success'))
            <div class="alert d-flex align-items-center gap-2 mb-4 animate-in"
                 style="background:#e8f5e9;border:0;border-left:4px solid #2e7d32;border-radius:10px;color:#1b5e20;font-size:.875rem;padding:.75rem 1rem;"
                 role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── Welcome Hero ─────────────────────────────────────────── --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="animate-in" style="background:linear-gradient(135deg,var(--secondary) 0%,#1a4d9e 60%, #163f82 100%);border-radius:20px;padding:2.5rem 2.5rem 2rem;position:relative;overflow:hidden;">
                    {{-- decorative circle --}}
                    <div style="position:absolute;width:280px;height:280px;background:rgba(227,30,36,.15);border-radius:50%;right:-60px;top:-80px;"></div>
                    <div style="position:absolute;width:160px;height:160px;background:rgba(255,255,255,.05);border-radius:50%;right:80px;bottom:-60px;"></div>

                    <div style="position:relative;z-index:1;">
                        <p style="color:rgba(255,255,255,.65);font-size:.82rem;font-weight:500;margin-bottom:.35rem;letter-spacing:.3px;text-transform:uppercase;">
                            Welcome back
                        </p>
                        <h1 style="color:#fff;font-size:1.85rem;font-weight:800;margin-bottom:.35rem;line-height:1.2;">
                            {{ Auth::user()->name }}
                        </h1>
                        <p style="color:rgba(255,255,255,.7);font-size:.92rem;margin-bottom:1.75rem;">
                            AccessHub &nbsp;·&nbsp; PT Telkom Infrastruktur Indonesia
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span style="background:rgba(255,255,255,.15);backdrop-filter:blur(4px);color:#fff;border-radius:20px;padding:.3rem .85rem;font-size:.78rem;font-weight:600;">
                                <i class="bi bi-person-badge me-1"></i>{{ Auth::user()->username }}
                            </span>
                            <span style="background:rgba(255,255,255,.15);backdrop-filter:blur(4px);color:#fff;border-radius:20px;padding:.3rem .85rem;font-size:.78rem;font-weight:600;">
                                <i class="bi bi-envelope me-1"></i>{{ Auth::user()->email }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Module Cards ─────────────────────────────────────────── --}}
        <div class="row g-3">

            @php
                $modules = [
                    [
                        'icon' => 'bi-table',
                        'color' => '#0B2E6D',
                        'bg' => '#e8edf7',
                        'title' => 'User Access Matrix',
                        'desc' => 'Manage and track user access permissions across all systems.',
                        'status' => 'active',
                        'route' => route('access-matrix.index')
                    ],
                    [
                        'icon' => 'bi-clipboard2-check-fill',
                        'color' => '#0B2E6D',
                        'bg' => '#e8edf7',
                        'title' => 'Access Review',
                        'desc' => 'Conduct periodic reviews and certifications of user access rights.',
                        'status' => 'coming-soon',
                        'route' => '#'
                    ],
                    [
                        'icon' => 'bi-graph-up-arrow',
                        'color' => '#0B2E6D',
                        'bg' => '#e8edf7',
                        'title' => 'Monitoring',
                        'desc' => 'Real-time monitoring of access activities and system events.',
                        'status' => 'coming-soon',
                        'route' => '#'
                    ],
                    [
                        'icon' => 'bi-file-earmark-bar-graph-fill',
                        'color' => '#0B2E6D',
                        'bg' => '#e8edf7',
                        'title' => 'Reports',
                        'desc' => 'Generate compliance and audit reports for management review.',
                        'status' => 'coming-soon',
                        'route' => '#'
                    ],
                ];
            @endphp

            @foreach ($modules as $i => $mod)
            <div class="col-12 col-sm-6 col-xl-3 animate-in" style="animation-delay: {{ $i * 0.07 }}s;">
                @if ($mod['status'] === 'active')
                <a href="{{ $mod['route'] }}" class="h-100 d-block" style="text-decoration:none; color:inherit;">
                @else
                <div class="h-100">
                @endif
                    <div class="h-100" style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem;position:relative;overflow:hidden;transition:transform .22s,box-shadow .22s;cursor:{{ $mod['status'] === 'active' ? 'pointer' : 'default' }};"
                         onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--card-shadow)';if('{{ $mod['status'] }}' === 'active'){this.style.borderColor='var(--secondary)';}"
                         onmouseleave="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border)';">

                        <div style="width:48px;height:48px;background:{{ $mod['bg'] }};border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                            <i class="bi {{ $mod['icon'] }}" style="font-size:1.3rem;color:{{ $mod['color'] }};"></i>
                        </div>

                        <h3 style="font-size:.95rem;font-weight:700;color:var(--secondary);margin-bottom:.35rem;">{{ $mod['title'] }}</h3>
                        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.5;">{{ $mod['desc'] }}</p>

                        @if ($mod['status'] === 'active')
                        <span style="display:inline-flex;align-items:center;gap:.35rem;background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:.25rem .7rem;font-size:.72rem;font-weight:700;">
                            <i class="bi bi-check-circle-fill" style="font-size:.7rem;"></i> Active
                        </span>
                        @else
                        <span style="display:inline-flex;align-items:center;gap:.35rem;background:var(--primary-light);color:var(--primary);border-radius:20px;padding:.25rem .7rem;font-size:.72rem;font-weight:700;">
                            <i class="bi bi-clock-history" style="font-size:.7rem;"></i> Coming soon
                        </span>
                        @endif
                    </div>
                @if ($mod['status'] === 'active')
                </a>
                @else
                </div>
                @endif
            </div>
            @endforeach

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

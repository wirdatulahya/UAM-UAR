@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">
                {{-- Generic Back Button --}}
                <button type="button" onclick="window.history.back();" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;font-size:1.4rem;display:flex;align-items:center;transition:color var(--transition);" onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'" title="Go Back">
                    <i class="bi bi-arrow-left-circle"></i>
                </button>

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
            </div>

            {{-- Right — Profile Dropdown --}}
            <div class="position-relative" id="profileDropdownWrapper">
                <button id="profileDropdownBtn" type="button"
                    style="background:none;border:1.5px solid var(--border);border-radius:40px;padding:.35rem .75rem .35rem .45rem;display:flex;align-items:center;gap:.6rem;cursor:pointer;transition:all var(--transition);">
                    <div style="width:32px;height:32px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <i class="bi bi-person-fill" style="color:#fff;font-size:.9rem;"></i>
                        @endif
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

                    {{-- Profile (active) --}}
                    <a href="{{ route('profile.index') }}"
                        style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--secondary);background:var(--secondary-light);text-decoration:none;">
                        <i class="bi bi-person-circle" style="font-size:.9rem;color:var(--secondary);"></i>
                        My Profile
                    </a>

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
        <a href="{{ route('dashboard') }}" class="sidebar-nav-item">
            <i class="bi bi-grid-fill"></i>
            Dashboard
        </a>

        <div class="sidebar-section-label">Modules</div>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-table"></i>
            AccessHub
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>
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

        {{-- Breadcrumb --}}
        <div class="mb-4 animate-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="font-size:.82rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="auth-link">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </nav>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                
                {{-- Form Card --}}
                <div class="card animate-in" style="background:#fff;border:1.5px solid var(--border);border-radius:16px;box-shadow:var(--card-shadow);margin-top:1rem;">
                    <div class="card-body p-4 p-sm-5">
                        
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div style="width:40px;height:40px;background:var(--primary-light);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-person-vcard-fill" style="color:var(--primary);font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <h2 style="font-size:1.25rem;font-weight:800;color:var(--secondary);margin:0;">My Profile</h2>
                                <p style="font-size:.78rem;color:var(--text-muted);margin:0;">Manage your profile information and picture</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" id="profileForm">
                            @csrf

                            {{-- Profile Photo Section --}}
                            <div class="mb-5 d-flex flex-column flex-sm-row align-items-sm-center gap-4">
                                <div style="width:100px;height:100px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1);border:3px solid #fff;position:relative;">
                                    @if(Auth::user()->profile_photo_path)
                                        <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <i class="bi bi-person-fill" style="color:#fff;font-size:3.5rem;"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h5 style="font-size:.95rem;font-weight:700;color:var(--secondary);margin-bottom:.5rem;">Profile Picture</h5>
                                    <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.8rem;">Upload a new profile picture. JPG, PNG, WEBP, or GIF. Max 2MB.</p>
                                    
                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                        <input type="file" name="profile_photo" id="profile_photo" accept="image/*" class="form-control" style="font-size:.82rem;padding:.4rem .75rem;max-width:250px;">
                                        <button type="submit" class="btn btn-primary" style="font-size:.82rem;padding:.4rem 1rem;font-weight:600;">Upload Photo</button>
                                    </div>
                                    @error('profile_photo')
                                        <div class="text-danger mt-1" style="font-size:.75rem;">{{ $message }}</div>
                                    @enderror
                                    @if(session('success'))
                                        <div class="text-success mt-1" style="font-size:.75rem;"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
                                    @endif
                                </div>
                            </div>

                            <hr style="border-color:var(--border);margin-bottom:2rem;">

                            {{-- Read Only User Information --}}
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Full Name</label>
                                    <div style="background:#f9fafb;border:1px solid var(--border);border-radius:var(--input-radius);padding:.6rem .8rem;font-size:.88rem;color:var(--secondary);font-weight:600;">
                                        {{ Auth::user()->name }}
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Username</label>
                                    <div style="background:#f9fafb;border:1px solid var(--border);border-radius:var(--input-radius);padding:.6rem .8rem;font-size:.88rem;color:var(--secondary);font-weight:600;">
                                        {{ Auth::user()->username }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Email Address</label>
                                    <div style="background:#f9fafb;border:1px solid var(--border);border-radius:var(--input-radius);padding:.6rem .8rem;font-size:.88rem;color:var(--secondary);font-weight:600;">
                                        {{ Auth::user()->email }}
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

@endsection

@push('scripts')
<script>
    // ── Profile Dropdown ───────────────────────────────────────────────
    const profileBtn  = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    const chevron     = document.getElementById('profileChevron');

    profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = profileMenu.style.display === 'block';
        profileMenu.style.display = isOpen ? 'none' : 'block';
        if (chevron) chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
        profileBtn.style.borderColor = isOpen ? 'var(--border)' : 'var(--secondary)';
    });

    document.addEventListener('click', function () {
        profileMenu.style.display = 'none';
        if (chevron) chevron.style.transform = '';
        profileBtn.style.borderColor = 'var(--border)';
    });

    document.getElementById('logoutForm').addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out…';
    });
</script>
@endpush

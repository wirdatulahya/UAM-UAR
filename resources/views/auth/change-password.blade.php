@extends('layouts.app')

@section('title', 'Change Password')

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

            {{-- Right — User info + Change Password / Logout --}}
            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-sm-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-person-fill" style="color:var(--secondary);font-size:.9rem;"></i>
                    </div>
                    <div style="line-height:1.2;">
                        <div style="font-size:.82rem;font-weight:700;color:var(--text);">{{ Auth::user()->name }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted);">{{ '@' . Auth::user()->username }}</div>
                    </div>
                </div>

                <a href="{{ route('password.change') }}" class="btn-logout me-1 active-logout" style="text-decoration:none;border-color:var(--primary);color:var(--primary);background:var(--primary-light);">
                    <i class="bi bi-key-fill"></i>
                    <span class="d-none d-sm-inline">Change Password</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="btn-logout" id="logoutBtn">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
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

        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('password.change') }}" class="sidebar-nav-item active">
            <i class="bi bi-key-fill"></i>
            Change Password
        </a>
    </aside>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

        {{-- Breadcrumb --}}
        <div class="mb-4 animate-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="font-size:.82rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="auth-link">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                </ol>
            </nav>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                
                {{-- Form Card --}}
                <div class="card animate-in" style="background:#fff;border:1.5px solid var(--border);border-radius:16px;box-shadow:var(--card-shadow);margin-top:1rem;">
                    <div class="card-body p-4 p-sm-5">
                        
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div style="width:40px;height:40px;background:var(--primary-light);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-key-fill" style="color:var(--primary);font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <h2 style="font-size:1.25rem;font-weight:800;color:var(--secondary);margin:0;">Change Password</h2>
                                <p style="font-size:.78rem;color:var(--text-muted);margin:0;">Update your account credentials for security</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('password.update') }}" id="changePasswordForm" novalidate>
                            @csrf

                            {{-- Current Password --}}
                            <div class="mb-4">
                                <label for="current_password" class="form-label">Current Password</label>
                                <div class="input-password-wrapper">
                                    <div class="input-group">
                                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                                            <i class="bi bi-shield-lock-fill"></i>
                                        </span>
                                        <input
                                            type="password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            id="current_password"
                                            name="current_password"
                                            placeholder="Enter current password"
                                            style="border-radius:0;border-left:0;border-right:0;"
                                        >
                                        <button class="input-group-text password-toggle-btn" type="button" id="toggleCurrentPassword"
                                            aria-label="Toggle current password visibility"
                                            style="border-radius:0 var(--input-radius) var(--input-radius) 0;border:1.5px solid var(--border);border-left:0;background:#fff;cursor:pointer;color:var(--text-muted);transition:color .22s;">
                                            <i class="bi bi-eye-fill" id="toggleCurrentPasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- New Password --}}
                            <div class="mb-4">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-password-wrapper">
                                    <div class="input-group">
                                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                                            <i class="bi bi-key-fill"></i>
                                        </span>
                                        <input
                                            type="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            id="password"
                                            name="password"
                                            placeholder="Enter at least 8 characters"
                                            style="border-radius:0;border-left:0;border-right:0;"
                                        >
                                        <button class="input-group-text password-toggle-btn" type="button" id="togglePassword"
                                            aria-label="Toggle new password visibility"
                                            style="border-radius:0 var(--input-radius) var(--input-radius) 0;border:1.5px solid var(--border);border-left:0;background:#fff;cursor:pointer;color:var(--text-muted);transition:color .22s;">
                                            <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Confirm New Password --}}
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="input-password-wrapper">
                                    <div class="input-group">
                                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                                            <i class="bi bi-key-fill"></i>
                                        </span>
                                        <input
                                            type="password"
                                            class="form-control @error('password_confirmation') is-invalid @enderror"
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            placeholder="Confirm new password"
                                            style="border-radius:0;border-left:0;border-right:0;"
                                        >
                                        <button class="input-group-text password-toggle-btn" type="button" id="toggleConfirmPassword"
                                            aria-label="Toggle confirm password visibility"
                                            style="border-radius:0 var(--input-radius) var(--input-radius) 0;border:1.5px solid var(--border);border-left:0;background:#fff;cursor:pointer;color:var(--text-muted);transition:color .22s;">
                                            <i class="bi bi-eye-fill" id="toggleConfirmPasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Action buttons --}}
                            <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-2">
                                <a href="{{ route('dashboard') }}" class="btn btn-light" style="border-radius:var(--input-radius);padding:.72rem 1.5rem;font-weight:600;font-size:.95rem;width:48%;border:1.5px solid var(--border);background:#fff;color:var(--text-muted);transition:all .22s;text-decoration:none;display:inline-block;text-align:center;">
                                    Cancel
                                </a>
                                <button type="submit" class="btn-primary-custom" id="submitBtn" style="width:48%;">
                                    Update Password
                                </button>
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
    // Helper function to toggle password visibility
    function setupPasswordToggle(buttonId, inputId, iconId) {
        const toggleBtn  = document.getElementById(buttonId);
        const passInput  = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

        toggleBtn.addEventListener('click', function () {
            const isPassword = passInput.type === 'password';
            passInput.type   = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('bi-eye-fill',   !isPassword);
            toggleIcon.classList.toggle('bi-eye-slash-fill', isPassword);
            this.style.color = isPassword ? 'var(--secondary)' : 'var(--text-muted)';
        });
    }

    // Initialize toggles
    setupPasswordToggle('toggleCurrentPassword', 'current_password', 'toggleCurrentPasswordIcon');
    setupPasswordToggle('togglePassword', 'password', 'togglePasswordIcon');
    setupPasswordToggle('toggleConfirmPassword', 'password_confirmation', 'toggleConfirmPasswordIcon');

    // Button loading state on submit
    document.getElementById('changePasswordForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating…';
    });

    document.getElementById('logoutForm').addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    });
</script>
@endpush

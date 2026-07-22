@extends('layouts.app')

@section('title', 'Change Password')

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
            <x-navbar-right />
        </div>
    </div>
</nav>

{{-- ─── App Shell (Sidebar + Main) ────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

                <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Change Password'],
        ]" />

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
</script>
@endpush




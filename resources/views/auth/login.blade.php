@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-wrapper">

    {{-- ─── Left Decorative Panel ──────────────────────────────────── --}}
    <div class="auth-panel">
        <div class="auth-panel-logo animate-in">
            <div class="brand-badge">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>User Access Matrix<br>&amp; Review</h1>
            <p>PT Telkom Infrastruktur Indonesia</p>
        </div>

        <div class="auth-panel-features animate-in animate-in-delay-2">
            <div class="feature-item">
                <i class="bi bi-person-badge-fill"></i>
                <span>User Access Matrix Management</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-clipboard2-check-fill"></i>
                <span>Periodic Access Review Workflow</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Monitoring &amp; Reporting</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-shield-check-fill"></i>
                <span>Secure Role-Based Access Control</span>
            </div>
        </div>
    </div>

    {{-- ─── Right Login Form ────────────────────────────────────────── --}}
    <div class="auth-content">
        <div class="auth-card">

            {{-- Header --}}
            <div class="mb-4 animate-in">
                <div class="d-flex align-items-center gap-2 mb-3 d-md-none">
                    <div style="width:36px;height:36px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-shield-lock-fill text-white"></i>
                    </div>
                    <div>
                        <div style="font-size:.78rem;font-weight:800;color:var(--secondary);line-height:1.1">UAM &amp; Review</div>
                        <div style="font-size:.65rem;color:var(--text-muted)">PT Telkom Infrastruktur Indonesia</div>
                    </div>
                </div>
                <h2 class="auth-card-title">Welcome back</h2>
                <p class="auth-card-subtitle">Sign in to your account to continue</p>
            </div>

            {{-- Success Alert (after register redirect) --}}
            @if (session('success'))
                <div class="alert alert-custom alert-custom-success d-flex align-items-center gap-2 mb-3 animate-in" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="alert alert-custom alert-custom-danger d-flex align-items-start gap-2 mb-3 animate-in" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>Login failed.</strong>
                        {{ $errors->first('login') }}
                    </div>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login.submit') }}" id="loginForm" novalidate>
                @csrf

                {{-- Username / Email --}}
                <div class="mb-3 animate-in animate-in-delay-1">
                    <label for="login" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control @error('login') is-invalid @enderror"
                            id="login"
                            name="login"
                            value="{{ old('login') }}"
                            placeholder="Enter your username or email"
                            autocomplete="username"
                            autofocus
                            style="border-radius: 0 var(--input-radius) var(--input-radius) 0; border-left: 0;"
                        >
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-4 animate-in animate-in-delay-2">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-password-wrapper">
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                style="border-radius:0;border-left:0;border-right:0;"
                            >
                            <button class="input-group-text password-toggle-btn" type="button" id="togglePassword"
                                aria-label="Toggle password visibility"
                                style="border-radius:0 var(--input-radius) var(--input-radius) 0;border:1.5px solid var(--border);border-left:0;background:#fff;cursor:pointer;color:var(--text-muted);transition:color .22s;">
                                <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Submit --}}
                <div class="animate-in animate-in-delay-3">
                    <button type="submit" class="btn-primary-custom" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Sign In
                    </button>
                </div>

                {{-- Register link --}}
                <p class="text-center mt-3 mb-0 animate-in animate-in-delay-4" style="font-size:.84rem; color:var(--text-muted);">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="auth-link">Create one</a>
                </p>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Password show/hide toggle ──────────────────────────────────────
    const toggleBtn  = document.getElementById('togglePassword');
    const passInput  = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    toggleBtn.addEventListener('click', function () {
        const isPassword = passInput.type === 'password';
        passInput.type   = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('bi-eye-fill',   !isPassword);
        toggleIcon.classList.toggle('bi-eye-slash-fill', isPassword);
        this.style.color = isPassword ? 'var(--secondary)' : 'var(--text-muted)';
    });

    // ── Button loading state on submit ────────────────────────────────
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Signing in…';
    });
</script>
@endpush

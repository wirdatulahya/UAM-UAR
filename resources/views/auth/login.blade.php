@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-wrapper">

    {{-- ─── Left Decorative Panel ──────────────────────────────────── --}}
    <div class="auth-panel">

        {{-- Floating shapes --}}
        <div class="auth-panel-shape" style="width:220px;height:220px;background:rgba(255,255,255,.03);top:10%;left:-60px;animation-duration:7s;"></div>
        <div class="auth-panel-shape" style="width:120px;height:120px;background:rgba(227,30,36,.08);bottom:15%;right:-30px;animation-duration:5s;animation-delay:1s;"></div>

        <div class="auth-panel-logo animate-in">
            <div class="brand-badge">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>AccessHub</h1>
        </div>
    </div>

    {{-- ─── Right Login Form ────────────────────────────────────────── --}}
    <div class="auth-content">
        <div class="auth-card">

            {{-- Mobile Brand --}}
            <div class="d-flex align-items-center gap-2 mb-4 d-md-none animate-in">
                <div style="width:38px;height:38px;background:linear-gradient(135deg,var(--primary),#ff5c62);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px var(--primary-glow);">
                    <i class="bi bi-shield-lock-fill text-white"></i>
                </div>
                <div>
                    <div style="font-size:.8rem;font-weight:800;color:var(--secondary);line-height:1.1">AccessHub</div>
                </div>
            </div>

            {{-- Header --}}
            <div class="mb-4 animate-in">
                <h2 class="auth-card-title">Welcome back 👋</h2>
                <p class="auth-card-subtitle">Sign in to your account to continue</p>
            </div>

            {{-- Success Alert --}}
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
                    <div style="position:relative;">
                        <span style="position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.95rem;pointer-events:none;z-index:2;">
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
                            style="padding-left:2.5rem;"
                        >
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-4 animate-in animate-in-delay-2">
                    <label for="password" class="form-label">Password</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.95rem;pointer-events:none;z-index:2;">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            style="padding-left:2.5rem;padding-right:2.8rem;"
                        >
                        <button class="password-toggle" type="button" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                        </button>
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

            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Password show/hide toggle
    const toggleBtn  = document.getElementById('togglePassword');
    const passInput  = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    toggleBtn.addEventListener('click', function () {
        const isPassword = passInput.type === 'password';
        passInput.type   = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('bi-eye-fill',      !isPassword);
        toggleIcon.classList.toggle('bi-eye-slash-fill', isPassword);
        this.style.color = isPassword ? 'var(--secondary)' : 'var(--text-muted)';
    });

    // Button loading state on submit
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Signing in…';
    });
</script>
@endpush

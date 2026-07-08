@extends('layouts.app')

@section('title', 'Create Account')

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
                <i class="bi bi-person-plus-fill"></i>
                <span>Quick Account Setup</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-lock-fill"></i>
                <span>Secured with Bcrypt Hashing</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-people-fill"></i>
                <span>Role-Based Access Control</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-shield-check-fill"></i>
                <span>Enterprise-Grade Security</span>
            </div>
        </div>
    </div>

    {{-- ─── Right Register Form ─────────────────────────────────────── --}}
    <div class="auth-content" style="align-items:flex-start; padding-top: 2.5rem; padding-bottom: 2.5rem;">
        <div class="auth-card" style="max-width:480px;">

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
                <h2 class="auth-card-title">Create account</h2>
                <p class="auth-card-subtitle">Fill in the form below to register your account</p>
            </div>

            {{-- General Errors --}}
            @if ($errors->any())
                <div class="alert alert-custom alert-custom-danger d-flex align-items-start gap-2 mb-3 animate-in" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Register Form --}}
            <form method="POST" action="{{ route('register.submit') }}" id="registerForm" novalidate>
                @csrf

                {{-- Full Name --}}
                <div class="mb-3 animate-in animate-in-delay-1">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            autofocus
                            style="border-radius: 0 var(--input-radius) var(--input-radius) 0; border-left: 0;"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Username --}}
                <div class="mb-3 animate-in animate-in-delay-2">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                            <i class="bi bi-at"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control @error('username') is-invalid @enderror"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Choose a unique username"
                            autocomplete="username"
                            style="border-radius: 0 var(--input-radius) var(--input-radius) 0; border-left: 0;"
                        >
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="font-size:.75rem; color:var(--text-muted); margin-top:.3rem;">
                        <i class="bi bi-info-circle"></i> Letters, numbers, and underscores only.
                    </div>
                </div>

                {{-- Email --}}
                <div class="mb-3 animate-in animate-in-delay-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            autocomplete="email"
                            style="border-radius: 0 var(--input-radius) var(--input-radius) 0; border-left: 0;"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-3 animate-in animate-in-delay-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            id="password"
                            name="password"
                            placeholder="Minimum 8 characters"
                            autocomplete="new-password"
                            style="border-radius:0;border-left:0;border-right:0;"
                        >
                        <button class="input-group-text" type="button" id="togglePassword"
                            aria-label="Toggle password visibility"
                            style="border-radius:0 var(--input-radius) var(--input-radius) 0;border:1.5px solid var(--border);border-left:0;background:#fff;cursor:pointer;color:var(--text-muted);transition:color .22s;">
                            <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password strength indicator --}}
                    <div class="mt-2" id="passwordStrengthBar" style="display:none;">
                        <div style="height:4px;background:var(--border);border-radius:4px;overflow:hidden;">
                            <div id="strengthFill" style="height:100%;width:0%;border-radius:4px;transition:width .35s,background .35s;"></div>
                        </div>
                        <div id="strengthLabel" style="font-size:.72rem;margin-top:.3rem;color:var(--text-muted);"></div>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="mb-4 animate-in animate-in-delay-5">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:var(--input-radius) 0 0 var(--input-radius);border:1.5px solid var(--border);border-right:0;background:#fff;color:var(--text-muted);">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input
                            type="password"
                            class="form-control"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Re-enter your password"
                            autocomplete="new-password"
                            style="border-radius:0;border-left:0;border-right:0;"
                        >
                        <button class="input-group-text" type="button" id="toggleConfirmPassword"
                            aria-label="Toggle confirm password visibility"
                            style="border-radius:0 var(--input-radius) var(--input-radius) 0;border:1.5px solid var(--border);border-left:0;background:#fff;cursor:pointer;color:var(--text-muted);transition:color .22s;">
                            <i class="bi bi-eye-fill" id="toggleConfirmPasswordIcon"></i>
                        </button>
                    </div>
                    <div id="confirmMatchMsg" style="font-size:.75rem;margin-top:.3rem;display:none;"></div>
                </div>

                {{-- Submit --}}
                <div class="animate-in animate-in-delay-5">
                    <button type="submit" class="btn-primary-custom" id="registerBtn">
                        <i class="bi bi-person-plus-fill me-1"></i>
                        Create Account
                    </button>
                </div>

                <p class="text-center mt-3 mb-0 animate-in animate-in-delay-5" style="font-size:.84rem; color:var(--text-muted);">
                    Already have an account?
                    <a href="{{ route('login') }}" class="auth-link">Sign in</a>
                </p>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Password show/hide — main password ───────────────────────────
    const toggleBtn  = document.getElementById('togglePassword');
    const passInput  = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    toggleBtn.addEventListener('click', function () {
        const show = passInput.type === 'password';
        passInput.type = show ? 'text' : 'password';
        toggleIcon.classList.toggle('bi-eye-fill',      !show);
        toggleIcon.classList.toggle('bi-eye-slash-fill', show);
        this.style.color = show ? 'var(--secondary)' : 'var(--text-muted)';
    });

    // ── Password show/hide — confirm password ─────────────────────────
    const toggleConfirmBtn  = document.getElementById('toggleConfirmPassword');
    const confirmInput      = document.getElementById('password_confirmation');
    const toggleConfirmIcon = document.getElementById('toggleConfirmPasswordIcon');

    toggleConfirmBtn.addEventListener('click', function () {
        const show = confirmInput.type === 'password';
        confirmInput.type = show ? 'text' : 'password';
        toggleConfirmIcon.classList.toggle('bi-eye-fill',      !show);
        toggleConfirmIcon.classList.toggle('bi-eye-slash-fill', show);
        this.style.color = show ? 'var(--secondary)' : 'var(--text-muted)';
    });

    // ── Password strength meter ───────────────────────────────────────
    const strengthBar   = document.getElementById('passwordStrengthBar');
    const strengthFill  = document.getElementById('strengthFill');
    const strengthLabel = document.getElementById('strengthLabel');

    passInput.addEventListener('input', function () {
        const val = this.value;
        strengthBar.style.display = val.length ? 'block' : 'none';

        let score = 0;
        if (val.length >= 8)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { pct: '20%', color: '#E31E24', label: 'Very weak' },
            { pct: '40%', color: '#e36a1e', label: 'Weak'      },
            { pct: '65%', color: '#e3c01e', label: 'Fair'      },
            { pct: '85%', color: '#56b754', label: 'Strong'    },
            { pct:'100%', color: '#2e7d32', label: 'Very strong'},
        ];
        const lvl = levels[Math.min(score, 4)];
        strengthFill.style.width      = lvl.pct;
        strengthFill.style.background = lvl.color;
        strengthLabel.style.color     = lvl.color;
        strengthLabel.textContent     = lvl.label;

        checkMatch();
    });

    // ── Confirm password match indicator ─────────────────────────────
    const confirmMsg = document.getElementById('confirmMatchMsg');

    function checkMatch() {
        const pass    = passInput.value;
        const confirm = confirmInput.value;
        if (!confirm.length) { confirmMsg.style.display = 'none'; return; }
        confirmMsg.style.display = 'block';
        if (pass === confirm) {
            confirmMsg.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#2e7d32"></i> <span style="color:#2e7d32">Passwords match</span>';
        } else {
            confirmMsg.innerHTML = '<i class="bi bi-x-circle-fill" style="color:var(--primary)"></i> <span style="color:var(--primary)">Passwords do not match</span>';
        }
    }

    confirmInput.addEventListener('input', checkMatch);

    // ── Loading state on submit ───────────────────────────────────────
    document.getElementById('registerForm').addEventListener('submit', function () {
        const btn = document.getElementById('registerBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating account…';
    });
</script>
@endpush

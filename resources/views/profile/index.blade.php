@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="window.history.back();" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;font-size:1.4rem;display:flex;align-items:center;transition:color var(--transition);" onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'" title="Go Back">
                    <i class="bi bi-arrow-left-circle"></i>
                </button>
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
            <x-navbar-right />
        </div>
    </div>
</nav>

<div class="d-flex" style="min-height:calc(100vh - 57px); background-color: var(--bg);">
    <x-sidebar />

    <main class="flex-grow-1 page-content px-4">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'My Profile'],
        ]" />

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-xl-10">
                
                <div class="d-flex align-items-center gap-3 mb-4 animate-in">
                    <div style="width:48px;height:48px;background:var(--primary-light);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-person-badge-fill" style="color:var(--primary);font-size:1.4rem;"></i>
                    </div>
                    <div>
                        <h2 style="font-size:1.5rem;font-weight:800;color:var(--secondary);margin:0;">My Profile</h2>
                        <p style="font-size:.85rem;color:var(--text-muted);margin:0;">Manage your personal information and security settings</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-custom alert-custom-success animate-in mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-custom alert-custom-danger animate-in mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> Please fix the errors below.
                    </div>
                @endif
                
                @if(!Auth::user()->is_profile_completed)
                    <div class="alert animate-in mb-4" style="background:#fff3cd; border-left:4px solid #ffc107; color:#856404; padding:1rem 1.25rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                            <h5 style="margin:0; font-weight:700;">Action Required: Complete Your Profile</h5>
                        </div>
                        <p style="margin:0; font-size:.9rem; line-height:1.5;">
                            Profile completion is required before accessing the system. Please fill out your <strong>Employee ID (NIK), Phone Number, Department, Division/Organization, and Position/Job Title</strong>.
                        </p>
                    </div>
                @endif
                
                @if(Auth::user()->requires_onboarding && Auth::user()->is_profile_completed)
                    <div class="alert animate-in mb-4" style="background:#fff3cd; border-left:4px solid #ffc107; color:#856404; padding:1rem 1.25rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                            <h5 style="margin:0; font-weight:700;">Action Required: Change Password</h5>
                        </div>
                        <p style="margin:0; font-size:.9rem; line-height:1.5;">
                            Please change your temporary password in the Security Settings section below to unlock full access.
                        </p>
                    </div>
                @endif

                <div class="row g-4">
                    {{-- Left Column: Profile & Organization Info --}}
                    <div class="col-12 col-lg-8">
                        
                        {{-- Photo update uses its own form so it doesn't conflict with main profile form --}}
                        <div class="card animate-in-delay-1 mb-4" style="border:1px solid var(--border);border-radius:var(--card-radius);box-shadow:var(--card-shadow);border-top:4px solid var(--secondary);">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                                <h5 style="font-size:1.1rem;font-weight:700;color:var(--secondary);margin:0;"><i class="bi bi-image text-primary me-2"></i> Profile Photo</h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="d-flex flex-column flex-sm-row align-items-sm-center gap-4">
                                    @csrf
                                    <div style="width:100px;height:100px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1);border:3px solid #fff;position:relative;">
                                        @if(Auth::user()->profile_photo_path)
                                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <i class="bi bi-person-fill" style="color:#fff;font-size:3.5rem;"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.8rem;">Upload a new profile picture. JPG, PNG, WEBP. Max 2MB (Optional).</p>
                                        <div class="d-flex flex-column flex-sm-row gap-2">
                                            <input type="file" name="profile_photo" id="profile_photo" accept="image/*" class="form-control" style="font-size:.82rem;padding:.4rem .75rem;max-width:250px;" required>
                                            <button type="submit" class="btn btn-primary-custom" style="font-size:.82rem;padding:.4rem 1rem;width:auto;">Update Photo</button>
                                        </div>
                                        @error('profile_photo')
                                            <div class="text-danger mt-1" style="font-size:.75rem;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Main Profile Info Form (Wraps Profile & Org Info) --}}
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            
                            {{-- 1. Profile Information (Card) --}}
                            <div class="card animate-in-delay-1 mb-4" style="border:1px solid var(--border);border-radius:var(--card-radius);box-shadow:var(--card-shadow);">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                                    <h5 style="font-size:1.1rem;font-weight:700;color:var(--secondary);margin:0;"><i class="bi bi-person-lines-fill text-primary me-2"></i> Profile Information</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', Auth::user()->name) }}" required>
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Employee ID (NIK) <span class="text-danger">*</span></label>
                                            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', Auth::user()->nik ?? Auth::user()->username) }}" required>
                                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Email Address</label>
                                            <input type="email" class="form-control bg-light" value="{{ Auth::user()->email }}" readonly>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Phone Number <span class="text-danger">*</span></label>
                                            <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', Auth::user()->phone_number) }}" placeholder="+62..." required>
                                            @error('phone_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Organization Information (Card) --}}
                            <div class="card animate-in-delay-2" style="border:1px solid var(--border);border-radius:var(--card-radius);box-shadow:var(--card-shadow);">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                                    <h5 style="font-size:1.1rem;font-weight:700;color:var(--secondary);margin:0;"><i class="bi bi-building text-primary me-2"></i> Organization Information</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Department <span class="text-danger">*</span></label>
                                            <input type="text" name="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department', Auth::user()->department) }}" required>
                                            @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Division / Organization <span class="text-danger">*</span></label>
                                            <input type="text" name="division" class="form-control @error('division') is-invalid @enderror" value="{{ old('division', Auth::user()->division) }}" required>
                                            @error('division') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Position / Job Title <span class="text-danger">*</span></label>
                                            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', Auth::user()->position ?? Auth::user()->job_title) }}" required>
                                            @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">System Role</label>
                                            <div class="form-control bg-light">
                                                @if(Auth::user()->role === 'admin')
                                                    <span class="badge bg-danger rounded-pill px-3">Admin</span>
                                                @elseif(Auth::user()->role === 'pic_ao')
                                                    <span class="badge bg-primary rounded-pill px-3">PIC AO</span>
                                                @elseif(Auth::user()->role === 'manager')
                                                    <span class="badge bg-success rounded-pill px-3">Manager</span>
                                                @elseif(Auth::user()->role === 'ao')
                                                    <span class="badge bg-info text-dark rounded-pill px-3">AO</span>
                                                @else
                                                    <span class="badge bg-secondary rounded-pill px-3">{{ Auth::user()->role ?? 'User' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary-custom" style="width:auto;font-size:.85rem;padding:.5rem 1.25rem;">Save Profile Changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Right Column: Account & Security --}}
                    <div class="col-12 col-lg-4 d-flex flex-column gap-4">
                        
                        {{-- 3. Account Information --}}
                        <div class="card animate-in-delay-3" style="border:1px solid var(--border);border-radius:var(--card-radius);box-shadow:var(--card-shadow);">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                                <h5 style="font-size:1.1rem;font-weight:700;color:var(--secondary);margin:0;"><i class="bi bi-info-circle-fill text-primary me-2"></i> Account Info</h5>
                            </div>
                            <div class="card-body p-4">
                                <ul class="list-unstyled mb-0" style="font-size:.85rem;">
                                    <li class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                        <span class="text-muted fw-semibold">Username</span>
                                        <span class="text-dark fw-bold">{{ Auth::user()->username }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                        <span class="text-muted fw-semibold">Status</span>
                                        @if((Auth::user()->account_status ?? 'Active') === 'Active')
                                            <span class="badge bg-success text-white">Active</span>
                                        @else
                                            <span class="badge bg-secondary text-white">{{ Auth::user()->account_status }}</span>
                                        @endif
                                    </li>
                                    <li class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                        <span class="text-muted fw-semibold">Created On</span>
                                        <span class="text-dark">{{ Auth::user()->created_at ? Auth::user()->created_at->format('d M Y') : '-' }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span class="text-muted fw-semibold">Last Login</span>
                                        <span class="text-dark fst-italic">Just now</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- 4. Security --}}
                        <div class="card animate-in-delay-4" style="border:1px solid var(--border);border-radius:var(--card-radius);box-shadow:var(--card-shadow);">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                                <h5 style="font-size:1.1rem;font-weight:700;color:var(--secondary);margin:0;"><i class="bi bi-shield-lock-fill text-primary me-2"></i> Security</h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="{{ route('profile.password.update') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Current Password</label>
                                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••">
                                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">New Password</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label text-muted text-uppercase" style="font-size:.7rem;">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                                    </div>
                                    <button type="submit" class="btn btn-outline-danger w-100" style="font-weight:600;font-size:.85rem;border-radius:var(--input-radius);">Update Password</button>
                                </form>

                                <hr class="my-4">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.85rem;">Two-Factor Auth</div>
                                        <div class="text-muted" style="font-size:.75rem;">Not enabled</div>
                                    </div>
                                    <button class="btn btn-sm btn-light text-muted" disabled style="font-size:.75rem;">Coming Soon</button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.85rem;">Active Sessions</div>
                                        <div class="text-muted" style="font-size:.75rem;">Manage devices</div>
                                    </div>
                                    <button class="btn btn-sm btn-light text-muted" disabled style="font-size:.75rem;">Coming Soon</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 5. Activity Summary --}}
                <div class="card animate-in-delay-5 mt-4 mb-5" style="border:1px solid var(--border);border-radius:var(--card-radius);box-shadow:var(--card-shadow);background:linear-gradient(135deg, var(--secondary-dark) 0%, var(--secondary) 100%);color:#fff;">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-4 mb-4 mb-md-0 border-end border-light border-opacity-25">
                                <h4 class="fw-bold mb-1"><i class="bi bi-activity text-primary-light me-2"></i> Activity Summary</h4>
                                <p class="text-white-50 mb-0" style="font-size:.85rem;">Overview of your interactions within AccessHub.</p>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="row text-center">
                                    <div class="col-3">
                                        <h3 class="fw-bolder mb-0 text-white">{{ $requestsSubmitted ?? '-' }}</h3>
                                        <div class="text-white-50 text-uppercase" style="font-size:.65rem;font-weight:600;letter-spacing:.5px;">Submitted</div>
                                    </div>
                                    <div class="col-3">
                                        <h3 class="fw-bolder mb-0 text-white">{{ $requestsApproved ?? '-' }}</h3>
                                        <div class="text-white-50 text-uppercase" style="font-size:.65rem;font-weight:600;letter-spacing:.5px;">Approved</div>
                                    </div>
                                    <div class="col-3">
                                        <h3 class="fw-bolder mb-0 text-white">{{ $requestsReturned ?? '-' }}</h3>
                                        <div class="text-white-50 text-uppercase" style="font-size:.65rem;font-weight:600;letter-spacing:.5px;">Returned</div>
                                    </div>
                                    <div class="col-3">
                                        <h5 class="fw-bold mb-0 text-white" style="margin-top:5px;font-size:.9rem;">
                                            @if(!empty($lastActivity))
                                                {{ \Carbon\Carbon::parse($lastActivity)->diffForHumans() }}
                                            @else
                                                -
                                            @endif
                                        </h5>
                                        <div class="text-white-50 text-uppercase mt-1" style="font-size:.65rem;font-weight:600;letter-spacing:.5px;">Last Activity</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('logoutForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out…';
        }
    });
</script>
@endpush

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
            ['label' => 'My Profile'],
        ]" />

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
    // Handled globally by Bootstrap dropdowns in app layout

    document.getElementById('logoutForm').addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out…';
    });
</script>
@endpush

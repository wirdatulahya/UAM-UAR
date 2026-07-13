@extends('layouts.app')

@section('title', 'Add UAM Record')

@section('content')

{{-- Navbar --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="navbar-brand-wrapper">
                <div class="brand-dot"><i class="bi bi-shield-lock-fill"></i></div>
                <div>
                    <div class="brand-text-main">AccessHub</div>
                    <div class="brand-text-sub">PT Telkom Infrastruktur Indonesia</div>
                </div>
            </a>
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
                <div id="profileDropdownMenu"
                    style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:200px;background:#fff;border:1.5px solid var(--border);border-radius:14px;box-shadow:0 8px 32px rgba(11,46,109,.13);z-index:200;overflow:hidden;">
                    <div style="padding:.85rem 1rem .75rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                        <div style="font-size:.8rem;font-weight:700;color:var(--secondary);">{{ Auth::user()->name }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted);">{{ Auth::user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <button type="submit"
                            style="display:flex;align-items:center;gap:.65rem;width:100%;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:#c0392b;background:none;border:none;cursor:pointer;transition:background var(--transition);"
                            onmouseenter="this.style.background='#fde8e9';"
                            onmouseleave="this.style.background='';">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <aside class="sidebar d-none d-lg-block" style="width:230px;flex-shrink:0;">
        <div class="sidebar-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="sidebar-nav-item"><i class="bi bi-grid-fill"></i> Dashboard</a>
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
                <a href="{{ route('access-matrix.approval') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.approval') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Approval Access Matrix
                </a>
            </div>
        </div>
        <a href="#" class="sidebar-nav-item" aria-disabled="true"><i class="bi bi-clipboard2-check-fill"></i> Access Review
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span></a>
        <a href="#" class="sidebar-nav-item" aria-disabled="true"><i class="bi bi-graph-up-arrow"></i> Monitoring
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span></a>
    </aside>

    <main class="flex-grow-1 page-content px-4">

        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="animate-in" style="margin-bottom:.4rem;">
            <ol class="breadcrumb" style="background:none;padding:0;margin:0;font-size:.78rem;font-weight:500;display:flex;gap:.35rem;list-style:none;">
                <li class="breadcrumb-item d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;">Dashboard</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item d-flex align-items-center" style="margin-left:.35rem;">
                    <a href="{{ route('access-matrix.index') }}" style="color:var(--text-muted);text-decoration:none;">User Access Matrix</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item d-flex align-items-center" style="margin-left:.35rem;">
                    <a href="{{ route('access-matrix.sap') }}" style="color:var(--text-muted);text-decoration:none;">UAM SAP</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item active" style="color:var(--secondary);font-weight:600;margin-left:.35rem;" aria-current="page">Add Record</li>
            </ol>
        </nav>

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in">
            <div>
                <h1 style="font-size:1.35rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                    <i class="bi bi-plus-circle-fill me-2" style="color:var(--primary);"></i>Add New UAM Record
                </h1>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0;">Create a new User Access Matrix entry</p>
            </div>
            <a href="{{ route('access-matrix.sap', $requestId ? ['request_id' => $requestId] : []) }}"
               style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:10px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:600;color:var(--text-muted);text-decoration:none;transition:all var(--transition);"
               onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';"
               onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        {{-- Form Card --}}
        <div class="animate-in animate-in-delay-1" style="max-width:720px;">
            <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">

                <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                    <div style="font-size:.88rem;font-weight:700;color:var(--secondary);">
                        <i class="bi bi-shield-lock-fill me-2"></i>Record Details
                    </div>
                </div>

                <div style="padding:1.75rem 1.5rem;">

                    @if ($errors->any())
                        <div style="background:var(--primary-light);border-left:4px solid var(--primary);border-radius:10px;color:#7b0d0f;font-size:.875rem;padding:.75rem 1rem;margin-bottom:1.25rem;">
                            <div style="font-weight:600;margin-bottom:.3rem;"><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:</div>
                            <ul style="margin:0;padding-left:1.2rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('access-matrix.store') }}" id="createForm">
                        @csrf
                        @if($requestId)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        {{-- Role --}}
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                Role <span style="color:var(--primary);">*</span>
                            </label>
                            <input type="text" id="role" name="role"
                                   class="form-control @error('role') is-invalid @enderror"
                                   value="{{ old('role') }}"
                                   placeholder="e.g. ZPS-MD-1014-000000-PROJ-CHG"
                                   style="font-family:monospace;font-weight:600;">
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description Role --}}
                        <div class="mb-3">
                            <label for="description_role" class="form-label">Description Role</label>
                            <textarea id="description_role" name="description_role" rows="3"
                                      class="form-control @error('description_role') is-invalid @enderror"
                                      placeholder="Describe what this role is for…">{{ old('description_role') }}</textarea>
                            @error('description_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- Module --}}
                            <div class="col-12 col-sm-6">
                                <label for="module" class="form-label">
                                    Module <span style="color:var(--primary);">*</span>
                                </label>
                                <input type="text" id="module" name="module"
                                       class="form-control @error('module') is-invalid @enderror"
                                       value="{{ old('module') }}"
                                       placeholder="e.g. PS" required>
                                @error('module')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Period --}}
                            <div class="col-12 col-sm-6">
                                <label for="period" class="form-label">
                                    Period <span style="color:var(--primary);">*</span>
                                </label>
                                <input type="text" id="period" name="period"
                                       class="form-control @error('period') is-invalid @enderror"
                                       value="{{ old('period') }}"
                                       placeholder="e.g. Q2 2026" required>
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- TCODE --}}
                            <div class="col-12 col-sm-6">
                                <label for="tcode" class="form-label">TCODE</label>
                                <input type="text" id="tcode" name="tcode"
                                       class="form-control @error('tcode') is-invalid @enderror"
                                       value="{{ old('tcode') }}"
                                       placeholder="e.g. SU01"
                                       style="font-family:monospace;">
                                @error('tcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- UNI --}}
                            <div class="col-12 col-sm-6">
                                <label for="unit" class="form-label">UNIT</label>
                                <input type="text" id="unit" name="unit"
                                       class="form-control @error('unit') is-invalid @enderror"
                                       value="{{ old('unit') }}"
                                       placeholder="Unit name">
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            {{-- BPO --}}
                            <div class="col-12 col-sm-6">
                                <label for="bpo" class="form-label">BPO</label>
                                <input type="text" id="bpo" name="bpo"
                                       class="form-control @error('bpo') is-invalid @enderror"
                                       value="{{ old('bpo') }}"
                                       placeholder="Business Process Owner">
                                @error('bpo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Access Owner --}}
                            <div class="col-12 col-sm-6">
                                <label for="access_owner" class="form-label">User Access Matrix</label>
                                <input type="text" id="access_owner" name="access_owner"
                                       class="form-control @error('access_owner') is-invalid @enderror"
                                       value="{{ old('access_owner') }}"
                                       placeholder="Who grants access (AO)">
                                @error('access_owner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" id="saveBtn" class="btn-primary-custom"
                                    style="width:auto;padding:.65rem 2rem;font-size:.9rem;display:inline-flex;align-items:center;gap:.5rem;">
                                <i class="bi bi-check-lg"></i> Save Record
                            </button>
                             <a href="{{ route('access-matrix.sap') }}"
                               style="font-size:.85rem;color:var(--text-muted);text-decoration:none;font-weight:500;transition:color var(--transition);"
                               onmouseenter="this.style.color='var(--primary)'"
                               onmouseleave="this.style.color='var(--text-muted)'">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
</div>

@endsection

@push('scripts')
<script>
    // Profile dropdown
    const profileBtn  = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    const chevron     = document.getElementById('profileChevron');
    profileBtn.addEventListener('click', e => {
        e.stopPropagation();
        const open = profileMenu.style.display === 'block';
        profileMenu.style.display = open ? 'none' : 'block';
        chevron.style.transform   = open ? '' : 'rotate(180deg)';
    });
    document.addEventListener('click', () => { profileMenu.style.display = 'none'; chevron.style.transform = ''; });

    // Save button spinner
    document.getElementById('createForm').addEventListener('submit', function () {
        const btn = document.getElementById('saveBtn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
    });
</script>
@endpush

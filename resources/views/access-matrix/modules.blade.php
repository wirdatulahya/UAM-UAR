@extends('layouts.app')

@section('title', 'User Access Matrix Application')

@push('styles')
<style>
    .module-landing-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        min-height: 220px;
        height: 100%;
    }

    .module-landing-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-shadow);
        border-color: var(--secondary);
    }

    .module-landing-card:hover .btn-enter i {
        transform: translateX(4px);
    }

    .module-landing-card:hover .btn-enter {
        color: var(--primary-dark) !important;
    }

    .add-new-uam-card {
        background: rgba(248, 249, 250, 0.65);
        border: 2px dashed rgba(11, 46, 109, 0.22);
        border-radius: 16px;
        padding: 1.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 220px;
        height: 100%;
        cursor: pointer;
        transition: all .25s ease;
        text-decoration: none;
    }

    .add-new-uam-card:hover {
        background: #fff;
        border-color: var(--secondary);
        transform: translateY(-4px);
        box-shadow: var(--card-shadow);
    }

    .add-new-uam-card .icon-wrap {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: var(--secondary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: .85rem;
        transition: transform .25s ease, background .25s ease, color .25s ease;
        color: var(--secondary);
    }

    .add-new-uam-card:hover .icon-wrap {
        transform: scale(1.08);
        background: var(--secondary);
        color: #fff;
    }
</style>
@endpush

@section('content')

{{-- ─── App Shell ─────────────────────────────────────────────────────── --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Sidebar (Fixed full-height) --}}
<x-sidebar />

{{-- Content Wrapper --}}
<div class="app-content-wrapper">

    {{-- Topbar --}}
    <header class="app-topbar">
        <div class="topbar-left">
            <button class="btn-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
        {{-- Right — Profile Dropdown --}}
        <x-navbar-right />
    </header>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

        @php
            $moduleName = $type === 'request' ? 'Request Access Matrix' : ($type === 'approval' ? 'Approval Access Matrix' : 'Accept');
        @endphp
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => $moduleName],
        ]" />

        {{-- ── Page Header ── --}}
        <div class="mb-4 animate-in">
            <h1 style="font-size:1.45rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                <i class="bi bi-table me-2" style="color:var(--primary);"></i>
                {{ $type === 'request' ? 'Request Access Matrix Application' : ($type === 'accept' ? 'Accept Application' : 'Approval Access Matrix Application') }}
            </h1>
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                Select a target application to {{ $type === 'request' ? 'submit and manage requests' : ($type === 'accept' ? 'review TCODEs for' : 'provide final approvals for') }}.
            </p>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 mb-4 shadow-sm" role="alert" style="background:#e8f5e9;color:#1b5e20;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div class="fw-semibold">{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- ── Modules Grid ── --}}
        <div class="row g-4 animate-in animate-in-delay-1">

            {{-- 1. Dynamic Application Cards --}}
            @if(isset($applications) && $applications->count() > 0)
                @foreach($applications as $app)
                    @php
                        $appUrl = ($type === 'request') 
                            ? route('access-matrix.request.app', ['app' => $app->slug])
                            : (($type === 'accept')
                                ? route('access-matrix.uam-request.app', ['app' => $app->slug])
                                : route('access-matrix.approval.app', ['app' => $app->slug]));
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <a href="{{ $appUrl }}" class="module-landing-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:52px;height:52px;background:{{ $type === 'request' ? 'var(--secondary-light)' : ($type === 'accept' ? '#fffbeb' : '#fde8e9') }};border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi {{ $app->icon ?? ($type === 'request' ? 'bi-pc-display-horizontal' : ($type === 'accept' ? 'bi-card-checklist' : 'bi-check2-square')) }}" style="font-size:1.5rem;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};"></i>
                                </div>
                                <div>
                                    <h2 style="font-size:1.15rem;font-weight:800;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};margin:0;">{{ $app->name }}</h2>
                                    <span style="display:inline-flex;align-items:center;gap:.25rem;background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:.15rem .55rem;font-size:.65rem;font-weight:700;margin-top:.15rem;">
                                        <i class="bi bi-check-circle-fill" style="font-size:.6rem;"></i> {{ ucfirst($app->status ?? 'Active') }}
                                    </span>
                                    @if($app->slug === 'sap' && isset($pendingCount) && $pendingCount > 0)
                                    <span style="display:inline-flex;align-items:center;gap:.25rem;background:{{ $type === 'request' ? '#f3f4f6' : ($type === 'accept' ? '#fef3c7' : '#fee2e2') }};color:{{ $type === 'request' ? '#4b5563' : ($type === 'accept' ? '#92400e' : '#991b1b') }};border-radius:20px;padding:.15rem .55rem;font-size:.65rem;font-weight:700;margin-top:.15rem;margin-left:.25rem;">
                                        {{ $pendingCount }} Pending
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem;">
                                {{ $app->description ?? ($type === 'request' ? 'Submit and manage user access matrix requests for this application.' : ($type === 'accept' ? 'Review individual TCODEs for pending requests waiting for accept review.' : 'Process final approvals for requests.')) }}
                            </p>

                            <div class="d-flex align-items-center justify-content-between pt-3" style="border-top:1px solid var(--border); margin-top:auto;">
                                <span style="font-size:.7rem;color:var(--text-muted);">
                                    <i class="bi bi-clock-history me-1"></i> {{ $lastUpdated ? 'Updated ' . $lastUpdated->diffForHumans() : 'No updates' }}
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            @else
                {{-- Fallback UAM SAP Module Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ $type === 'request' ? route('access-matrix.request.sap') : ($type === 'accept' ? route('access-matrix.uam-request.sap') : route('access-matrix.approval.sap')) }}" class="module-landing-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:52px;height:52px;background:{{ $type === 'request' ? 'var(--secondary-light)' : ($type === 'accept' ? '#fffbeb' : '#fde8e9') }};border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi {{ $type === 'request' ? 'bi-pc-display-horizontal' : ($type === 'accept' ? 'bi-card-checklist' : 'bi-check2-square') }}" style="font-size:1.5rem;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};"></i>
                            </div>
                            <div>
                                <h2 style="font-size:1.15rem;font-weight:800;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};margin:0;">UAM SAP</h2>
                                <span style="display:inline-flex;align-items:center;gap:.25rem;background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:.15rem .55rem;font-size:.65rem;font-weight:700;margin-top:.15rem;">
                                    <i class="bi bi-check-circle-fill" style="font-size:.6rem;"></i> Active
                                </span>
                                @if(isset($pendingCount) && $pendingCount > 0)
                                <span style="display:inline-flex;align-items:center;gap:.25rem;background:{{ $type === 'request' ? '#f3f4f6' : ($type === 'accept' ? '#fef3c7' : '#fee2e2') }};color:{{ $type === 'request' ? '#4b5563' : ($type === 'accept' ? '#92400e' : '#991b1b') }};border-radius:20px;padding:.15rem .55rem;font-size:.65rem;font-weight:700;margin-top:.15rem;margin-left:.25rem;">
                                    {{ $pendingCount }} Pending
                                </span>
                                @endif
                            </div>
                        </div>

                        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem;">
                            {{ $type === 'request' ? 'Submit and manage user access matrix requests for SAP modules.' : ($type === 'accept' ? 'Review individual TCODEs for pending UAM requests waiting for accept review.' : 'Process final approvals for UAM SAP requests.') }}
                        </p>

                        <div class="d-flex align-items-center justify-content-between pt-3" style="border-top:1px solid var(--border); margin-top:auto;">
                            <span style="font-size:.7rem;color:var(--text-muted);">
                                <i class="bi bi-clock-history me-1"></i> {{ $lastUpdated ? 'Updated ' . $lastUpdated->diffForHumans() : 'No updates' }}
                            </span>
                        </div>
                    </a>
                </div>
            @endif

            {{-- 2. Add New UAM Dashed Card (Only for Request and Admin/PIC AO) --}}
            @if($type === 'request' && (Auth::user()->isAdmin() || Auth::user()->isPicAo()))
            <div class="col-12 col-md-6 col-xl-4">
                <div class="add-new-uam-card" data-bs-toggle="modal" data-bs-target="#modalAddUam">
                    <div class="icon-wrap">
                        <i class="bi bi-plus-lg" style="font-size:1.4rem;"></i>
                    </div>
                    <h3 style="font-size:1.05rem;font-weight:800;color:var(--secondary);margin-bottom:.25rem;">Add New UAM</h3>
                    <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Register a new target application</p>
                </div>
            </div>
            @endif

        </div>
    </main>

</div>

{{-- ── Modal Add New UAM Application ──────────────────────────────────────── --}}
@if($type === 'request' && (Auth::user()->isAdmin() || Auth::user()->isPicAo()))
<div class="modal fade" id="modalAddUam" tabindex="-1" aria-labelledby="modalAddUamLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="padding:1.5rem 1.5rem 0.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;background:var(--secondary-light);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--secondary);flex-shrink:0;">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="modalAddUamLabel" style="color:var(--secondary);font-size:1.15rem;margin:0;">Add New UAM Application</h5>
                        <p class="text-muted small mb-0" style="font-size:.78rem;">Enter the details to register a new application</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('access-matrix.application.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:1.25rem 1.5rem;">
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:.7rem;letter-spacing:.5px;">Application Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3 fs-6" placeholder="e.g. UAM SAP" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:.7rem;letter-spacing:.5px;">Description <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="description" class="form-control rounded-3" rows="3" placeholder="e.g. Submit and manage user access matrix requests for this application."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" style="background:var(--secondary);border-color:var(--secondary);">
                        <i class="bi bi-check2-circle me-1"></i> Save Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
</script>
@endpush




@extends('layouts.app')

@section('title', 'User Access Matrix Modules')

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

    .module-landing-card-disabled {
        background: rgba(255,255,255,0.7);
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        opacity: 0.8;
    }
</style>
@endpush

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">

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

{{-- ─── App Shell ──────────────────────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <aside class="sidebar d-none d-lg-block" style="width:230px;flex-shrink:0;">

        <div class="sidebar-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="sidebar-nav-item">
            <i class="bi bi-grid-fill"></i>
            Dashboard
        </a>

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
                <a href="{{ route('access-matrix.request.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.request.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Request Access Matrix
                </a>
                <a href="{{ route('access-matrix.uam-request.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.uam-request.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Accept
                </a>
                <a href="{{ route('access-matrix.approval.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.approval.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Approval Access Matrix
                </a>
            </div>
        </div>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-clipboard2-check-fill"></i>
            Access Review
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>

    </aside>

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
                {{ $type === 'request' ? 'Request Access Matrix Modules' : ($type === 'accept' ? 'Accept Modules' : 'Approval Access Matrix Modules') }}
            </h1>
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                Select a target system module to {{ $type === 'request' ? 'submit and manage requests' : ($type === 'accept' ? 'review TCODEs for' : 'provide final approvals for') }}.
            </p>
        </div>

        {{-- ── Modules Grid ── --}}
        <div class="row g-4 animate-in animate-in-delay-1">

            {{-- 1. UAM SAP Module Card --}}
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
    </main>

</div>

@endsection

@push('scripts')
<script>
</script>
@endpush




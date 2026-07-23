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
    <x-sidebar />

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
                </a>
            </div>
        </div>
    </main>

</div>

@endsection

@push('scripts')
<script>
</script>
@endpush




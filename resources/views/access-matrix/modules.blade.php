@extends('layouts.app')

@section('title', 'User Access Matrix Application')

@push('styles')
<style>
    .module-card-wrapper {
        position: relative;
        height: 100%;
    }

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

    .btn-card-delete {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: transparent;
        color: #94a3b8;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
        opacity: 0.6;
    }

    .module-card-wrapper:hover .btn-card-delete {
        opacity: 1;
    }

    .btn-card-delete:hover {
        background: #fee2e2;
        color: #dc2626;
        opacity: 1;
        transform: scale(1.1);
    }

    .add-new-uam-card {
        background: #fafbfc;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 1.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 200px;
        height: 100%;
        cursor: pointer;
        transition: all .22s ease;
        text-decoration: none;
    }

    .add-new-uam-card:hover {
        background: #fff;
        border-color: var(--secondary);
        transform: translateY(-4px);
        box-shadow: var(--card-shadow);
    }

    .add-new-uam-card .icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: var(--secondary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: .85rem;
        transition: transform .22s ease, background .22s ease, color .22s ease;
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

        {{-- ── Page Hero ── --}}
        <div class="mb-4 animate-in">
            <div style="background:linear-gradient(135deg,#071f4d 0%,#0B2E6D 50%,#1e3a8a 100%);border-radius:18px;padding:1.4rem 2rem;position:relative;overflow:hidden;box-shadow:0 8px 20px -4px rgba(11,46,109,.2);">
                <div style="position:absolute;width:240px;height:240px;background:radial-gradient(circle,rgba(59,130,246,.18) 0%,transparent 70%);border-radius:50%;right:-40px;top:-60px;pointer-events:none;"></div>
                <div style="position:absolute;width:100px;height:100px;background:rgba(255,255,255,.04);border-radius:50%;right:140px;bottom:-30px;pointer-events:none;"></div>
                <div class="position-relative" style="z-index:1;">
                    <h1 style="color:#fff;font-size:1.6rem;font-weight:800;margin:0;line-height:1.2;letter-spacing:-.4px;">
                        {{ $type === 'request' ? 'Request Access Matrix Application' : ($type === 'accept' ? 'Accept Application' : 'Approval Access Matrix Application') }}
                    </h1>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="animate-in mb-4" role="alert"
                 style="background:#e8f5e9;border:0;border-left:4px solid #2e7d32;border-radius:10px;color:#1b5e20;font-size:.875rem;padding:.75rem 1rem;display:flex;align-items:center;gap:.6rem;">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="animate-in mb-4" role="alert"
                 style="background:var(--primary-light);border:0;border-left:4px solid var(--primary);border-radius:10px;color:#7b0d0f;font-size:.875rem;padding:.75rem 1rem;">
                <div style="display:flex;align-items:center;gap:.6rem;font-weight:600;margin-bottom:.3rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Validation Error
                </div>
                <ul style="margin:0;padding-left:1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                        <div class="module-card-wrapper">
                            <a href="{{ $appUrl }}" class="module-landing-card">
                                <div class="d-flex align-items-center gap-3 mb-3" style="{{ $type === 'request' ? 'padding-right: 2rem;' : '' }}">
                                    <div style="width:52px;height:52px;background:{{ $type === 'request' ? 'var(--secondary-light)' : ($type === 'accept' ? '#fffbeb' : '#fde8e9') }};border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="bi {{ $app->icon ?? ($type === 'request' ? 'bi-pc-display-horizontal' : ($type === 'accept' ? 'bi-card-checklist' : 'bi-check2-square')) }}" style="font-size:1.5rem;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};"></i>
                                    </div>
                                    <div>
                                        <h2 style="font-size:1.15rem;font-weight:800;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};margin:0;">{{ $app->name }}</h2>
                                        @if(!empty($app->pending_count) && $app->pending_count > 0)
                                        <span style="display:inline-flex;align-items:center;gap:.25rem;background:{{ $type === 'request' ? '#f3f4f6' : ($type === 'accept' ? '#fef3c7' : '#fee2e2') }};color:{{ $type === 'request' ? '#4b5563' : ($type === 'accept' ? '#92400e' : '#991b1b') }};border-radius:20px;padding:.15rem .55rem;font-size:.65rem;font-weight:700;margin-top:.15rem;">
                                            {{ $app->pending_count }} Pending
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem;">
                                    {{ $app->description ?? ($type === 'request' ? 'Submit and manage user access matrix requests for this application.' : ($type === 'accept' ? 'Review individual TCODEs for pending requests waiting for accept review.' : 'Process final approvals for requests.')) }}
                                </p>

                                <div class="d-flex align-items-center justify-content-between pt-3" style="border-top:1px solid var(--border); margin-top:auto;">
                                    <span style="font-size:.7rem;color:var(--text-muted);">
                                        <i class="bi bi-clock-history me-1"></i> {{ !empty($app->last_updated) ? 'Updated ' . $app->last_updated->diffForHumans() : 'No updates' }}
                                    </span>
                                </div>
                            </a>

                            @if($type === 'request' && (Auth::user()->isAdmin() || Auth::user()->isPicAo()))
                            <div style="position:absolute;top:1rem;right:1rem;z-index:5;">
                                <form method="POST" action="{{ route('access-matrix.application.destroy', $app->id) }}" onsubmit="return confirm('Are you sure you want to delete application &quot;{{ addslashes($app->name) }}&quot;? All related request and matrix data will also be deleted.');" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-card-delete" title="Delete {{ $app->name }}" aria-label="Delete {{ $app->name }}" onclick="event.stopPropagation();">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Fallback UAM SAP Module Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ $type === 'request' ? route('access-matrix.request.index') : ($type === 'accept' ? route('access-matrix.uam-request.index') : route('access-matrix.approval.index')) }}" class="module-landing-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:52px;height:52px;background:{{ $type === 'request' ? 'var(--secondary-light)' : ($type === 'accept' ? '#fffbeb' : '#fde8e9') }};border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi {{ $type === 'request' ? 'bi-pc-display-horizontal' : ($type === 'accept' ? 'bi-card-checklist' : 'bi-check2-square') }}" style="font-size:1.5rem;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};"></i>
                            </div>
                            <div>
                                <h2 style="font-size:1.15rem;font-weight:800;color:{{ $type === 'request' ? 'var(--secondary)' : ($type === 'accept' ? '#f59e0b' : '#E31E24') }};margin:0;">UAM SAP</h2>
                                @if(isset($pendingCount) && $pendingCount > 0)
                                <span style="display:inline-flex;align-items:center;gap:.25rem;background:{{ $type === 'request' ? '#f3f4f6' : ($type === 'accept' ? '#fef3c7' : '#fee2e2') }};color:{{ $type === 'request' ? '#4b5563' : ($type === 'accept' ? '#92400e' : '#991b1b') }};border-radius:20px;padding:.15rem .55rem;font-size:.65rem;font-weight:700;margin-top:.15rem;">
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
                        <i class="bi bi-plus-lg" style="font-size:1.3rem;"></i>
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
                <div class="modal-body" style="padding:1.25rem 1.5rem 1.5rem;">
                    <div>
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:.7rem;letter-spacing:.5px;">Application Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3 fs-6" placeholder="e.g. UAM SAP" required autofocus>
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

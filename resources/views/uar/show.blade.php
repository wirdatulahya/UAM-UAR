@extends('layouts.app')

@section('title', $uarSession->name . ' — User Access Review')

@section('content')

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<x-sidebar />

<div class="app-content-wrapper">
    {{-- Topbar --}}
    <header class="app-topbar">
        <div class="topbar-left">
            <button class="btn-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'User Access Review', 'url' => route('uar.index')],
                ['label' => $uarSession->name],
            ]" />
        </div>
        <x-navbar-right />
    </header>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4 py-4">

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="d-flex align-items-center gap-2 mb-4 animate-in"
                 style="background:#dcfce7;border:0;border-left:4px solid #16a34a;border-radius:12px;color:#166534;font-size:.875rem;padding:.8rem 1.1rem;"
                 role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- ── Session Header & Action Toolbar ──────────────────────── --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background:#fff;">
            <div class="row align-items-center justify-content-between g-3">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <a href="{{ route('uar.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 me-1">
                            &larr; Back to Sessions
                        </a>
                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-bold">
                            {{ $uarSession->application }} &bull; Modul {{ $uarSession->module }}
                        </span>
                        <span class="badge rounded-pill bg-light text-dark border px-2.5 py-1">
                            BPO: {{ $uarSession->bpo ?: '—' }}
                        </span>
                        <span class="badge rounded-pill bg-light text-muted border px-2.5 py-1">
                            {{ $uarSession->period }}
                        </span>
                        @if($uarSession->status === 'Completed')
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                                <i class="bi bi-check-all"></i> Completed
                            </span>
                        @else
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fw-bold">
                                <i class="bi bi-hourglass-split"></i> In Review
                            </span>
                        @endif
                    </div>
                    <h2 class="fw-bold text-dark mb-1" style="font-size:1.5rem;letter-spacing:-.3px;">
                        {{ $uarSession->name }}
                    </h2>
                    <p class="text-muted small mb-0">
                        Uploaded by <span class="fw-semibold text-dark">{{ $uarSession->uploader->name ?? 'System' }}</span> on {{ $uarSession->created_at->format('d M Y, H:i') }} &bull; Source: <span class="fw-medium">{{ $uarSession->source_type }}</span> &bull; <span class="fw-semibold text-dark">{{ number_format($summary['total_employees']) }}</span> Users &bull; <span class="fw-semibold text-dark">{{ number_format($summary['total_roles']) }}</span> Roles ({{ number_format($summary['total_records']) }} T-Codes)
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-flex flex-wrap gap-2">
                        <a href="{{ route('uar.export-excel', $uarSession->id) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 py-2 fw-semibold">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('uar.export-pdf', $uarSession->id) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-3 px-3 py-2 fw-semibold">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Metrics & Filter Cards ────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            {{-- Total Roles --}}
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('uar.show', $uarSession->id) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 transition-all hover-scale {{ !request('filter') ? 'border-2 border-primary' : '' }}" style="background:#fff;">
                        <div class="text-muted small fw-semibold">Total Roles</div>
                        <div class="fs-4 fw-bold text-dark mt-1" id="statTotal">{{ number_format($summary['total_roles']) }}</div>
                        <div class="text-muted small" style="font-size:.72rem;">{{ number_format($summary['total_employees']) }} Users</div>
                    </div>
                </a>
            </div>

            {{-- Active Roles --}}
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('uar.show', [$uarSession->id, 'filter' => 'active']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 {{ request('filter') === 'active' ? 'border-2 border-success' : '' }}" style="background:#fff;border-left:4px solid #10b981 !important;">
                        <div class="text-success small fw-semibold">🟢 Active (Retained)</div>
                        <div class="fs-4 fw-bold text-success mt-1" id="statActive">{{ number_format($summary['active_roles']) }}</div>
                        <div class="text-muted small" style="font-size:.72rem;">Approved Roles</div>
                    </div>
                </a>
            </div>

            {{-- Delete Inactive >90d --}}
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ route('uar.show', [$uarSession->id, 'filter' => 'delete_90']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 {{ request('filter') === 'delete_90' ? 'border-2 border-danger' : '' }}" style="background:#fff;border-left:4px solid #ef4444 !important;">
                        <div class="text-danger small fw-semibold">🔴 Inactive > 90 Days</div>
                        <div class="fs-4 fw-bold text-danger mt-1" id="statDelete90">{{ number_format($summary['delete_90']) }}</div>
                        <div class="text-muted small" style="font-size:.72rem;">No Login / Not in Use</div>
                    </div>
                </a>
            </div>

            {{-- Delete UAM Mismatch --}}
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ route('uar.show', [$uarSession->id, 'filter' => 'delete_uam']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 {{ request('filter') === 'delete_uam' ? 'border-2 border-danger' : '' }}" style="background:#fff;border-left:4px solid #f97316 !important;">
                        <div class="text-warning-emphasis small fw-semibold">⚠️ UAM Mismatch</div>
                        <div class="fs-4 fw-bold text-dark mt-1" id="statDeleteUam">{{ number_format($summary['delete_uam']) }}</div>
                        <div class="text-muted small" style="font-size:.72rem;">Role outside baseline</div>
                    </div>
                </a>
            </div>

            {{-- Overridden / Exceptions --}}
            <div class="col-12 col-md-4 col-xl-2">
                <a href="{{ route('uar.show', [$uarSession->id, 'filter' => 'overridden']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 {{ request('filter') === 'overridden' ? 'border-2 border-info' : '' }}" style="background:#fff;border-left:4px solid #0ea5e9 !important;">
                        <div class="text-info-emphasis small fw-semibold">✏️ Manual Override</div>
                        <div class="fs-4 fw-bold text-info-emphasis mt-1" id="statOverridden">{{ number_format($summary['overridden']) }}</div>
                        <div class="text-muted small" style="font-size:.72rem;">Edited by BPO</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- ── Review Table Card ────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#fff;">
            {{-- Toolbar: Filters & Live Search --}}
            <div class="p-3 border-bottom bg-light bg-opacity-40">
                <div class="row g-2 align-items-center justify-content-between">
                    {{-- Search Form --}}
                    <div class="col-md-5 col-lg-4">
                        <form method="GET" action="{{ route('uar.show', $uarSession->id) }}">
                            @if(request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" 
                                       placeholder="Search User, Role, or T-Code..." 
                                       value="{{ request('search') }}">
                                @if(request('search'))
                                    <a href="{{ route('uar.show', [$uarSession->id, 'filter' => request('filter')]) }}" class="btn btn-outline-secondary btn-sm" title="Clear Search">
                                        <i class="bi bi-x"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Quick Filter Badges --}}
                    <div class="col-md-7 col-lg-8 text-md-end">
                        <div class="d-inline-flex flex-wrap gap-1 align-items-center">
                            <span class="text-muted small me-1">Filter:</span>
                            <a href="{{ route('uar.show', $uarSession->id) }}" 
                               class="btn btn-xs rounded-pill px-2.5 py-1 {{ !request('filter') ? 'btn-primary' : 'btn-light border' }}" style="font-size:.75rem;">
                                All ({{ $summary['total_roles'] }} Roles)
                            </a>
                            <a href="{{ route('uar.show', [$uarSession->id, 'filter' => 'active']) }}" 
                               class="btn btn-xs rounded-pill px-2.5 py-1 {{ request('filter') === 'active' ? 'btn-success' : 'btn-light border' }}" style="font-size:.75rem;">
                                Active ({{ $summary['active_roles'] }})
                            </a>
                            <a href="{{ route('uar.show', [$uarSession->id, 'filter' => 'delete_all']) }}" 
                               class="btn btn-xs rounded-pill px-2.5 py-1 {{ request('filter') === 'delete_all' ? 'btn-danger' : 'btn-light border' }}" style="font-size:.75rem;">
                                Delete ({{ $summary['delete_roles'] }})
                            </a>
                            <a href="{{ route('uar.show', [$uarSession->id, 'filter' => 'overridden']) }}" 
                               class="btn btn-xs rounded-pill px-2.5 py-1 {{ request('filter') === 'overridden' ? 'btn-info text-white' : 'btn-light border' }}" style="font-size:.75rem;">
                                Override ({{ $summary['overridden'] }})
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="table-responsive" style="max-height: 750px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" style="font-size:.825rem;">
                    <thead class="sticky-top" style="background:#071f4d;color:#fff;font-weight:600;font-size:.75rem;letter-spacing:.4px;">
                        <tr>
                            <th class="ps-3 py-3" style="width:45px;">No</th>
                            <th class="py-3" style="min-width:200px;">User</th>
                            <th class="py-3" style="min-width:180px;">Position</th>
                            <th class="py-3" style="min-width:160px;">Access Scope</th>
                            <th class="py-3 text-center" style="min-width:120px;">Latest LOGON</th>
                            <th class="py-3 text-center" style="min-width:160px;">Roles Overview</th>
                            <th class="pe-3 py-3 text-center" style="width:140px;">Review Roles</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($employees as $index => $emp)
                            @php
                                $empKey = $emp->user_id ?: $emp->full_name;
                                $empRecords = $detailedRecords->get($empKey, collect());
                                $groupedRoles = $empRecords->groupBy('role_name');
                                $rowId = 'emp-' . md5($empKey);
                                $rowNumber = ($employees->currentPage() - 1) * $employees->perPage() + $index + 1;
                                $isInactive = (strtolower($emp->latest_logon) === 'not in use' || str_contains(strtolower($emp->latest_logon), 'never'));
                                
                                // Quick status counters for this employee's roles
                                $totalRolesCount = $groupedRoles->count();
                                $empActiveRoles = 0;
                                $empDeleteRoles = 0;
                                $empReviewedCount = 0;
                                foreach($groupedRoles as $rName => $rRecs) {
                                    $rev = $rRecs->first()->final_review_result ?? '';
                                    if (!empty($rev)) {
                                        $empReviewedCount++;
                                        if (str_starts_with($rev, 'Active')) $empActiveRoles++;
                                        elseif (str_starts_with($rev, 'Delete')) $empDeleteRoles++;
                                    }
                                }
                                $isAllRolesReviewed = ($empReviewedCount === $totalRolesCount && $totalRolesCount > 0);
                            @endphp
                            {{-- ── Main Employee Row ──────────────────────── --}}
                            <tr id="row-{{ $rowId }}" class="{{ $emp->has_override ? 'bg-info bg-opacity-10' : '' }}" style="transition:background .2s; cursor:pointer;" onclick="toggleSubRows('{{ $rowId }}')">
                                {{-- No --}}
                                <td class="ps-3 py-3 text-muted fw-semibold">{{ $rowNumber }}</td>

                                {{-- User ID & Name --}}
                                <td class="py-3">
                                    <div class="fw-bold text-dark" style="font-size:.875rem;">{{ $emp->full_name ?: '—' }}</div>
                                    <div class="font-monospace text-primary small mt-0.5" style="font-size:.75rem;">
                                        <i class="bi bi-person-badge me-0.5"></i> {{ $emp->user_id ?: '—' }}
                                    </div>
                                </td>

                                {{-- Position / Jabatan --}}
                                <td class="py-3">
                                    <div class="text-dark fw-medium" style="max-width:220px;line-height:1.25;">
                                        {{ $emp->jabatan ?: '—' }}
                                    </div>
                                </td>

                                {{-- Access Scope (Roles & TCodes count) --}}
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border px-2.5 py-1 fw-semibold font-monospace" style="font-size:.74rem;">
                                        <i class="bi bi-shield-lock text-primary me-1"></i>{{ $emp->total_roles }} Role{{ $emp->total_roles > 1 ? 's' : '' }} &bull; {{ $emp->total_tcodes }} T-Code{{ $emp->total_tcodes > 1 ? 's' : '' }}
                                    </span>
                                </td>

                                {{-- Latest Logon --}}
                                <td class="py-3 text-center">
                                    @if($isInactive)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold" style="font-size:.72rem;">
                                            <i class="bi bi-clock-history me-1"></i> Not in Use
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size:.75rem;">
                                            {{ $emp->latest_logon ?: '—' }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Roles Overview Summary --}}
                                <td class="py-3 text-center">
                                    <div class="d-inline-flex align-items-center gap-1.5" id="summary-badges-{{ $rowId }}">
                                        @if($isAllRolesReviewed)
                                            @if($empActiveRoles > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:.72rem;">
                                                    <i class="bi bi-check-circle-fill me-0.5"></i> {{ $empActiveRoles }} Active
                                                </span>
                                            @endif
                                            @if($empDeleteRoles > 0)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size:.72rem;">
                                                    <i class="bi bi-trash3-fill me-0.5"></i> {{ $empDeleteRoles }} Delete
                                                </span>
                                            @endif
                                        @elseif($empReviewedCount > 0)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size:.72rem;">
                                                <i class="bi bi-hourglass-split me-0.5"></i> In Progress ({{ $empReviewedCount }}/{{ $totalRolesCount }})
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:.72rem;">
                                                Pending Review
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Expand / Collapse Button --}}
                                <td class="pe-3 py-3 text-center" onclick="event.stopPropagation();">
                                    <button type="button" onclick="toggleSubRows('{{ $rowId }}')" id="btn-toggle-{{ $rowId }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5"
                                            style="font-size:.75rem;transition:all .2s;"
                                            title="Click to view and review roles">
                                        <span>Roles</span>
                                        <i class="bi bi-chevron-down" id="icon-sub-{{ $rowId }}" style="transition:transform .25s ease;font-size:.8rem;"></i>
                                    </button>
                                </td>
                            </tr>

                            {{-- ── Expandable Details Sub-row (Per Role Review Accordion) ── --}}
                            <tr class="subrow-{{ $rowId }}" style="display:none; background:#f8fafc; border-top:1px dashed #cbd5e1; border-bottom:2px solid #e2e8f0;">
                                <td colspan="7" class="p-3">
                                    <div class="p-2">
                                        {{-- Header info inside expanded box --}}
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-person-lines-fill text-primary fs-5"></i>
                                                <div>
                                                    <span class="fw-bold text-dark" style="font-size:.9rem;">
                                                        Role Access Review for <span class="text-primary">{{ $emp->full_name }}</span>
                                                    </span>
                                                    <span class="text-muted small ms-1 font-monospace">(ID: {{ $emp->user_id ?: '—' }})</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-light text-muted border px-2.5 py-1 font-monospace" style="font-size:.75rem;">
                                                {{ $groupedRoles->count() }} Role(s) &bull; {{ $empRecords->count() }} Total T-Codes
                                            </span>
                                        </div>

                                        {{-- ── List of Roles with BPO Review per Role ── --}}
                                        <div class="d-flex flex-column gap-3">
                                            @foreach($groupedRoles as $roleName => $roleRecs)
                                                @php
                                                    $firstRec = $roleRecs->first();
                                                    $roleRowId = $rowId . '-role-' . md5($roleName);
                                                    $roleReview = $firstRec->final_review_result;
                                                    $systemReview = $firstRec->system_review_result;
                                                    $systemNotes = $firstRec->system_review_notes;
                                                    $hasRoleOverride = (bool)$firstRec->is_overridden;
                                                    $roleLogon = $roleRecs->pluck('last_logon')->filter()->sortDesc()->first() ?: '—';
                                                    $isRoleInactive = (strtolower($roleLogon) === 'not in use' || str_contains(strtolower($roleLogon), 'never'));
                                                @endphp
                                                <div class="card border rounded-3 shadow-sm overflow-hidden" id="card-{{ $roleRowId }}" style="background:#fff;border-color:#e2e8f0 !important;">
                                                    {{-- Role Card Header --}}
                                                    <div class="card-header py-2.5 px-3 bg-light bg-opacity-75 d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-bottom:1px solid #e2e8f0;">
                                                        {{-- Left: Role Name & Info --}}
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:.68rem;">ROLE</span>
                                                            <div>
                                                                <div class="font-monospace fw-bold text-dark" style="font-size:.85rem;">
                                                                    {{ $roleName ?: '—' }}
                                                                </div>
                                                                @if($firstRec->role_description)
                                                                    <div class="text-muted small mt-0.5" style="font-size:.74rem;">
                                                                        {{ $firstRec->role_description }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Right: Last LOGON, System Recommendation & BPO Review Dropdown per Role --}}
                                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                                            {{-- Last LOGON (Di atas kartu role) --}}
                                                            <div class="text-start">
                                                                <span class="text-muted d-block" style="font-size:.7rem;">Last LOGON:</span>
                                                                <div class="font-monospace fw-semibold text-dark" style="font-size:.78rem;">
                                                                    @if($isRoleInactive)
                                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5" style="font-size:.7rem;">Not in Use</span>
                                                                    @else
                                                                        {{ $roleLogon }}
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            {{-- System Recommendation Badge --}}
                                                            <div class="text-start">
                                                                <span class="text-muted d-block" style="font-size:.7rem;">System Recommendation:</span>
                                                                @if(empty($systemReview))
                                                                    <span class="text-muted small">—</span>
                                                                @elseif(str_starts_with($systemReview, 'Active'))
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-semibold" 
                                                                          data-bs-toggle="tooltip" 
                                                                          title="{{ $systemNotes ?: 'Active login & valid access' }}"
                                                                          style="font-size:.72rem;">
                                                                        <i class="bi bi-check-circle-fill me-1"></i> {{ $systemReview }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-semibold" 
                                                                          data-bs-toggle="tooltip" 
                                                                          title="{{ $systemNotes ?: 'Flagged for revocation' }}"
                                                                          style="font-size:.72rem;">
                                                                        <i class="bi bi-trash3-fill me-1"></i> {{ $systemReview }}
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            {{-- BPO Review Dropdown per Role --}}
                                                            <div>
                                                                <span class="text-muted d-block fw-semibold" style="font-size:.7rem;">Review BPO:</span>
                                                                <div class="d-flex align-items-center gap-1">
                                                                    <select class="form-select form-select-sm fw-semibold role-review-dropdown shadow-sm" 
                                                                            data-user-id="{{ $empKey }}"
                                                                            data-role-name="{{ $roleName }}"
                                                                            data-role-card-id="{{ $roleRowId }}"
                                                                            data-parent-row-id="{{ $rowId }}"
                                                                            style="font-size:.75rem;min-width:200px;
                                                                            @if(empty($roleReview))
                                                                                background-color:#ffffff;color:#64748b;border-color:#cbd5e1;
                                                                            @elseif(str_starts_with($roleReview, 'Active'))
                                                                                border-color:#86efac;color:#15803d;background-color:#f0fdf4;
                                                                            @else
                                                                                border-color:#fca5a5;color:#b91c1c;background-color:#fef2f2;
                                                                            @endif">
                                                                        <option value="" {{ empty($roleReview) ? 'selected' : '' }} style="color:#64748b;background-color:#ffffff;">
                                                                            -- Select Decision --
                                                                        </option>
                                                                        <optgroup label="Retain / Active">
                                                                            @foreach($reviewOptions as $optVal => $optMeta)
                                                                                @if($optMeta['type'] === 'active')
                                                                                    <option value="{{ $optVal }}" {{ $roleReview === $optVal ? 'selected' : '' }} style="color:#15803d;background-color:#ffffff;">
                                                                                        {{ $optVal }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                        </optgroup>
                                                                        <optgroup label="Revoke / Delete">
                                                                            @foreach($reviewOptions as $optVal => $optMeta)
                                                                                @if($optMeta['type'] === 'delete')
                                                                                    <option value="{{ $optVal }}" {{ $roleReview === $optVal ? 'selected' : '' }} style="color:#b91c1c;background-color:#ffffff;">
                                                                                        {{ $optVal }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                        </optgroup>
                                                                    </select>

                                                                    <span id="override-badge-{{ $roleRowId }}" class="badge bg-info text-white rounded-circle p-1 {{ $hasRoleOverride ? '' : 'd-none' }}" title="Manually modified from system recommendation">
                                                                        <i class="bi bi-pencil-fill" style="font-size:.65rem;"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Inside Role Card: Clean T-Codes List --}}
                                                    <div class="table-responsive bg-white">
                                                        <table class="table table-sm table-hover align-middle mb-0" style="font-size:.75rem;">
                                                            <thead style="background:#f8fafc;color:#475569;font-size:.7rem;font-weight:600;">
                                                                <tr>
                                                                    <th class="ps-3 py-1.5" style="width:160px;">T-Code</th>
                                                                    <th class="pe-3 py-1.5">T-Code Description</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($roleRecs as $rec)
                                                                    <tr>
                                                                        <td class="ps-3 py-1.5">
                                                                            <span class="badge bg-light text-dark border font-monospace px-2 py-0.5" style="font-size:.72rem;">
                                                                                {{ $rec->tcode }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="pe-3 py-1.5 text-dark">
                                                                            {{ $rec->tcode_description ?: '—' }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-3 d-block mb-2 text-secondary"></i>
                                    No access records found matching this filter/search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($employees->hasPages())
                <div class="p-3 border-top bg-light bg-opacity-25 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="text-muted small" style="font-size:.78rem;">
                        Showing <span class="fw-semibold text-dark">{{ $employees->firstItem() }}</span> to <span class="fw-semibold text-dark">{{ $employees->lastItem() }}</span> of <span class="fw-semibold text-dark">{{ $employees->total() }}</span> users ({{ number_format($summary['total_roles']) }} total roles)
                    </div>
                    <div>
                        {{ $employees->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Bottom Actions Bar ────────────────────────────────────────── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 mb-5 pt-1">
            <div>
                <form method="POST" action="{{ route('uar.bulk-accept', $uarSession->id) }}" onsubmit="return confirm('Reset all records to automated System Recommendations?');" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary rounded-3 px-4 py-2 fw-semibold" title="Apply all automated system recommendations">
                        Accept All System
                    </button>
                </form>
            </div>

            <div>
                @if($uarSession->status !== 'Completed')
                <form method="POST" action="{{ route('uar.complete', $uarSession->id) }}" onsubmit="return confirm('Are you sure you want to submit and complete this UAR session?');" class="d-inline">
                    @csrf
                    <button type="submit" class="btn rounded-3 px-4 py-2 fw-bold text-white shadow-sm" style="background:#0B2E6D;">
                        Submit
                    </button>
                </form>
                @else
                <button class="btn btn-success rounded-3 px-4 py-2 fw-semibold" disabled>
                    Submitted / Completed
                </button>
                @endif
            </div>
        </div>

    </main>
</div>

{{-- Toast Notification --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="statusToast" class="toast align-items-center text-white bg-dark border-0 rounded-3 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                <i class="bi bi-check-circle-fill text-success"></i> Status updated successfully.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// Bootstrap Tooltips
document.addEventListener("DOMContentLoaded", function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Expand/Collapse Sub-rows Toggle
window.toggleSubRows = function(rowId) {
    const subrows = document.querySelectorAll('.subrow-' + rowId);
    const icon = document.getElementById('icon-sub-' + rowId);
    const btn = document.getElementById('btn-toggle-' + rowId);

    if (subrows.length === 0) return;
    const isHidden = subrows[0].style.display === 'none' || subrows[0].style.display === '';

    if (icon) {
        icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
    }
    if (btn) {
        if (isHidden) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary', 'text-white');
        } else {
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-outline-primary');
        }
    }

    subrows.forEach(row => {
        row.style.display = isHidden ? 'table-row' : 'none';
    });
};

// AJAX Handler for Live Dropdown Status Change per Role
document.querySelectorAll('.role-review-dropdown').forEach(function(select) {
    select.addEventListener('change', function() {
        const userId = this.getAttribute('data-user-id');
        const roleName = this.getAttribute('data-role-name');
        const roleCardId = this.getAttribute('data-role-card-id');
        const parentRowId = this.getAttribute('data-parent-row-id');
        const selectedValue = this.value;
        const selectEl = this;

        // Dynamic styling for dropdown
        if (!selectedValue) {
            selectEl.style.borderColor = '#cbd5e1';
            selectEl.style.color = '#64748b';
            selectEl.style.backgroundColor = '#ffffff';
        } else if (selectedValue.startsWith('Active')) {
            selectEl.style.borderColor = '#86efac';
            selectEl.style.color = '#15803d';
            selectEl.style.backgroundColor = '#f0fdf4';
        } else {
            selectEl.style.borderColor = '#fca5a5';
            selectEl.style.color = '#b91c1c';
            selectEl.style.backgroundColor = '#fef2f2';
        }

        fetch(`{{ route('uar.role-review', $uarSession->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                role_name: roleName,
                final_review_result: selectedValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update override badge on role card
                const overrideBadge = document.getElementById(`override-badge-${roleCardId}`);
                if (overrideBadge) {
                    if (data.is_overridden) {
                        overrideBadge.classList.remove('d-none');
                    } else {
                        overrideBadge.classList.add('d-none');
                    }
                }

                // Update top summary cards dynamically
                if (data.session_stats) {
                    const setVal = (id, val) => {
                        const el = document.getElementById(id);
                        if (el && val !== undefined) el.innerText = Number(val).toLocaleString();
                    };
                    setVal('statTotal', data.session_stats.total_roles);
                    setVal('statActive', data.session_stats.active_roles);
                    setVal('statDelete90', data.session_stats.delete_90);
                    setVal('statDeleteUam', data.session_stats.delete_uam);
                    setVal('statOverridden', data.session_stats.overridden);
                }

                // Update summary badges in parent employee row
                if (parentRowId) {
                    const subrowEl = document.querySelector(`.subrow-${parentRowId}`);
                    if (subrowEl) {
                        let activeCount = 0;
                        let deleteCount = 0;
                        let reviewedCount = 0;
                        const allDropdowns = subrowEl.querySelectorAll('.role-review-dropdown');
                        const totalRoles = allDropdowns.length;

                        allDropdowns.forEach(sel => {
                            const val = sel.value;
                            if (val) {
                                reviewedCount++;
                                if (val.startsWith('Active')) activeCount++;
                                else if (val.startsWith('Delete')) deleteCount++;
                            }
                        });

                        const badgeContainer = document.getElementById(`summary-badges-${parentRowId}`);
                        if (badgeContainer) {
                            let badgeHtml = '';
                            if (reviewedCount === totalRoles && totalRoles > 0) {
                                // Semua role sudah diisi keputusannya!
                                if (activeCount > 0) {
                                    badgeHtml += `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:.72rem;"><i class="bi bi-check-circle-fill me-0.5"></i> ${activeCount} Active</span> `;
                                }
                                if (deleteCount > 0) {
                                    badgeHtml += `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size:.72rem;"><i class="bi bi-trash3-fill me-0.5"></i> ${deleteCount} Delete</span>`;
                                }
                            } else if (reviewedCount > 0) {
                                // Belum lengkap semua role di-review
                                badgeHtml = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size:.72rem;"><i class="bi bi-hourglass-split me-0.5"></i> In Progress (${reviewedCount}/${totalRoles})</span>`;
                            } else {
                                // Belum ada yang di-review
                                badgeHtml = `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:.72rem;">Pending Review</span>`;
                            }
                            badgeContainer.innerHTML = badgeHtml;
                        }
                    }
                }

                // Show toast
                const toastEl = document.getElementById('statusToast');
                document.getElementById('toastMessage').innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> ' + data.message;
                const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
                toast.show();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to update review status. Please try again.');
        });
    });
});
</script>

@endsection

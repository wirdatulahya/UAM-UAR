@extends('layouts.app')

@section('title', 'User Access Review - ' . $currentModule)

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
        </div>
        <x-navbar-right />
    </header>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4 py-4">

        @php
            $appName = $currentApp->slug === 'sap' ? 'UAR SAP' : (str_starts_with($currentApp->name, 'UAR ') ? $currentApp->name : (str_starts_with($currentApp->name, 'UAM ') ? 'UAR ' . substr($currentApp->name, 4) : 'UAR ' . $currentApp->name));
        @endphp
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'User Access Review', 'url' => route('uar.index')],
            ['label' => $appName, 'url' => route('uar.app', ['app' => $currentApp->slug])],
            ['label' => $currentModule],
        ]" />



        {{-- ── Hero Section ─────────────────────────────────────────── --}}
        <div class="mb-4">
            <div class="animate-in" style="background:linear-gradient(135deg,#071f4d 0%,#0B2E6D 50%,#1e3a8a 100%);border-radius:18px;padding:1.4rem 2rem;position:relative;overflow:hidden;box-shadow:0 8px 20px -4px rgba(11,46,109,.2);">
                <div style="position:absolute;width:240px;height:240px;background:radial-gradient(circle,rgba(59,130,246,.18) 0%,transparent 70%);border-radius:50%;right:-40px;top:-60px;pointer-events:none;"></div>
                <div style="position:absolute;width:100px;height:100px;background:rgba(255,255,255,.04);border-radius:50%;right:140px;bottom:-30px;pointer-events:none;"></div>
                <div class="position-relative" style="z-index:1;">

                    <h1 style="color:#fff;font-size:1.6rem;font-weight:800;margin-bottom:0;line-height:1.2;letter-spacing:-.4px;display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;">
                        User Access Review <span style="font-size:1.1rem;font-weight:800;color:var(--secondary);background:#e0edff;border-radius:10px;padding:.2rem .75rem;display:inline-flex;letter-spacing:0.5px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">{{ strtoupper($currentModule) }}</span>
                    </h1>
                </div>
            </div>
        </div>

        {{-- ── Global Stats Grid ──────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #0B2E6D !important;">
                    <div class="text-muted small fw-semibold">Audit Sessions</div>
                    <div class="fs-3 fw-bolder text-dark mt-1 mb-0">{{ number_format($globalStats['total_sessions']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #0B2E6D !important;">
                    <div class="text-muted small fw-semibold">Total Employees</div>
                    <div class="fs-3 fw-bolder text-dark mt-1 mb-0">{{ number_format($globalStats['total_employees']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #0B2E6D !important;">
                    <div class="text-muted small fw-semibold">Active (Retained)</div>
                    <div class="fs-3 fw-bolder text-success mt-1 mb-0">{{ number_format($globalStats['total_active']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #0B2E6D !important;">
                    <div class="text-muted small fw-semibold">Revoke / Delete</div>
                    <div class="fs-3 fw-bolder text-danger mt-1 mb-0">{{ number_format($globalStats['total_delete']) }}</div>
                </div>
            </div>
        </div>

        {{-- ── Session Table Card ───────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background:#fff;">
            {{-- Search & Filter Bar --}}
            <div class="p-3 border-bottom bg-light bg-opacity-50">
                <form method="GET" action="{{ route('uar.module.sessions', ['app' => $currentApp->slug, 'module' => $currentModule]) }}" class="row g-2 align-items-center">
                    <div class="col-md-8 col-lg-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                   placeholder="Search session name or period..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-4">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="In Review" {{ request('status') === 'In Review' ? 'selected' : '' }}>In Review</option>
                            <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <tr class="text-uppercase text-muted small fw-bold" style="font-size:.73rem;letter-spacing:.5px;">
                            <th class="ps-4 py-3" style="width:280px;">Session Name</th>
                            <th class="py-3">Period</th>
                            <th class="py-3 text-center" style="width:120px;">Employees</th>
                            <th class="py-3" style="width:220px;">Decision Ratio</th>
                            <th class="py-3 text-center" style="width:120px;">Status</th>
                            <th class="py-3">Created By</th>
                            <th class="pe-4 py-3 text-end" style="width:130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            @php
                                $total = max(1, $session->total_records);
                                $activePct = round(($session->total_active / $total) * 100);
                                $deletePct = round(($session->total_delete / $total) * 100);
                                $empCount = $session->employee_count ?? $session->records()->distinct('user_id')->count('user_id');
                            @endphp
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold shadow-xs"
                                             style="width:42px;height:42px;background:#f1f5f9;color:#0B2E6D;font-size:.85rem;border:1px solid #e2e8f0;flex-shrink:0;">
                                             {{ substr($session->module, 0, 3) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('uar.show', $session->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                                {{ str_replace(' ' . $session->module, '', \Illuminate\Support\Str::beforeLast($session->name, ' - ')) }}
                                            </a>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $session->period }}</div>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="fw-bold text-dark fs-6">{{ number_format($empCount) }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex justify-content-between small mb-1" style="font-size:.72rem;">
                                        <span class="text-success fw-bold">
                                            {{ $session->total_active }} Active ({{ $activePct }}%)
                                        </span>
                                        <span class="text-danger fw-bold">
                                            {{ $session->total_delete }} Delete ({{ $deletePct }}%)
                                        </span>
                                    </div>
                                    <div class="progress" style="height:6px;background:#fee2e2;border-radius:99px;">
                                        <div class="progress-bar bg-success" style="width: {{ $activePct }}%;border-radius:99px 0 0 99px;"></div>
                                        <div class="progress-bar bg-danger" style="width: {{ $deletePct }}%;border-radius:0 99px 99px 0;"></div>
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    @if($session->status === 'Completed')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-semibold rounded-pill" style="font-size:.75rem;">
                                            Completed
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold rounded-pill" style="font-size:.75rem;">
                                            In Review
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="fw-medium text-dark">{{ $session->uploader->name ?? 'System' }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">{{ $session->created_at->format('d M Y, H:i') }}</div>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                        <a href="{{ route('uar.show', $session->id) }}" 
                                           style="display:inline-flex;align-items:center;gap:.35rem;background:linear-gradient(135deg,#0B2E6D,#1a4d9e);color:#fff;border:none;border-radius:6px;padding:.3rem .65rem;font-size:.75rem;font-weight:700;text-decoration:none;box-shadow:0 2px 5px rgba(11,46,109,.15);transition:all .15s ease;"
                                           onmouseenter="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 8px rgba(11,46,109,.25)';"
                                           onmouseleave="this.style.transform='';this.style.boxShadow='0 2px 5px rgba(11,46,109,.15)';"
                                           title="Open Review">
                                            <i class="bi bi-pencil-square" style="font-size:.75rem;"></i>
                                            <span>Review</span>
                                        </a>

                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border rounded-2 p-0" 
                                                    style="width:26px;height:26px;display:flex;align-items:center;justify-content:center;color:#64748b;background:#fff;" 
                                                    data-bs-toggle="dropdown" aria-expanded="false" title="Options">
                                                <i class="bi bi-three-dots-vertical" style="font-size:.75rem;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size:.85rem;">
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('uar.export-excel', $session->id) }}">
                                                        <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i> Export Excel (.xlsx)
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('uar.export-pdf', $session->id) }}" target="_blank">
                                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i> Export PDF Report
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('uar.destroy', $session->id) }}" onsubmit="return confirm('Are you sure you want to delete this UAR session?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                                            <i class="bi bi-trash3 me-2"></i> Delete Session
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding:4rem 1rem;text-align:center;">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:1rem;">
                                        <button type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#uploadUarModal"
                                            id="emptyStateUploadBtn"
                                            title="Upload UAR Excel file"
                                            style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#0B2E6D 0%,#1a4d9e 100%);border:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 18px rgba(11,46,109,.28);transition:transform .2s,box-shadow .2s;"
                                            onmouseenter="this.style.transform='scale(1.1)';this.style.boxShadow='0 8px 28px rgba(11,46,109,.40)';"
                                            onmouseleave="this.style.transform='';this.style.boxShadow='0 4px 18px rgba(11,46,109,.28)';"
                                        >
                                            <i class="bi bi-plus-lg" style="font-size:1.75rem;color:#fff;line-height:1;"></i>
                                        </button>
                                        <div>
                                            <h3 style="font-size:1rem;font-weight:800;color:#0B2E6D;margin:0 0 .3rem;">No sessions found for {{ $currentModule }}</h3>
                                            <p style="font-size:.82rem;color:#64748b;margin:0;">Click the button above to upload your first UAR Excel file for module {{ $currentModule }}.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($sessions->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="7" style="padding:1.1rem 1rem;text-align:center;border-top:1px solid #e2e8f0;background:#fcfcff;">
                                <button type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#uploadUarModal"
                                    id="addRowUploadBtn"
                                    title="Upload new UAR Excel session"
                                    style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#0B2E6D 0%,#1a4d9e 100%);border:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 3px 10px rgba(11,46,109,.25);transition:transform .2s,box-shadow .2s;"
                                    onmouseenter="this.style.transform='scale(1.12)';this.style.boxShadow='0 6px 18px rgba(11,46,109,.38)';"
                                    onmouseleave="this.style.transform='';this.style.boxShadow='0 3px 10px rgba(11,46,109,.25)';"
                                >
                                    <i class="bi bi-plus-lg" style="font-size:1.1rem;color:#fff;line-height:1;"></i>
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            @if($sessions->hasPages())
                <div class="p-3 border-top bg-light bg-opacity-25 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="text-muted small" style="font-size:.78rem;">
                        Showing <span class="fw-semibold text-dark">{{ $sessions->firstItem() }}</span> to <span class="fw-semibold text-dark">{{ $sessions->lastItem() }}</span> of <span class="fw-semibold text-dark">{{ $sessions->total() }}</span> sessions
                    </div>
                    <div>
                        {{ $sessions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>

{{-- ── Upload Modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="uploadUarModal" tabindex="-1" aria-labelledby="uploadUarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('uar.import-multi') }}" enctype="multipart/form-data" id="uarMultiImportForm">
                @csrf
                <div class="modal-header border-0 pb-2 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2.5">
                        <div style="width:38px;height:38px;background:linear-gradient(135deg, #e0edff 0%, #d0e1fd 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#0B2E6D;flex-shrink:0;">
                            <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="uploadUarModalLabel" style="font-size:1.15rem;">
                                Import User Access Review
                            </h5>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 pt-2">
                    {{-- 4 Files Grid --}}
                    <div class="row g-3 mb-3">
                        {{-- 1. LIST_USER_ROLES --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                1. LIST_USER_ROLES (.xlsx) <span class="text-danger">*</span>
                            </label>
                            <div class="border border-2 border-dashed rounded-3 p-3 text-center position-relative file-card d-flex flex-column align-items-center justify-content-center" style="background:#f8fafc;cursor:pointer;min-height:95px;" onclick="document.getElementById('inputUserRoles').click();">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#eef4ff;">
                                    <i class="bi bi-file-earmark-excel-fill fs-4" style="color:#0B2E6D;"></i>
                                </div>
                                <div id="labelUserRoles"></div>
                                <input type="file" name="file_user_roles" id="inputUserRoles" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelUserRoles')">
                            </div>
                        </div>

                        {{-- 2. LIST_ROLE_TCODES --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                2. LIST_ROLE_TCODES (.xlsx) <span class="text-danger">*</span>
                            </label>
                            <div class="border border-2 border-dashed rounded-3 p-3 text-center position-relative file-card d-flex flex-column align-items-center justify-content-center" style="background:#f8fafc;cursor:pointer;min-height:95px;" onclick="document.getElementById('inputRoleTcodes').click();">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#eef4ff;">
                                    <i class="bi bi-file-earmark-excel-fill fs-4" style="color:#0B2E6D;"></i>
                                </div>
                                <div id="labelRoleTcodes"></div>
                                <input type="file" name="file_role_tcodes" id="inputRoleTcodes" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelRoleTcodes')">
                            </div>
                        </div>

                        {{-- 3. LIST_OF_TCODES --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                3. LIST_OF_TCODES (.xlsx) <span class="text-danger">*</span>
                            </label>
                            <div class="border border-2 border-dashed rounded-3 p-3 text-center position-relative file-card d-flex flex-column align-items-center justify-content-center" style="background:#f8fafc;cursor:pointer;min-height:95px;" onclick="document.getElementById('inputTcodes').click();">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#eef4ff;">
                                    <i class="bi bi-file-earmark-excel-fill fs-4" style="color:#0B2E6D;"></i>
                                </div>
                                <div id="labelTcodes"></div>
                                <input type="file" name="file_tcodes" id="inputTcodes" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelTcodes')">
                            </div>
                        </div>

                        {{-- 4. LIST_USER_LAST_LOGON --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                4. LIST_USER_LAST_LOGON (.xlsx) <span class="text-danger">*</span>
                            </label>
                            <div class="border border-2 border-dashed rounded-3 p-3 text-center position-relative file-card d-flex flex-column align-items-center justify-content-center" style="background:#f8fafc;cursor:pointer;min-height:95px;" onclick="document.getElementById('inputLogon').click();">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:#eef4ff;">
                                    <i class="bi bi-file-earmark-excel-fill fs-4" style="color:#0B2E6D;"></i>
                                </div>
                                <div id="labelLogon"></div>
                                <input type="file" name="file_logon" id="inputLogon" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelLogon')">
                            </div>
                        </div>
                    </div>

                    {{-- Target Module & Period Config --}}
                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Target Module <span class="text-danger">*</span></label>
                                <input type="text" name="module" class="form-control form-control-sm fw-bold bg-white text-uppercase" value="{{ $currentModule }}" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Period <span class="text-danger">*</span></label>
                                <input type="text" name="period" class="form-control form-control-sm bg-white" placeholder="e.g. Q2 2026" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 text-sm fw-bold" style="background:#0B2E6D;border-color:#0B2E6D;" id="btnSubmitUarMulti">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function setFileNameBadge(input, labelId) {
        const label = document.getElementById(labelId);
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            const fileSize = (input.files[0].size / 1024 / 1024).toFixed(2);
            label.innerHTML = `
                <div class="mt-2 text-dark fw-bold text-truncate" style="max-width:180px;font-size:.78rem;" title="${fileName}">${fileName}</div>
                <div class="text-success small" style="font-size:.7rem;"><i class="bi bi-check2-circle"></i> ${fileSize} MB</div>
            `;
        }
    }

    document.getElementById('uarMultiImportForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitUarMulti');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Merging & Analyzing...';
        }
    });
</script>
@endpush

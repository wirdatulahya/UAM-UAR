@extends('layouts.app')

@section('title', 'User Access Review')

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
                ['label' => 'User Access Review'],
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

        @if (session('error'))
            <div class="d-flex align-items-center gap-2 mb-4 animate-in"
                 style="background:#fee2e2;border:0;border-left:4px solid #dc2626;border-radius:12px;color:#991b1b;font-size:.875rem;padding:.8rem 1.1rem;"
                 role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- ── Hero Section ─────────────────────────────────────────── --}}
        <div class="mb-4">
            <div class="animate-in" style="background:linear-gradient(135deg,#071f4d 0%,#0B2E6D 50%,#1e3a8a 100%);border-radius:18px;padding:1.4rem 2rem;position:relative;overflow:hidden;box-shadow:0 8px 20px -4px rgba(11,46,109,.2);">
                {{-- Decorative circles --}}
                <div style="position:absolute;width:240px;height:240px;background:radial-gradient(circle,rgba(59,130,246,.18) 0%,transparent 70%);border-radius:50%;right:-40px;top:-60px;pointer-events:none;"></div>
                <div style="position:absolute;width:100px;height:100px;background:rgba(255,255,255,.04);border-radius:50%;right:140px;bottom:-30px;pointer-events:none;"></div>
                <div class="position-relative" style="z-index:1;">
                    <div style="display:inline-flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2);border-radius:99px;padding:.2rem .75rem;font-size:.7rem;font-weight:700;color:rgba(255,255,255,.9);letter-spacing:.5px;text-transform:uppercase;margin-bottom:.5rem;">
                        <i class="bi bi-shield-check" style="color:#60a5fa;"></i>
                        Governance & Compliance
                    </div>
                    <h1 style="color:#fff;font-size:1.6rem;font-weight:800;margin-bottom:0;line-height:1.2;letter-spacing:-.4px;">
                        User Access Review
                    </h1>
                </div>
            </div>
        </div>

        {{-- ── Global Stats Grid ──────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #3b82f6 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Total Audit Sessions</span>
                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:#eff6ff;color:#2563eb;width:34px;height:34px;">
                            <i class="bi bi-folder2-open fs-6"></i>
                        </div>
                    </div>
                    <div class="fs-3 fw-bolder text-dark mb-0">{{ number_format($globalStats['total_sessions']) }}</div>
                    <div class="text-muted small mt-1" style="font-size:.72rem;">All UAR Batches</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #6366f1 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Total Employees</span>
                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:#eef2ff;color:#4f46e5;width:34px;height:34px;">
                            <i class="bi bi-people-fill fs-6"></i>
                        </div>
                    </div>
                    <div class="fs-3 fw-bolder text-dark mb-0">{{ number_format($globalStats['total_employees']) }}</div>
                    <div class="text-muted small mt-1" style="font-size:.72rem;">{{ number_format($globalStats['total_records']) }} Access Items</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #10b981 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Active (Retained)</span>
                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:#ecfdf5;color:#059669;width:34px;height:34px;">
                            <i class="bi bi-check-circle-fill fs-6"></i>
                        </div>
                    </div>
                    <div class="fs-3 fw-bolder text-success mb-0">{{ number_format($globalStats['total_active']) }}</div>
                    <div class="text-muted small mt-1" style="font-size:.72rem;">Approved Employees</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background:#fff;border-left:4px solid #ef4444 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Revoke / Delete</span>
                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:#fef2f2;color:#dc2626;width:34px;height:34px;">
                            <i class="bi bi-trash3-fill fs-6"></i>
                        </div>
                    </div>
                    <div class="fs-3 fw-bolder text-danger mb-0">{{ number_format($globalStats['total_delete']) }}</div>
                    <div class="text-muted small mt-1" style="font-size:.72rem;">Flagged for Deletion</div>
                </div>
            </div>
        </div>

        {{-- ── Session Table Card ───────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background:#fff;">
            {{-- Search & Filter Bar --}}
            <div class="p-3 border-bottom bg-light bg-opacity-50">
                <form method="GET" action="{{ route('uar.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                   placeholder="Search session name, module, or BPO..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <select name="module" class="form-select" onchange="this.form.submit()">
                            <option value="">All Modules</option>
                            @foreach($modules as $m)
                                <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="In Review" {{ request('status') === 'In Review' ? 'selected' : '' }}>In Review</option>
                            <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-12 col-lg-3 text-lg-end text-muted small mt-2 mt-lg-0">
                        Showing {{ $sessions->total() }} audit session(s)
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <tr class="text-uppercase text-muted small fw-bold" style="font-size:.73rem;letter-spacing:.5px;">
                            <th class="ps-4 py-3" style="width:260px;">Session & Module</th>
                            <th class="py-3">BPO & Period</th>
                            <th class="py-3 text-center" style="width:140px;">Scope (Employees)</th>
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
                                                {{ $session->name }}
                                            </a>
                                            <div class="d-flex align-items-center gap-2 mt-0.5">
                                                <span class="badge bg-light text-muted border" style="font-size:.7rem;">
                                                    {{ $session->application }} &bull; {{ $session->module }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $session->bpo ?: '—' }}</div>
                                    <div class="text-muted" style="font-size:.78rem;">{{ $session->period }}</div>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="fw-bold text-dark fs-6">{{ number_format($empCount) }}</span>
                                    <div class="text-muted" style="font-size:.72rem;">Employees ({{ number_format($session->total_records) }} items)</div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex justify-content-between small mb-1" style="font-size:.72rem;">
                                        <span class="text-success fw-bold">
                                            <i class="bi bi-check2"></i> {{ $session->total_active }} Active ({{ $activePct }}%)
                                        </span>
                                        <span class="text-danger fw-bold">
                                            <i class="bi bi-trash3"></i> {{ $session->total_delete }} Delete ({{ $deletePct }}%)
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
                                            <i class="bi bi-check-circle-fill me-1"></i> Completed
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold rounded-pill" style="font-size:.75rem;">
                                            <i class="bi bi-clock-history me-1"></i> In Review
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
                                            <h3 style="font-size:1rem;font-weight:800;color:#0B2E6D;margin:0 0 .3rem;">No sessions found</h3>
                                            <p style="font-size:.82rem;color:#64748b;margin:0;">Click the button above to upload your first UAR Excel file.</p>
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
                <div class="p-3 border-top bg-light bg-opacity-25 d-flex justify-content-end">
                    {{ $sessions->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </main>
</div>

{{-- ── Upload Modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="uploadUarModal" tabindex="-1" aria-labelledby="uploadUarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:linear-gradient(135deg, #e0edff 0%, #d0e1fd 100%);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#0B2E6D;flex-shrink:0;" class="shadow-sm">
                        <i class="bi bi-cloud-arrow-up-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="uploadUarModalLabel" style="font-size:1.2rem;">
                            Import User Access Review (UAR)
                        </h5>
                        <p class="text-muted small mb-0 mt-0.5">Pilih metode upload: gabung 4 file mentah SAP secara instan atau upload file yang sudah digabung.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Nav Tabs --}}
            <div class="px-4 pt-3">
                <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 border" id="uarUploadTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2 rounded-3 text-primary d-flex align-items-center justify-content-center gap-2" id="multi-tab" data-bs-toggle="tab" data-bs-target="#multi-pane" type="button" role="tab">
                            <i class="bi bi-lightning-charge-fill text-warning"></i> ⚡ 4-Files SAP Raw Extract <span class="badge bg-primary text-white ms-1" style="font-size:.68rem;">Auto-Merge</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2 rounded-3 text-secondary d-flex align-items-center justify-content-center gap-2" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-pane" type="button" role="tab">
                            <i class="bi bi-file-earmark-spreadsheet"></i> 📄 Single Pre-Merged File
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                {{-- TAB 1: 4-Files SAP Auto-Merge --}}
                <div class="tab-pane fade show active" id="multi-pane" role="tabpanel">
                    <form method="POST" action="{{ route('uar.import-multi') }}" enctype="multipart/form-data" id="uarMultiImportForm">
                        @csrf
                        <div class="modal-body p-4 pt-3">
                            <div class="alert alert-info py-2 px-3 border-0 rounded-3 mb-3 d-flex align-items-center gap-2" style="background:#f0f7ff;color:#0369a1;font-size:.82rem;">
                                <i class="bi bi-info-circle-fill fs-6 flex-shrink-0"></i>
                                <span>Sistem akan otomatis menggabungkan (merge/join) data User, Role, T-Code, End Date, dan Last Logon, lalu memfilter sesuai modul BPO yang dipilih.</span>
                            </div>

                            {{-- 4 Files Grid --}}
                            <div class="row g-3 mb-3">
                                {{-- 1. LIST_USER_ROLES --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="bi bi-people text-primary me-1"></i> 1. LIST_USER_ROLES (.xlsx) <span class="text-danger">*</span>
                                    </label>
                                    <div class="border rounded-3 p-2 text-center position-relative file-card" style="background:#fafbfc;cursor:pointer;" onclick="document.getElementById('inputUserRoles').click();">
                                        <i class="bi bi-file-earmark-person text-primary fs-3 mb-1 d-block"></i>
                                        <div class="fw-semibold text-dark small text-truncate" id="labelUserRoles">Pilih file USER_ROLES</div>
                                        <div class="text-muted" style="font-size:.7rem;">Mapping User, Role & End Date</div>
                                        <input type="file" name="file_user_roles" id="inputUserRoles" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelUserRoles')">
                                    </div>
                                </div>

                                {{-- 2. LIST_ROLE_TCODES --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="bi bi-shield-lock text-success me-1"></i> 2. LIST_ROLE_TCODES (.xlsx) <span class="text-danger">*</span>
                                    </label>
                                    <div class="border rounded-3 p-2 text-center position-relative file-card" style="background:#fafbfc;cursor:pointer;" onclick="document.getElementById('inputRoleTcodes').click();">
                                        <i class="bi bi-file-earmark-lock text-success fs-3 mb-1 d-block"></i>
                                        <div class="fw-semibold text-dark small text-truncate" id="labelRoleTcodes">Pilih file ROLE_TCODES</div>
                                        <div class="text-muted" style="font-size:.7rem;">Relasi Role ke Transaksi T-Code</div>
                                        <input type="file" name="file_role_tcodes" id="inputRoleTcodes" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelRoleTcodes')">
                                    </div>
                                </div>

                                {{-- 3. LIST_OF_TCODES --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="bi bi-journal-code text-warning me-1"></i> 3. LIST_OF_TCODES (.xlsx) <span class="text-danger">*</span>
                                    </label>
                                    <div class="border rounded-3 p-2 text-center position-relative file-card" style="background:#fafbfc;cursor:pointer;" onclick="document.getElementById('inputTcodes').click();">
                                        <i class="bi bi-file-earmark-code text-warning fs-3 mb-1 d-block"></i>
                                        <div class="fw-semibold text-dark small text-truncate" id="labelTcodes">Pilih file OF_TCODES</div>
                                        <div class="text-muted" style="font-size:.7rem;">Master Kamus Deskripsi T-Code</div>
                                        <input type="file" name="file_tcodes" id="inputTcodes" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelTcodes')">
                                    </div>
                                </div>

                                {{-- 4. LIST_USER_LAST_LOGON --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="bi bi-clock-history text-danger me-1"></i> 4. LIST_USER_LAST_LOGON (.xlsx) <span class="text-danger">*</span>
                                    </label>
                                    <div class="border rounded-3 p-2 text-center position-relative file-card" style="background:#fafbfc;cursor:pointer;" onclick="document.getElementById('inputLogon').click();">
                                        <i class="bi bi-file-earmark-medical text-danger fs-3 mb-1 d-block"></i>
                                        <div class="fw-semibold text-dark small text-truncate" id="labelLogon">Pilih file LAST_LOGON</div>
                                        <div class="text-muted" style="font-size:.7rem;">Tanggal Login & Tipe User Dialog/System</div>
                                        <input type="file" name="file_logon" id="inputLogon" class="d-none" accept=".xlsx, .xls" required onchange="setFileNameBadge(this, 'labelLogon')">
                                    </div>
                                </div>
                            </div>

                            {{-- Target Module & Period --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Target Modul BPO <span class="text-danger">*</span></label>
                                    <select name="module" class="form-select rounded-3 shadow-none fw-semibold">
                                        <option value="FM" selected>FM - Funds Management (Roles: ZFM-*)</option>
                                        <option value="PS">PS - Project System (Roles: ZPS-*)</option>
                                        <option value="HR">HR - Human Capital (Roles: ZHR-*, ZHC-*)</option>
                                        <option value="FI">FI - Financial Accounting (Roles: ZFI-*)</option>
                                        <option value="MM">MM - Materials Management (Roles: ZMM-*)</option>
                                        <option value="CO">CO - Controlling (Roles: ZCO-*)</option>
                                        <option value="SD">SD - Sales & Distribution (Roles: ZSD-*)</option>
                                        <option value="PM">PM - Plant Maintenance (Roles: ZPM-*)</option>
                                        <option value="BASIS">BASIS / Security IT (Roles: ZBC-*, SAP_*)</option>
                                        <option value="ALL">ALL - Semua Modul Sekaligus</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Periode Review</label>
                                    <input type="text" name="period" class="form-control rounded-3 shadow-none" placeholder="e.g. Q2.2026" value="Q{{ ceil(now()->month / 3) }}.{{ now()->year }}">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-3 px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" id="btnSubmitMulti">
                                <i class="bi bi-play-circle-fill"></i> Auto-Merge & Run Review
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAB 2: Single Pre-merged File --}}
                <div class="tab-pane fade" id="single-pane" role="tabpanel">
                    <form method="POST" action="{{ route('uar.import') }}" enctype="multipart/form-data" id="uarSingleImportForm">
                        @csrf
                        <div class="modal-body p-4 pt-3">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Pre-Merged UAR Excel File (.xlsx / .xls) <span class="text-danger">*</span></label>
                                <div class="border border-2 border-dashed rounded-4 p-4 text-center" 
                                     style="background:#f8fafc;cursor:pointer;border-color:#cbd5e1 !important;"
                                     onclick="document.getElementById('singleExcelFileInput').click();">
                                    <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-1 mb-2 d-block"></i>
                                    <div class="fw-semibold text-dark mb-1" id="singleFileLabel">Pilih file atau drag & drop ke sini</div>
                                    <div class="text-muted small">Contoh format: <code>Hasil UAR Modul FM.xlsx</code></div>
                                    <input type="file" name="excel_file" id="singleExcelFileInput" class="d-none" accept=".xlsx, .xls" required
                                           onchange="updateSingleFileName(this)">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-dark">Periode Review</label>
                                <input type="text" name="period" class="form-control rounded-3" 
                                       placeholder="e.g. Q2.2026" value="Q{{ ceil(now()->month / 3) }}.{{ now()->year }}">
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-3 px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" id="btnSubmitSingle">
                                Upload & Run Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.file-card:hover {
    background: #f0f7ff !important;
    border-color: #0d6efd !important;
    transition: all 0.2s ease-in-out;
}
.nav-pills .nav-link.active {
    background-color: #ffffff;
    color: #0d6efd !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
}
</style>

<script>
function setFileNameBadge(input, labelId) {
    if (input.files && input.files[0]) {
        const el = document.getElementById(labelId);
        el.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>' + input.files[0].name + '</span>';
        input.closest('.file-card').style.borderColor = '#198754';
        input.closest('.file-card').style.background = '#f0fff4';
    }
}

function updateSingleFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('singleFileLabel').innerHTML = '<span class="text-primary fw-bold">' + input.files[0].name + '</span> (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
    }
}

document.getElementById('uarMultiImportForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmitMulti');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Menggabungkan 4 File & Mengevaluasi...';
});

document.getElementById('uarSingleImportForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmitSingle');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Processing...';
});
</script>

@endsection

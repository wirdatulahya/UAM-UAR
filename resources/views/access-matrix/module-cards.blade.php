@extends('layouts.app')

@section('title', ($currentApp->name ?? 'UAM SAP') . ' Modules')

@push('styles')
<style>
    /* ─── Directory Table Styles (Selected Enterprise Design) ─── */
    .directory-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .directory-header {
        padding: 1.1rem 1.5rem;
        background: #fff;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .module-code-pill {
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: #0b2e6d;
        background: #eef4ff;
        border: 1px solid #dbeafe;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        display: inline-block;
    }

    .table-directory th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-muted);
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
        white-space: nowrap;
    }

    .table-directory td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        font-size: 0.86rem;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text);
    }

    .table-directory tbody tr {
        transition: background 0.15s ease;
    }

    .table-directory tbody tr:hover {
        background: #f8fafc;
    }

    .btn-open-module {
        display: inline-flex;
        align-items: center;
        background: #0b2e6d;
        color: #fff;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.42rem 0.95rem;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .btn-open-module:hover {
        background: #071f4a;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(11, 46, 109, 0.2);
    }

    .btn-row-delete {
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        background: transparent;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-row-delete:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
    }

    .search-directory-input {
        font-size: 0.84rem;
        padding: 0.48rem 0.9rem;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #fff;
        min-width: 260px;
        transition: all 0.15s ease;
    }

    .search-directory-input:focus {
        outline: none;
        border-color: #0b2e6d;
        box-shadow: 0 0 0 3px rgba(11, 46, 109, 0.1);
    }

    .status-badge-active {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: #10b981;
        background: #ecfdf5;
        padding: 0.15rem 0.55rem;
        border-radius: 20px;
    }

    .status-badge-active::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
    }
</style>
@endpush

@section('content')

{{-- ─── App Shell ─────────────────────────────────────────────────────── --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Sidebar --}}
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
        <x-navbar-right />
    </header>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

        @php
            $typeLabel = $type === 'request' ? 'Request Access Matrix' : ($type === 'approval' ? 'Approval Access Matrix' : 'Accept');
            $typeRoute = $type === 'request' ? route('access-matrix.request.index') : ($type === 'approval' ? route('access-matrix.approval.index') : route('access-matrix.uam-request.index'));
            $totalRequests = $modules->sum('request_count');
        @endphp

        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => $typeLabel, 'url' => $typeRoute],
            ['label' => $currentApp->name ?? 'UAM SAP'],
        ]" />

        {{-- ── Page Hero ── --}}
        <div class="mb-4 animate-in">
            <div style="background:linear-gradient(135deg,#071f4d 0%,#0B2E6D 50%,#1e3a8a 100%);border-radius:18px;padding:1.4rem 2rem;position:relative;overflow:hidden;box-shadow:0 8px 20px -4px rgba(11,46,109,.2);">
                <div style="position:absolute;width:240px;height:240px;background:radial-gradient(circle,rgba(59,130,246,.18) 0%,transparent 70%);border-radius:50%;right:-40px;top:-60px;pointer-events:none;"></div>
                <div style="position:absolute;width:100px;height:100px;background:rgba(255,255,255,.04);border-radius:50%;right:140px;bottom:-30px;pointer-events:none;"></div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index:1;">
                    <h1 style="color:#fff;font-size:1.6rem;font-weight:800;margin:0;line-height:1.2;letter-spacing:-.4px;">
                        {{ $currentApp->name ?? 'UAM SAP' }} Modules
                    </h1>
                    <div>
                        @if($type === 'request' && (Auth::user()->isAdmin() || Auth::user()->isPicAo()))
                        <button type="button" class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddModule"
                                style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;padding:.5rem 1.15rem;font-weight:700;font-size:.82rem;backdrop-filter:blur(8px);"
                                onmouseenter="this.style.background='rgba(255,255,255,.25)'" onmouseleave="this.style.background='rgba(255,255,255,.15)'">
                            + Add New Module
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>


        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="animate-in mb-4" role="alert"
                 style="background:#e8f5e9;border:0;border-left:4px solid #2e7d32;border-radius:8px;color:#1b5e20;font-size:.85rem;padding:.75rem 1rem;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="animate-in mb-4" role="alert"
                 style="background:var(--primary-light);border:0;border-left:4px solid var(--primary);border-radius:8px;color:#7b0d0f;font-size:.85rem;padding:.75rem 1rem;">
                <div style="font-weight:700;margin-bottom:.25rem;">Validation Notice</div>
                <ul style="margin:0;padding-left:1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Directory Table Card ── --}}
        <div class="directory-card animate-in animate-in-delay-1 mb-4">
            
            {{-- Table Toolbar --}}
            <div class="directory-header">
                <div class="d-flex align-items-center gap-3">
                    <span style="font-weight:700;font-size:.9rem;color:#0f172a;">
                        Module List
                    </span>
                    <span style="font-size:.75rem;background:#f1f5f9;color:#475569;padding:.2rem .6rem;border-radius:20px;font-weight:700;">
                        {{ $modules->count() }} Modules
                    </span>
                    @if($totalRequests > 0)
                    <span style="font-size:.75rem;background:#eef4ff;color:#0b2e6d;padding:.2rem .6rem;border-radius:20px;font-weight:700;">
                        {{ $totalRequests }} Total Request(s)
                    </span>
                    @endif
                </div>

                <div>
                    <input type="text" id="moduleSearchInput" class="search-directory-input" placeholder="Search module code or name..." onkeyup="filterModulesTable()">
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-directory mb-0" id="modulesTable">
                    <thead>
                        <tr>
                            <th style="width:10%;">Code</th>
                            <th style="width:28%;">Module Name</th>
                            <th style="width:37%;">Scope / Description</th>
                            <th style="width:12%;">Requests</th>
                            <th style="width:13%;text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $mod)
                            @php
                                if ($type === 'request') {
                                    $moduleUrl = route('access-matrix.request.module.list', ['app' => $currentApp->slug, 'module' => $mod->code]);
                                } elseif ($type === 'accept') {
                                    $moduleUrl = route('access-matrix.uam-request.module.list', ['app' => $currentApp->slug, 'module' => $mod->code]);
                                } else {
                                    $moduleUrl = route('access-matrix.approval.module.list', ['app' => $currentApp->slug, 'module' => $mod->code]);
                                }
                            @endphp
                            <tr class="module-row" data-code="{{ strtolower($mod->code) }}" data-name="{{ strtolower($mod->name) }}">
                                <td>
                                    <span class="module-code-pill">{{ $mod->code }}</span>
                                </td>
                                <td>
                                    <div style="font-weight:700;color:#0f172a;">{{ $mod->name }}</div>
                                    @if(!empty($mod->last_updated))
                                        <div style="font-size:.7rem;color:#94a3b8;margin-top:2px;">Updated {{ $mod->last_updated->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size:.82rem;color:#64748b;line-height:1.4;">
                                        {{ $mod->description ?: ('Access authorizations and matrix management for ' . $mod->name . '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:700;color:{{ ($mod->request_count ?? 0) > 0 ? '#0b2e6d' : '#94a3b8' }};">
                                        {{ $mod->request_count ?? 0 }}
                                    </span>

                                    @if(!empty($mod->pending_count) && $mod->pending_count > 0)
                                        <span style="font-size:.68rem;font-weight:700;background:#fef3c7;color:#92400e;padding:.12rem .4rem;border-radius:4px;margin-left:.3rem;">
                                            {{ $mod->pending_count }} Pending
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="{{ $moduleUrl }}" class="btn-open-module">
                                            Open
                                        </a>

                                        @if($type === 'request' && (Auth::user()->isAdmin() || Auth::user()->isPicAo()) && !in_array($mod->code, ['FM', 'PS', 'FI', 'CO', 'HR', 'MM', 'SD', 'PM']))
                                        <form method="POST" action="{{ route('access-matrix.module.destroy', $mod->id) }}" onsubmit="return confirm('Are you sure you want to delete module &quot;{{ addslashes($mod->name) }}&quot;?');" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-row-delete" title="Delete {{ $mod->code }}">
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Empty search row --}}
            <div id="noResultsRow" class="text-center py-4 d-none">
                <p style="font-size:.85rem;color:var(--text-muted);margin:0;">No modules found matching your search.</p>
            </div>

        </div>

    </main>

</div>

{{-- ── Modal Add New Module ────────────────────────────────────────────────── --}}
@if($type === 'request' && (Auth::user()->isAdmin() || Auth::user()->isPicAo()))
<div class="modal fade" id="modalAddModule" tabindex="-1" aria-labelledby="modalAddModuleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 20px 50px rgba(0,0,0,.15);overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="padding:1.5rem 1.5rem 0.5rem;">
                <div>
                    <h5 class="modal-title fw-bold" id="modalAddModuleLabel" style="color:var(--secondary);font-size:1.15rem;margin:0;">Add New Module</h5>
                    <p class="text-muted small mb-0" style="font-size:.78rem;">Register a business module for {{ $currentApp->name ?? 'UAM SAP' }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('access-matrix.module.store', ['app' => $currentApp->slug ?? 'sap']) }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:1.25rem 1.5rem 1.5rem;">
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:.7rem;letter-spacing:.5px;">Module Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control rounded-3" placeholder="e.g. QM, PP, BW, ABAP" required maxlength="50" style="text-transform:uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:.7rem;letter-spacing:.5px;">Module Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Quality Management" required maxlength="255">
                    </div>
                    <div>
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:.7rem;letter-spacing:.5px;">Description (Optional)</label>
                        <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Brief scope of authorizations..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" style="background:#0b2e6d;border-color:#0b2e6d;">
                        Save Module
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function filterModulesTable() {
        const query = document.getElementById('moduleSearchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.module-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const name = row.getAttribute('data-name') || '';
            if (code.includes(query) || name.includes(query)) {
                row.classList.remove('d-none');
                visibleCount++;
            } else {
                row.classList.add('d-none');
            }
        });

        const noResults = document.getElementById('noResultsRow');
        if (noResults) {
            if (visibleCount === 0 && query.length > 0) {
                noResults.classList.remove('d-none');
            } else {
                noResults.classList.add('d-none');
            }
        }
    }
</script>
@endpush

@endsection






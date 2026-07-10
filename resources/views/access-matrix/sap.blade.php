@extends('layouts.app')

@section('title', 'UAM SAP — Access Matrix')

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">

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

            {{-- Right — Profile Dropdown --}}
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

                {{-- Dropdown Menu --}}
                <div id="profileDropdownMenu"
                    style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:200px;background:#fff;border:1.5px solid var(--border);border-radius:14px;box-shadow:0 8px 32px rgba(11,46,109,.13);z-index:200;overflow:hidden;">

                    <div style="padding:.85rem 1rem .75rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                        <div style="font-size:.8rem;font-weight:700;color:var(--secondary);">{{ Auth::user()->name }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted);">{{ Auth::user()->email }}</div>
                    </div>

                    <a href="{{ route('password.change') }}"
                        style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--text);text-decoration:none;transition:background var(--transition);"
                        onmouseenter="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';"
                        onmouseleave="this.style.background='';this.style.color='var(--text)';">
                        <i class="bi bi-gear-fill" style="font-size:.9rem;color:var(--text-muted);"></i>
                        Change Password
                    </a>

                    <div style="height:1px;background:var(--border);"></div>

                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <button type="submit" id="logoutBtn"
                            style="display:flex;align-items:center;gap:.65rem;width:100%;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:#c0392b;background:none;border:none;cursor:pointer;transition:background var(--transition);"
                            onmouseenter="this.style.background='#fde8e9';"
                            onmouseleave="this.style.background='';">
                            <i class="bi bi-box-arrow-right" style="font-size:.9rem;"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>

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
        <a href="{{ route('access-matrix.index') }}" class="sidebar-nav-item active">
            <i class="bi bi-table"></i>
            Access Matrix
        </a>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-clipboard2-check-fill"></i>
            Access Review
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-graph-up-arrow"></i>
            Monitoring
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>
        <a href="#" class="sidebar-nav-item" aria-disabled="true">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
            Reports
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span>
        </a>

    </aside>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

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
                    <i class="bi bi-exclamation-triangle-fill"></i> Import Error
                </div>
                <ul style="margin:0;padding-left:1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Breadcrumbs ── --}}
        <nav aria-label="breadcrumb" class="animate-in" style="margin-bottom:.4rem;">
            <ol class="breadcrumb" style="background:none;padding:0;margin:0;font-size:.78rem;font-weight:500;display:flex;gap:.35rem;list-style:none;">
                <li class="breadcrumb-item d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;transition:color var(--transition);"
                       onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">Dashboard</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item d-flex align-items-center" style="margin-left:.35rem;">
                    <a href="{{ route('access-matrix.index') }}" style="color:var(--text-muted);text-decoration:none;transition:color var(--transition);"
                       onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">Access Matrix</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item active" style="color:var(--secondary);font-weight:600;margin-left:.35rem;" aria-current="page">UAM SAP</li>
            </ol>
        </nav>

        {{-- ── Page Header ── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 animate-in" style="gap:1rem;">
            <div>
                <h1 style="font-size:1.45rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                    <i class="bi bi-pc-display-horizontal me-2" style="color:var(--primary);"></i>UAM SAP Module
                </h1>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                    Search by Role to view, add, edit, or delete access records
                    @if($totalRecords > 0)
                        &nbsp;·&nbsp; <strong>{{ number_format($totalRecords) }}</strong> records in database
                    @endif
                </p>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                {{-- Import Excel Button --}}
                <button type="button" id="toggleUploadBtn"
                    style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:10px;padding:.55rem 1.25rem;font-size:.82rem;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all var(--transition);"
                    onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';"
                    onmouseleave="if(!uploadCardCollapse.dataset.open){this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';}">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    Import Excel
                </button>

                {{-- Add New Record --}}
                <a href="{{ route('access-matrix.create') }}"
                    class="btn-primary-custom"
                    style="width:auto;padding:.55rem 1.25rem;font-size:.82rem;display:inline-flex;align-items:center;gap:.45rem;border-radius:10px;text-decoration:none;">
                    <i class="bi bi-plus-lg"></i>
                    Add Record
                </a>

                @if ($totalRecords > 0)
                    <form method="POST" action="{{ route('access-matrix.clear') }}" id="clearForm"
                          onsubmit="return confirm('Delete ALL {{ $totalRecords }} records? This cannot be undone.');"
                          style="margin:0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" id="clearBtn"
                            style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:10px;padding:.55rem 1.25rem;font-size:.82rem;font-weight:600;color:#c0392b;cursor:pointer;transition:all var(--transition);"
                            onmouseenter="this.style.borderColor='#c0392b';this.style.background='#fde8e9';"
                            onmouseleave="this.style.borderColor='var(--border)';this.style.background='none';">
                            <i class="bi bi-trash3-fill"></i>
                            Clear All
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ── Collapsible Import Card ── --}}
        <div id="uploadCardCollapse" class="animate-in animate-in-delay-1 mb-4" style="display:none;">
            <div id="uploadCard" style="background:#fff;border:2px dashed var(--border);border-radius:16px;padding:2rem;transition:border-color var(--transition),background var(--transition);">

                <form method="POST" action="{{ route('access-matrix.import') }}"
                      enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div id="dropZone" style="text-align:center;cursor:pointer;" onclick="document.getElementById('fileInput').click();">
                        <div style="width:56px;height:56px;background:var(--secondary-light);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                            <i class="bi bi-file-earmark-arrow-up-fill" style="font-size:1.6rem;color:var(--secondary);"></i>
                        </div>
                        <h3 style="font-size:1rem;font-weight:700;color:var(--secondary);margin-bottom:.3rem;">
                            Drag &amp; Drop your UAM Excel file here
                        </h3>
                        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1.25rem;">
                            Supports <strong>.xlsx</strong>, <strong>.xls</strong>, and <strong>.csv</strong> &nbsp;·&nbsp; Max 10 MB
                        </p>
                        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1.25rem;">
                            Expected columns: <code>Role</code>, <code>Description Role</code>, <code>TCODE</code>, <code>UNIT</code>, <code>BPO</code>, <code>Access Owner</code>
                        </p>

                        <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" style="display:none;">

                        <div id="fileLabel"
                            style="display:inline-flex;align-items:center;gap:.5rem;background:var(--secondary);color:#fff;border:none;border-radius:8px;padding:.55rem 1.25rem;font-size:.85rem;font-weight:600;cursor:pointer;transition:filter var(--transition);"
                            onmouseenter="this.style.filter='brightness(1.1)'"
                            onmouseleave="this.style.filter=''">
                            <i class="bi bi-folder2-open"></i>
                            Browse File
                        </div>
                    </div>

                    {{-- File preview --}}
                    <div id="filePreview" style="display:none;margin-top:1.25rem;padding:1rem;background:var(--secondary-light);border-radius:10px;align-items:center;gap:.75rem;">
                        <div style="width:40px;height:40px;background:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                            <i class="bi bi-file-earmark-spreadsheet-fill" style="font-size:1.2rem;color:var(--secondary);"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div id="fileName" style="font-size:.85rem;font-weight:600;color:var(--secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                            <div id="fileSize" style="font-size:.72rem;color:var(--text-muted);"></div>
                        </div>
                        <button type="button" id="removeFile"
                            style="background:none;border:none;padding:.2rem .4rem;color:var(--text-muted);cursor:pointer;border-radius:6px;font-size:1rem;flex-shrink:0;"
                            title="Remove file">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    {{-- Submit --}}
                    <div id="submitWrapper" style="display:none;margin-top:1rem;text-align:right;">
                        <button type="submit" id="submitBtn" class="btn-primary-custom" style="width:auto;padding:.6rem 1.75rem;">
                            <i class="bi bi-upload me-1"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Search Bar ── --}}
        <div class="animate-in animate-in-delay-1 mb-4"
             style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.02);">
            <form method="GET" action="{{ route('access-matrix.sap') }}" id="searchForm">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-8 col-lg-9">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.9rem;"></i>
                            <input type="text" name="search" id="searchInput" value="{{ $search }}"
                                   class="form-control"
                                   style="padding-left:2.6rem;font-size:.9rem;"
                                   placeholder="Search by Role (e.g. ZPS-MD-1014-000000-PROJ-CHG)…"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                        <button type="submit" class="btn-primary-custom flex-grow-1"
                                style="padding:.65rem 1rem;font-size:.9rem;background:var(--secondary);border-radius:10px;box-shadow:none;">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        @if($search)
                            <a href="{{ route('access-matrix.sap') }}"
                               style="display:inline-flex;align-items:center;gap:.3rem;padding:.65rem .9rem;border-radius:10px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;color:var(--text-muted);text-decoration:none;white-space:nowrap;transition:all var(--transition);"
                               onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';"
                               onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';">
                                <i class="bi bi-x-lg"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Results Table ── --}}
        <div class="animate-in animate-in-delay-2 mb-4">
            <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">

                {{-- Table Header Bar --}}
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        <div style="width:36px;height:36px;background:var(--secondary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-shield-lock-fill" style="color:var(--secondary);font-size:.95rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.9rem;font-weight:700;color:var(--secondary);">UAM Records</div>
                            <div style="font-size:.72rem;color:var(--text-muted);">
                                @if ($search)
                                    @if ($records->total() > 0)
                                        Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }} records for
                                        <strong style="color:var(--secondary);">"{{ $search }}"</strong>
                                    @else
                                        No records found for <strong>"{{ $search }}"</strong>
                                    @endif
                                @else
                                    Enter a Role above to search
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($records->total() > 0)
                        <span style="background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.25rem .75rem;font-size:.75rem;font-weight:700;">
                            {{ $records->total() }} Result(s)
                        </span>
                    @endif
                </div>

                {{-- Table --}}
                <div style="overflow-x:auto;">
                    <table class="uam-table" style="width:100%;border-collapse:collapse;font-size:.82rem;">
                        <thead>
                            <tr style="background:var(--secondary-light);">
                                @php
                                    $thStyle = "padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);";
                                @endphp
                                <th style="{{ $thStyle }}">#</th>
                                <th style="{{ $thStyle }}">Role</th>
                                <th style="{{ $thStyle }}">Description Role</th>
                                <th style="{{ $thStyle }}">TCODE</th>
                                <th style="{{ $thStyle }}">Access</th>
                                <th style="{{ $thStyle }}">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $i => $rec)
                                <tr style="border-bottom:1px solid var(--border);transition:background var(--transition);"
                                    onmouseenter="this.style.background='var(--secondary-light)'"
                                    onmouseleave="this.style.background=''">
                                    <td style="padding:.7rem 1rem;color:var(--text-muted);font-size:.78rem;white-space:nowrap;">
                                        {{ $records->firstItem() + $i }}
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;max-width:260px;">
                                        <span style="font-family:monospace;background:#f1f5f9;padding:.2rem .45rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);font-weight:700;color:var(--secondary);">
                                            {{ $rec->role ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="padding:.7rem 1rem;color:var(--text);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                        title="{{ $rec->description_role }}">
                                        {{ $rec->description_role ?? '—' }}
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;">
                                        @if($rec->tcode)
                                            <span style="display:inline-flex;align-items:center;background:#eff6ff;color:#1d4ed8;border-radius:6px;padding:.2rem .5rem;font-size:.75rem;font-weight:600;border:1px solid #bfdbfe;font-family:monospace;">
                                                {{ $rec->tcode }}
                                            </span>
                                        @else
                                            <span style="color:var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;">
                                        <button type="button" onclick="openAccessModal(this)"
                                            data-role="{{ htmlspecialchars($rec->role ?? '—') }}"
                                            data-tcode="{{ htmlspecialchars($rec->tcode ?? '') }}"
                                            style="display:inline-flex;align-items:center;gap:.4rem;background:var(--secondary-light);color:var(--secondary);border:1px solid var(--border);border-radius:6px;padding:.3rem .75rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);"
                                            onmouseenter="this.style.background='var(--secondary)';this.style.color='#fff';"
                                            onmouseleave="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';">
                                            <i class="bi bi-shield-lock"></i> View Access
                                        </button>
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;">
                                        <div class="d-flex align-items-center gap-1">
                                            {{-- Edit --}}
                                            <a href="{{ route('access-matrix.edit', $rec->id) }}"
                                               style="display:inline-flex;align-items:center;gap:.3rem;background:#fef3c7;color:#d97706;border:none;border-radius:6px;padding:.3rem .6rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);text-decoration:none;"
                                               onmouseenter="this.style.filter='brightness(0.95)'"
                                               onmouseleave="this.style.filter=''">
                                                <i class="bi bi-pencil-fill"></i> Edit
                                            </a>
                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('access-matrix.destroy', $rec->id) }}"
                                                  onsubmit="return confirm('Delete this record?\nRole: {{ addslashes($rec->role) }}\nTCODE: {{ addslashes($rec->tcode ?? '—') }}')"
                                                  style="margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    style="display:inline-flex;align-items:center;gap:.3rem;background:var(--primary-light);color:var(--primary);border:none;border-radius:6px;padding:.3rem .6rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);"
                                                    onmouseenter="this.style.filter='brightness(0.95)'"
                                                    onmouseleave="this.style.filter=''">
                                                    <i class="bi bi-trash-fill"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="padding:3.5rem 1rem;text-align:center;">
                                        @if($search)
                                            {{-- Searched but no results --}}
                                            <div style="width:56px;height:56px;background:var(--primary-light);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                                <i class="bi bi-search" style="font-size:1.4rem;color:var(--primary);"></i>
                                            </div>
                                            <h3 style="font-size:.95rem;font-weight:700;color:var(--secondary);margin-bottom:.2rem;">No matching records found</h3>
                                            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">No UAM records match "<strong>{{ $search }}</strong>".</p>
                                            <a href="{{ route('access-matrix.create') }}"
                                               class="btn-primary-custom"
                                               style="width:auto;padding:.5rem 1.25rem;font-size:.82rem;display:inline-flex;align-items:center;gap:.4rem;border-radius:8px;text-decoration:none;">
                                                <i class="bi bi-plus-lg"></i> Add New Record
                                            </a>
                                        @else
                                            {{-- Initial state: no search yet --}}
                                            <div style="width:64px;height:64px;background:var(--secondary-light);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                                <i class="bi bi-search" style="font-size:1.6rem;color:var(--secondary);"></i>
                                            </div>
                                            <h3 style="font-size:1rem;font-weight:700;color:var(--secondary);margin-bottom:.3rem;">Search to view records</h3>
                                            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem;">
                                                Enter a <strong>Role</strong> in the search box above to find matching UAM records.
                                                @if($totalRecords > 0)
                                                    <br>There are <strong>{{ number_format($totalRecords) }}</strong> records available.
                                                @else
                                                    <br>No data imported yet. Use <strong>Import Excel</strong> or <strong>Add Record</strong> to get started.
                                                @endif
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($records->hasPages())
                    <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                        <div style="font-size:.78rem;color:var(--text-muted);">
                            Page {{ $records->currentPage() }} of {{ $records->lastPage() }}
                        </div>
                        <div style="display:flex;gap:.35rem;">
                            @if ($records->onFirstPage())
                                <span style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--text-muted);opacity:.5;">← Prev</span>
                            @else
                                <a href="{{ $records->previousPageUrl() }}" style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--secondary);text-decoration:none;transition:all var(--transition);"
                                   onmouseenter="this.style.background='var(--secondary-light)'"
                                   onmouseleave="this.style.background=''">← Prev</a>
                            @endif

                            @foreach ($records->getUrlRange(max(1, $records->currentPage() - 2), min($records->lastPage(), $records->currentPage() + 2)) as $page => $url)
                                @if ($page == $records->currentPage())
                                    <span style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--secondary);font-size:.78rem;background:var(--secondary);color:#fff;font-weight:700;">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--secondary);text-decoration:none;transition:all var(--transition);"
                                       onmouseenter="this.style.background='var(--secondary-light)'"
                                       onmouseleave="this.style.background=''">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($records->hasMorePages())
                                <a href="{{ $records->nextPageUrl() }}" style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--secondary);text-decoration:none;transition:all var(--transition);"
                                   onmouseenter="this.style.background='var(--secondary-light)'"
                                   onmouseleave="this.style.background=''">Next →</a>
                            @else
                                <span style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--text-muted);opacity:.5;">Next →</span>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </main>

    {{-- ── Access Modal ─────────────────────────────────────────────── --}}
    <div id="accessModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);padding:1rem;">
        <div class="animate-in" id="accessModalDialog"
             style="background:#fff;border-radius:20px;width:min(90vw, 1040px);max-height:82vh;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,.22);overflow:hidden;">

            {{-- Modal header --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.75rem;border-bottom:1px solid var(--border);flex-shrink:0;background:var(--secondary-light);">
                <div style="display:flex;align-items:center;gap:.65rem;">
                    <div style="width:36px;height:36px;background:var(--secondary);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-shield-check" style="color:#fff;font-size:1rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:1rem;font-weight:800;color:var(--secondary);line-height:1.1;">Access Permissions</div>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.1rem;">
                            Role: <span id="modalRole" style="font-family:monospace;font-weight:700;color:var(--secondary);"></span>
                            &nbsp;·&nbsp;
                            TCODE: <span id="modalTcodeHeader" style="font-family:monospace;font-weight:700;color:#1d4ed8;"></span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeAccessModal()"
                        style="background:none;border:1.5px solid var(--border);border-radius:8px;width:34px;height:34px;cursor:pointer;font-size:1rem;color:var(--text-muted);display:flex;align-items:center;justify-content:center;transition:all var(--transition);"
                        onmouseenter="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';"
                        onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted);'">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Scrollable body --}}
            <div style="flex:1;overflow-y:auto;padding:1.5rem 1.75rem;">

                {{-- Loading state --}}
                <div id="modalLoading" style="display:none;text-align:center;padding:3rem 1rem;">
                    <div class="spinner-border" role="status" style="width:1.75rem;height:1.75rem;border-width:.22em;color:var(--secondary);"></div>
                    <div style="font-size:.85rem;color:var(--text-muted);margin-top:.75rem;font-weight:500;">Fetching access data…</div>
                </div>

                {{-- Content --}}
                <div id="modalContentWrapper" style="display:none;">

                    {{-- Meta row: Unit + BPO --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                        <div style="background:var(--secondary-light);padding:.85rem 1rem;border-radius:12px;border:1px solid var(--border);">
                            <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.3rem;">Unit</div>
                            <div id="modalUnit" style="font-size:.88rem;font-weight:600;color:var(--secondary);line-height:1.4;"></div>
                        </div>
                        <div style="background:var(--secondary-light);padding:.85rem 1rem;border-radius:12px;border:1px solid var(--border);">
                            <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.3rem;">BPO</div>
                            <div id="modalBpo" style="font-size:.88rem;font-weight:600;color:var(--secondary);line-height:1.4;"></div>
                        </div>
                    </div>

                    {{-- Access Owners panel (scrollable, grid) --}}
                    <div style="border:1.5px solid #bbf7d0;border-radius:14px;overflow:hidden;margin-bottom:1.25rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1rem;background:#f0fdf4;border-bottom:1px solid #bbf7d0;">
                            <div style="display:flex;align-items:center;gap:.45rem;">
                                <i class="bi bi-people-fill" style="color:#166534;font-size:.9rem;"></i>
                                <span style="font-size:.72rem;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:.5px;">Access Owners</span>
                            </div>
                            <span id="modalOwnerCount" style="font-size:.7rem;font-weight:700;background:#166534;color:#fff;border-radius:20px;padding:.1rem .55rem;"></span>
                        </div>
                        <div id="modalOwnerScroll" style="max-height:220px;overflow-y:auto;padding:1rem;">
                            <div id="modalOwner" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.55rem;"></div>
                        </div>
                    </div>

                    {{-- TCODE badge for this specific row --}}
                    <div style="margin-bottom:1.25rem;">
                        <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.45rem;">Transaction Code</div>
                        <span id="modalTcodeBadge" style="display:inline-flex;align-items:center;background:#eff6ff;color:#1d4ed8;border-radius:8px;padding:.3rem .75rem;font-size:.82rem;font-weight:700;border:1px solid #bfdbfe;font-family:monospace;letter-spacing:.3px;"></span>
                    </div>

                </div>
            </div>

            {{-- Modal footer --}}
            <div style="padding:.9rem 1.75rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:flex-end;flex-shrink:0;background:#fafbfc;">
                <button type="button" onclick="closeAccessModal()"
                        style="padding:.5rem 1.5rem;background:var(--secondary);color:#fff;border:none;border-radius:9px;font-size:.85rem;font-weight:700;cursor:pointer;transition:filter var(--transition);letter-spacing:.1px;"
                        onmouseenter="this.style.filter='brightness(1.1)'" onmouseleave="this.style.filter=''">Close</button>
            </div>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    #uploadCard.dragover {
        border-color: var(--secondary);
        background: var(--secondary-light);
    }
</style>
@endpush

@push('scripts')
<script>
    // ── Profile Dropdown ───────────────────────────────────────────────
    const profileBtn  = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    const chevron     = document.getElementById('profileChevron');

    profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = profileMenu.style.display === 'block';
        profileMenu.style.display = isOpen ? 'none' : 'block';
        chevron.style.transform   = isOpen ? '' : 'rotate(180deg)';
        profileBtn.style.borderColor = isOpen ? 'var(--border)' : 'var(--secondary)';
    });

    document.addEventListener('click', function () {
        profileMenu.style.display = 'none';
        chevron.style.transform   = '';
        profileBtn.style.borderColor = 'var(--border)';
    });

    document.getElementById('logoutForm').addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out…';
    });

    // ── Import Panel Toggle ────────────────────────────────────────────
    const toggleUploadBtn    = document.getElementById('toggleUploadBtn');
    const uploadCardCollapse = document.getElementById('uploadCardCollapse');

    function openUploadPanel() {
        uploadCardCollapse.style.display = 'block';
        uploadCardCollapse.dataset.open  = '1';
        toggleUploadBtn.style.background    = 'var(--secondary-light)';
        toggleUploadBtn.style.borderColor   = 'var(--secondary)';
        toggleUploadBtn.style.color         = 'var(--secondary)';
    }

    function closeUploadPanel() {
        uploadCardCollapse.style.display = 'none';
        delete uploadCardCollapse.dataset.open;
        toggleUploadBtn.style.background  = 'none';
        toggleUploadBtn.style.borderColor = 'var(--border)';
        toggleUploadBtn.style.color       = 'var(--text-muted)';
    }

    @if ($errors->any())
        openUploadPanel();
    @endif

    toggleUploadBtn.addEventListener('click', function () {
        if (uploadCardCollapse.dataset.open) {
            closeUploadPanel();
        } else {
            openUploadPanel();
        }
    });

    // ── File Input & Drag-Drop ─────────────────────────────────────────
    const fileInput     = document.getElementById('fileInput');
    const filePreview   = document.getElementById('filePreview');
    const fileNameEl    = document.getElementById('fileName');
    const fileSizeEl    = document.getElementById('fileSize');
    const submitWrapper = document.getElementById('submitWrapper');
    const removeBtn     = document.getElementById('removeFile');
    const uploadCard    = document.getElementById('uploadCard');
    const dropZone      = document.getElementById('dropZone');

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function showFile(file) {
        fileNameEl.textContent      = file.name;
        fileSizeEl.textContent      = formatSize(file.size);
        filePreview.style.display   = 'flex';
        submitWrapper.style.display = 'block';
        dropZone.style.opacity      = '0.5';
    }

    function clearFile() {
        fileInput.value             = '';
        filePreview.style.display   = 'none';
        submitWrapper.style.display = 'none';
        dropZone.style.opacity      = '1';
    }

    fileInput.addEventListener('change', function () {
        if (this.files[0]) showFile(this.files[0]);
    });

    removeBtn.addEventListener('click', clearFile);

    ['dragenter', 'dragover'].forEach(evt => {
        uploadCard.addEventListener(evt, e => {
            e.preventDefault();
            uploadCard.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        uploadCard.addEventListener(evt, e => {
            e.preventDefault();
            uploadCard.classList.remove('dragover');
        });
    });

    uploadCard.addEventListener('drop', function (e) {
        const file    = e.dataTransfer.files[0];
        if (!file) return;
        const allowed = ['xlsx', 'xls', 'csv'];
        const ext     = file.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            alert('Only .xlsx, .xls, and .csv files are accepted.');
            return;
        }
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFile(file);
        openUploadPanel();
    });

    document.getElementById('importForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importing…';
    });

    // Auto-focus search input
    const si = document.getElementById('searchInput');
    if (si && !si.value) si.focus();

    // ── Access Modal ───────────────────────────────────────────────
    const accessModal = document.getElementById('accessModal');

    async function openAccessModal(btn) {
        const role  = btn.dataset.role;
        const tcode = btn.dataset.tcode || '';

        document.getElementById('modalRole').textContent        = role;
        document.getElementById('modalTcodeHeader').textContent = tcode || '—';
        document.getElementById('modalTcodeBadge').textContent  = tcode || '—';

        accessModal.style.display = 'flex';
        document.getElementById('modalLoading').style.display        = 'block';
        document.getElementById('modalContentWrapper').style.display = 'none';

        try {
            const url = `/access-matrix/sap/role-details?role=${encodeURIComponent(role)}&tcode=${encodeURIComponent(tcode)}`;
            const res  = await fetch(url);
            const data = await res.json();

            if (res.ok) {
                // ── Meta ────────────────────────────────────────────────────
                document.getElementById('modalUnit').textContent = data.unit || '—';
                document.getElementById('modalBpo').textContent  = data.bpo  || '—';

                // ── Access Owners grid ──────────────────────────────────────
                // API returns an array of individual owner names (already split).
                const owners = Array.isArray(data.access_owners) ? data.access_owners : [];

                const ownerEl    = document.getElementById('modalOwner');
                const ownerCount = document.getElementById('modalOwnerCount');
                ownerCount.textContent = owners.length;

                if (owners.length > 0) {
                    ownerEl.innerHTML = owners.map(o =>
                        `<div style="display:flex;align-items:center;gap:.4rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:.45rem .75rem;min-width:0;">
                            <i class="bi bi-person-check-fill" style="color:#166534;font-size:.8rem;flex-shrink:0;"></i>
                            <span style="font-size:.78rem;font-weight:600;color:#166534;line-height:1.3;word-break:break-word;">${o}</span>
                         </div>`
                    ).join('');
                } else {
                    ownerEl.innerHTML = '<span style="color:var(--text-muted);font-size:.82rem;">No access owners recorded</span>';
                }

                document.getElementById('modalLoading').style.display        = 'none';
                document.getElementById('modalContentWrapper').style.display = 'block';

                // Scroll owners panel to top each open
                document.getElementById('modalOwnerScroll').scrollTop = 0;

            } else {
                alert('Error fetching details: ' + (data.error || 'Unknown error'));
                closeAccessModal();
            }
        } catch (e) {
            alert('Failed to connect to server.');
            closeAccessModal();
        }
    }

    function closeAccessModal() {
        accessModal.style.display = 'none';
    }

    // Close modal on click outside
    window.addEventListener('click', function(e) {
        if (e.target === accessModal) {
            closeAccessModal();
        }
    });
</script>
@endpush
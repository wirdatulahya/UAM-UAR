@extends('layouts.app')

@section('title', 'Access Matrix')

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
        <nav aria-label="breadcrumb" class="animate-in" style="margin-bottom: .4rem;">
            <ol class="breadcrumb" style="background:none; padding:0; margin:0; font-size:.78rem; font-weight:500; display:flex; gap:.35rem; list-style:none;">
                <li class="breadcrumb-item d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" style="color:var(--text-muted); text-decoration:none; transition:color var(--transition);" onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">Dashboard</a>
                    <span style="color:var(--text-muted); margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item active" style="color:var(--secondary); font-weight:600; margin-left:.35rem;" aria-current="page">Access Matrix</li>
            </ol>
        </nav>

        {{-- ── Page Header ─────────────────────────────────────────── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 animate-in" style="gap:1rem;">
            <div>
                <h1 style="font-size:1.45rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                    <i class="bi bi-table me-2" style="color:var(--primary);"></i>Access Matrix
                </h1>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                    Import and manage roles and user access permissions
                </p>
            </div>
            
            <div class="d-flex align-items-center flex-wrap gap-2">
                {{-- Toggle Upload Panel Button --}}
                <button type="button" id="toggleUploadBtn"
                    style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:10px;padding:.55rem 1.25rem;font-size:.82rem;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all var(--transition);">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    Import Excel
                </button>

                {{-- Add Role Button (UI Only) --}}
                <button type="button" class="btn-primary-custom" 
                    style="width:auto;padding:.55rem 1.25rem;font-size:.82rem;display:inline-flex;align-items:center;gap:.45rem;border-radius:10px;"
                    onclick="alert('Add Role feature is under development (UI Only for now).')">
                    <i class="bi bi-plus-lg"></i>
                    Add Role
                </button>

                @if ($totalRecords > 0)
                    <form method="POST" action="{{ route('access-matrix.clear') }}" id="clearForm"
                          onsubmit="return confirm('Are you sure you want to delete all {{ $totalRecords }} records? This cannot be undone.');"
                          style="margin:0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" id="clearBtn"
                            style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:10px;padding:.55rem 1.25rem;font-size:.82rem;font-weight:600;color:#c0392b;cursor:pointer;transition:all var(--transition);"
                            onmouseenter="this.style.borderColor='#c0392b';this.style.background='#fde8e9';"
                            onmouseleave="this.style.borderColor='var(--border)';this.style.background='none';">
                            <i class="bi bi-trash3-fill"></i>
                            Clear Data
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ── Collapsible Upload Card ────────────────────────────────── --}}
        <div id="uploadCardCollapse" class="animate-in animate-in-delay-1 mb-4" style="display:none; transition: all var(--transition);">
            <div id="uploadCard" style="background:#fff;border:2px dashed var(--border);border-radius:16px;padding:2rem;transition:border-color var(--transition),background var(--transition);">

                <form method="POST" action="{{ route('access-matrix.import') }}"
                      enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div id="dropZone" style="text-align:center;cursor:pointer;" onclick="document.getElementById('fileInput').click();">
                        <div style="width:56px;height:56px;background:var(--secondary-light);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                            <i class="bi bi-file-earmark-arrow-up-fill" style="font-size:1.6rem;color:var(--secondary);"></i>
                        </div>
                        <h3 style="font-size:1rem;font-weight:700;color:var(--secondary);margin-bottom:.3rem;">
                            Drag & Drop your file here
                        </h3>
                        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1.25rem;">
                            Supports <strong>.xlsx</strong>, <strong>.xls</strong>, and <strong>.csv</strong> &nbsp;·&nbsp; Max 10 MB
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

                    {{-- Selected file preview --}}
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

        {{-- ── Search & Filters Bar ───────────────────────────────────── --}}
        <div class="animate-in animate-in-delay-1 mb-4" style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.02);">
            <form method="GET" action="{{ route('access-matrix.index') }}" id="filterForm">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-7">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.9rem;"></i>
                            <input type="text" name="search" value="{{ $search }}" class="form-control" style="padding-left:2.6rem;" 
                                   placeholder="{{ $activeTab === 'roles' ? 'Search by Role Code or Description...' : 'Search NIP, Name, Position, Department, etc...' }}">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                        <select name="module" class="form-select form-control" style="cursor:pointer;" onchange="this.form.submit()">
                            <option value="">All Modules</option>
                            @foreach ($availableModules as $mod)
                                <option value="{{ $mod }}" {{ $module === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <button type="submit" class="btn-primary-custom" style="padding:.65rem 1rem;font-size:.9rem;background:var(--secondary);border-radius:10px;box-shadow:none;">
                            <i class="bi bi-filter me-1"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Tabs Navigation ────────────────────────────────────────── --}}
        <div class="d-flex align-items-center mb-4 animate-in animate-in-delay-2" style="border-bottom:1.5px solid var(--border);gap:1.5rem;padding-bottom:.1rem;">
            <a href="{{ route('access-matrix.index', array_merge(request()->query(), ['tab' => 'roles', 'page' => 1])) }}" 
               class="tab-link" 
               style="padding:.6rem .2rem;font-size:.88rem;font-weight:600;text-decoration:none;border-bottom:3px solid {{ $activeTab === 'roles' ? 'var(--secondary)' : 'transparent' }};color:{{ $activeTab === 'roles' ? 'var(--secondary)' : 'var(--text-muted)' }};transition:all var(--transition);">
                <i class="bi bi-shield-lock-fill me-1"></i> Roles Matrix ({{ $roles->total() }})
            </a>
            @if ($totalRecords > 0)
            <a href="{{ route('access-matrix.index', array_merge(request()->query(), ['tab' => 'raw', 'page' => 1])) }}" 
               class="tab-link" 
               style="padding:.6rem .2rem;font-size:.88rem;font-weight:600;text-decoration:none;border-bottom:3px solid {{ $activeTab === 'raw' ? 'var(--secondary)' : 'transparent' }};color:{{ $activeTab === 'raw' ? 'var(--secondary)' : 'var(--text-muted)' }};transition:all var(--transition);">
                <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Raw Uploaded Data ({{ $totalRecords }})
            </a>
            @endif
        </div>

        {{-- ── Data Display ───────────────────────────────────────────── --}}
        @if ($activeTab === 'roles')
            {{-- ──────────────────────────────────────
               ROLES MATRIX TAB
            ────────────────────────────────────── --}}
            <div class="animate-in animate-in-delay-3 mb-4">
                <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">
                    
                    {{-- Table Header Info --}}
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                        <div style="display:flex;align-items:center;gap:.65rem;">
                            <div style="width:36px;height:36px;background:var(--secondary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-shield-lock-fill" style="color:var(--secondary);font-size:.95rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:.9rem;font-weight:700;color:var(--secondary);">Access Matrix Roles</div>
                                <div style="font-size:.72rem;color:var(--text-muted);">
                                    @if($roles->total() > 0)
                                        Showing {{ $roles->firstItem() }}–{{ $roles->lastItem() }} of {{ $roles->total() }} unique roles
                                    @else
                                        No roles found
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span style="background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.25rem .75rem;font-size:.75rem;font-weight:700;">
                            {{ $roles->total() }} Roles Total
                        </span>
                    </div>

                    @if($roles->total() > 0)
                        {{-- Scrollable Table --}}
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
                                <thead>
                                    <tr style="background:var(--secondary-light);">
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">No</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Role Code</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Description</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Stream Process</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Module</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $i => $role)
                                        @php
                                            $roleObj = (object)$role;
                                            $streamVal = isset($roleObj->stream_process) ? $roleObj->stream_process : 'Operation';
                                        @endphp
                                        <tr style="border-bottom:1px solid var(--border);transition:background var(--transition);"
                                            onmouseenter="this.style.background='var(--secondary-light)'"
                                            onmouseleave="this.style.background=''">
                                            <td style="padding:.75rem 1rem;color:var(--text-muted);font-size:.78rem;white-space:nowrap;">{{ $roles->firstItem() + $i }}</td>
                                            <td style="padding:.75rem 1rem;font-weight:700;color:var(--secondary);white-space:nowrap;">
                                                <span style="font-family:monospace;background:#f1f5f9;padding:.2rem .4rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);">
                                                    {{ $roleObj->role_code }}
                                                </span>
                                            </td>
                                            <td style="padding:.75rem 1rem;font-weight:500;">{{ $roleObj->description ?? '—' }}</td>
                                            <td style="padding:.75rem 1rem;white-space:nowrap;">
                                                <span style="display:inline-flex;align-items:center;gap:.3rem;background:#fff8e1;color:#b78103;border-radius:6px;padding:.2rem .55rem;font-size:.75rem;font-weight:600;border:1px solid #ffe082;">
                                                    <i class="bi bi-gear-wide-connected" style="font-size:.7rem;"></i>{{ $streamVal }}
                                                </span>
                                            </td>
                                            <td style="padding:.75rem 1rem;white-space:nowrap;">
                                                @if ($roleObj->module)
                                                    <span style="display:inline-flex;align-items:center;gap:.3rem;background:var(--secondary-light);color:var(--secondary);border-radius:6px;padding:.2rem .55rem;font-size:.75rem;font-weight:600;border:1px solid rgba(11,46,109,.15);">
                                                        <i class="bi bi-app-indicator" style="font-size:.7rem;"></i>{{ $roleObj->module }}
                                                    </span>
                                                @else
                                                    <span style="color:var(--text-muted);">—</span>
                                                @endif
                                            </td>
                                            <td style="padding:.75rem 1rem;white-space:nowrap;">
                                                <div class="d-flex align-items-center gap-1">
                                                    {{-- Detail UI Only --}}
                                                    <button type="button"
                                                        style="background:var(--secondary-light);color:var(--secondary);border:none;border-radius:6px;padding:.3rem .55rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);"
                                                        onmouseenter="this.style.filter='brightness(0.9)'"
                                                        onmouseleave="this.style.filter=''"
                                                        onclick="alert('Role detail view is under development.')">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </button>
                                                    {{-- Edit UI Only --}}
                                                    <button type="button"
                                                        style="background:#fef3c7;color:#d97706;border:none;border-radius:6px;padding:.3rem .55rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);"
                                                        onmouseenter="this.style.filter='brightness(0.95)'"
                                                        onmouseleave="this.style.filter=''"
                                                        onclick="alert('Edit Role feature is under development.')">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    {{-- Delete UI Only --}}
                                                    <button type="button"
                                                        style="background:var(--primary-light);color:var(--primary);border:none;border-radius:6px;padding:.3rem .55rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);"
                                                        onmouseenter="this.style.filter='brightness(0.95)'"
                                                        onmouseleave="this.style.filter=''"
                                                        onclick="alert('Delete Role feature is under development.')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if ($roles->hasPages())
                            <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                                <div style="font-size:.78rem;color:var(--text-muted);">
                                    Page {{ $roles->currentPage() }} of {{ $roles->lastPage() }}
                                </div>
                                <div style="display:flex;gap:.35rem;">
                                    @if ($roles->onFirstPage())
                                        <span style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--text-muted);opacity:.5;">← Prev</span>
                                    @else
                                        <a href="{{ $roles->previousPageUrl() }}" style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--secondary);text-decoration:none;transition:all var(--transition);"
                                            onmouseenter="this.style.background='var(--secondary-light)'"
                                            onmouseleave="this.style.background=''">← Prev</a>
                                    @endif

                                    @if ($roles->hasMorePages())
                                        <a href="{{ $roles->nextPageUrl() }}" style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--secondary);text-decoration:none;transition:all var(--transition);"
                                            onmouseenter="this.style.background='var(--secondary-light)'"
                                            onmouseleave="this.style.background=''">Next →</a>
                                    @else
                                        <span style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--text-muted);opacity:.5;">Next →</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Search Empty State --}}
                        <div class="text-center" style="padding:3rem 1rem;">
                            <div style="width:56px;height:56px;background:var(--secondary-light);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                <i class="bi bi-search" style="font-size:1.4rem;color:var(--secondary);"></i>
                            </div>
                            <h3 style="font-size:.95rem;font-weight:700;color:var(--secondary);margin-bottom:.2rem;">No matching roles found</h3>
                            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:0;">Try adjusting your search query or module filters.</p>
                        </div>
                    @endif

                </div>
            </div>
        @else
            {{-- ──────────────────────────────────────
               RAW UPLOADED DATA TAB
            ────────────────────────────────────── --}}
            <div class="animate-in animate-in-delay-3 mb-4">
                <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">
                    
                    {{-- Table Header --}}
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                        <div style="display:flex;align-items:center;gap:.65rem;">
                            <div style="width:36px;height:36px;background:var(--secondary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-file-earmark-spreadsheet" style="color:var(--secondary);font-size:.95rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:.9rem;font-weight:700;color:var(--secondary);">Imported Records (UAM Data)</div>
                                <div style="font-size:.72rem;color:var(--text-muted);">
                                    Showing {{ $rawRecords->firstItem() }}–{{ $rawRecords->lastItem() }} of {{ $rawRecords->total() }} records
                                </div>
                            </div>
                        </div>
                        <span style="background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.25rem .75rem;font-size:.75rem;font-weight:700;">
                            {{ $rawRecords->total() }} Total Rows
                        </span>
                    </div>

                    @if($rawRecords->total() > 0)
                        {{-- Scrollable Table --}}
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
                                <thead>
                                    <tr style="background:var(--secondary-light);">
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">No</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">NIP</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Nama</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Jabatan</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Department</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Aplikasi</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Hak Akses</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Status</th>
                                        <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rawRecords as $i => $rec)
                                    <tr style="border-bottom:1px solid var(--border);transition:background var(--transition);"
                                        onmouseenter="this.style.background='var(--secondary-light)'"
                                        onmouseleave="this.style.background=''">
                                        <td style="padding:.7rem 1rem;color:var(--text-muted);font-size:.78rem;white-space:nowrap;">{{ $rec->no ?? $rawRecords->firstItem() + $i }}</td>
                                        <td style="padding:.7rem 1rem;font-weight:600;color:var(--secondary);white-space:nowrap;">{{ $rec->nip ?? '—' }}</td>
                                        <td style="padding:.7rem 1rem;font-weight:500;white-space:nowrap;">{{ $rec->nama ?? '—' }}</td>
                                        <td style="padding:.7rem 1rem;color:var(--text-muted);white-space:nowrap;">{{ $rec->jabatan ?? '—' }}</td>
                                        <td style="padding:.7rem 1rem;white-space:nowrap;">{{ $rec->department ?? '—' }}</td>
                                        <td style="padding:.7rem 1rem;white-space:nowrap;">
                                            @if ($rec->aplikasi)
                                                <span style="display:inline-flex;align-items:center;gap:.3rem;background:var(--secondary-light);color:var(--secondary);border-radius:6px;padding:.2rem .55rem;font-size:.75rem;font-weight:600;border:1px solid rgba(11,46,109,.12);">
                                                    <i class="bi bi-app-indicator" style="font-size:.7rem;"></i>{{ $rec->aplikasi }}
                                                </span>
                                            @else
                                                <span style="color:var(--text-muted);">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:.7rem 1rem;white-space:nowrap;font-family:monospace;font-weight:600;color:var(--secondary);">{{ $rec->hak_akses ?? '—' }}</td>
                                        <td style="padding:.7rem 1rem;white-space:nowrap;">
                                            @php
                                                $statusVal = strtolower($rec->status ?? '');
                                                $isActive  = in_array($statusVal, ['active', 'aktif', '1', 'yes', 'ya']);
                                            @endphp
                                            @if ($rec->status)
                                                <span style="display:inline-flex;align-items:center;gap:.3rem;border-radius:20px;padding:.2rem .65rem;font-size:.72rem;font-weight:700;
                                                    background:{{ $isActive ? '#e8f5e9' : 'var(--primary-light)' }};
                                                    color:{{ $isActive ? '#2e7d32' : 'var(--primary)' }}; border:1px solid {{ $isActive ? '#c8e6c9' : '#ffcdd2' }}">
                                                    <i class="bi {{ $isActive ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}" style="font-size:.65rem;"></i>
                                                    {{ $rec->status }}
                                                </span>
                                            @else
                                                <span style="color:var(--text-muted);">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:.7rem 1rem;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $rec->keterangan }}">
                                            {{ $rec->keterangan ?? '—' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if ($rawRecords->hasPages())
                        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                            <div style="font-size:.78rem;color:var(--text-muted);">
                                Page {{ $rawRecords->currentPage() }} of {{ $rawRecords->lastPage() }}
                            </div>
                            <div style="display:flex;gap:.35rem;">
                                @if ($rawRecords->onFirstPage())
                                    <span style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--text-muted);opacity:.5;">← Prev</span>
                                @else
                                    <a href="{{ $rawRecords->previousPageUrl() }}" style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--secondary);text-decoration:none;transition:all var(--transition);"
                                        onmouseenter="this.style.background='var(--secondary-light)'"
                                        onmouseleave="this.style.background=''">← Prev</a>
                                @endif

                                @if ($rawRecords->hasMorePages())
                                    <a href="{{ $rawRecords->nextPageUrl() }}" style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--secondary);text-decoration:none;transition:all var(--transition);"
                                        onmouseenter="this.style.background='var(--secondary-light)'"
                                        onmouseleave="this.style.background=''">Next →</a>
                                @else
                                    <span style="padding:.3rem .7rem;border-radius:6px;border:1.5px solid var(--border);font-size:.78rem;color:var(--text-muted);opacity:.5;">Next →</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @else
                        {{-- Search Empty State --}}
                        <div class="text-center" style="padding:3rem 1rem;">
                            <div style="width:56px;height:56px;background:var(--secondary-light);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                <i class="bi bi-search" style="font-size:1.4rem;color:var(--secondary);"></i>
                            </div>
                            <h3 style="font-size:.95rem;font-weight:700;color:var(--secondary);margin-bottom:.2rem;">No matching records found</h3>
                            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:0;">Try adjusting your search query or module filters.</p>
                        </div>
                    @endif

                </div>
            </div>
        @endif

    </main>

</div>

@endsection

@push('styles')
<style>
    /* Drag-over highlight */
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

    // Logout spinner
    document.getElementById('logoutForm').addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out…';
    });

    // ── Collapsible Upload card ────────────────────────────────────────
    const toggleUploadBtn = document.getElementById('toggleUploadBtn');
    const uploadCardCollapse = document.getElementById('uploadCardCollapse');
    
    // Check if there are errors on the page, if so, automatically expand upload card
    @if ($errors->any())
        uploadCardCollapse.style.display = 'block';
        toggleUploadBtn.style.background = 'var(--secondary-light)';
        toggleUploadBtn.style.borderColor = 'var(--secondary)';
        toggleUploadBtn.style.color = 'var(--secondary)';
    @endif

    toggleUploadBtn.addEventListener('click', function() {
        const isHidden = uploadCardCollapse.style.display === 'none';
        uploadCardCollapse.style.display = isHidden ? 'block' : 'none';
        toggleUploadBtn.style.background = isHidden ? 'var(--secondary-light)' : 'none';
        toggleUploadBtn.style.borderColor = isHidden ? 'var(--secondary)' : 'var(--border)';
        toggleUploadBtn.style.color = isHidden ? 'var(--secondary)' : 'var(--text-muted)';
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
        fileNameEl.textContent  = file.name;
        fileSizeEl.textContent  = formatSize(file.size);
        filePreview.style.display = 'flex';
        submitWrapper.style.display = 'block';
        dropZone.style.opacity = '0.5';
    }

    function clearFile() {
        fileInput.value = '';
        filePreview.style.display = 'none';
        submitWrapper.style.display = 'none';
        dropZone.style.opacity = '1';
    }

    fileInput.addEventListener('change', function () {
        if (this.files[0]) showFile(this.files[0]);
    });

    removeBtn.addEventListener('click', clearFile);

    // Drag and drop
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
        const file = e.dataTransfer.files[0];
        if (!file) return;

        const allowed = ['xlsx', 'xls', 'csv'];
        const ext     = file.name.split('.').pop().toLowerCase();

        if (!allowed.includes(ext)) {
            alert('Only .xlsx, .xls, and .csv files are accepted.');
            return;
        }

        // Assign to the input via DataTransfer
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFile(file);
    });

    // Submit spinner
    document.getElementById('importForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importing…';
    });
</script>
@endpush
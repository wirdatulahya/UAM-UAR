@extends('layouts.app')

@section('title', 'UAM SAP — User Access Matrix')

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
        <a href="#uamCollapse" data-bs-toggle="collapse" class="sidebar-nav-item {{ request()->routeIs('access-matrix.*') ? 'active' : 'collapsed' }}" role="button" aria-expanded="{{ request()->routeIs('access-matrix.*') ? 'true' : 'false' }}" aria-controls="uamCollapse">
            <i class="bi bi-table"></i>
            <span class="d-flex align-items-center w-100">
                User Access Matrix
                <i class="bi bi-chevron-down ms-auto" style="font-size:.7rem; transition: transform var(--transition);"></i>
            </span>
        </a>
        <div class="collapse {{ request()->routeIs('access-matrix.*') ? 'show' : '' }}" id="uamCollapse">
            <div style="padding: .25rem 0; background: var(--bg);">
                <a href="{{ route('access-matrix.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Request Access Matrix
                </a>
                <a href="#" class="sidebar-nav-item" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Approval Access Matrix
                </a>
            </div>
        </div>
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
                    <a href="{{ route('access-matrix.approval') }}" style="color:var(--text-muted);text-decoration:none;transition:color var(--transition);"
                       onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">Request Access Matrix</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item active" style="color:var(--secondary);font-weight:600;margin-left:.35rem;" aria-current="page">
                    UAM SAP
                    @if($uamRequest)
                        &nbsp;<span style="background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.1rem .55rem;font-size:.7rem;font-weight:700;">{{ $uamRequest->batch_name }}</span>
                    @endif
                </li>
            </ol>
        </nav>

        {{-- ── Page Header ── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 animate-in" style="gap:1rem;">
            <div>
                <h1 style="font-size:1.45rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                    <i class="bi bi-pc-display-horizontal me-2" style="color:var(--primary);"></i>UAM SAP Module
                    @if($uamRequest)
                        <span style="font-size:.75rem;font-weight:600;background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.2rem .65rem;vertical-align:middle;margin-left:.5rem;">{{ $uamRequest->batch_name }}</span>
                    @endif
                </h1>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                    @if($uamRequest)
                        Records from request &mdash; {{ $uamRequest->period }} {{ $uamRequest->year }} &mdash; {{ $uamRequest->record_count }} record(s)
                    @else
                        Search by Role to view, add, edit, or delete access records
                        @if($totalRecords > 0)
                            &nbsp;·&nbsp; <strong>{{ number_format($totalRecords) }}</strong> records in database
                        @endif
                    @endif
                </p>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                {{-- Add New Record --}}
                <a href="{{ route('access-matrix.create', $requestId ? ['request_id' => $requestId] : []) }}"
                    class="btn-primary-custom"
                    style="width:auto;padding:.55rem 1.25rem;font-size:.82rem;display:inline-flex;align-items:center;gap:.45rem;border-radius:10px;text-decoration:none;">
                    <i class="bi bi-plus-lg"></i>
                    Add Record
                </a>

                @if ($requestId && $uamRequest)
                    <form method="POST" action="{{ route('access-matrix.clear') }}" id="clearForm"
                          onsubmit="return confirm('Delete all {{ $uamRequest->record_count }} records in this request? This cannot be undone.');"
                          style="margin:0;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="request_id" value="{{ $requestId }}">
                        <button type="submit" id="clearBtn"
                            style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:10px;padding:.55rem 1.25rem;font-size:.82rem;font-weight:600;color:#c0392b;cursor:pointer;transition:all var(--transition);"
                            onmouseenter="this.style.borderColor='#c0392b';this.style.background='#fde8e9';"
                            onmouseleave="this.style.borderColor='var(--border)';this.style.background='none';">
                            <i class="bi bi-trash3-fill"></i>
                            Clear Request
                        </button>
                    </form>
                @elseif(!$requestId && $totalRecords > 0)
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

        {{-- ── Search Bar ── --}}

        <div class="animate-in animate-in-delay-1 mb-4"
             style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.02);">
            <form method="GET" action="{{ route('access-matrix.sap') }}" id="searchForm">
                @if($requestId)
                    <input type="hidden" name="request_id" value="{{ $requestId }}">
                @endif
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="moduleSelect" class="form-label" style="font-size:.82rem;font-weight:700;color:var(--secondary);">Module</label>
                        <select name="module" id="moduleSelect" class="form-select" style="font-size:.9rem;border-radius:10px;border-color:var(--border);" required>
                            <option value="">-- Select Module --</option>
                            @if(isset($availableModules))
                                @foreach($availableModules as $mod)
                                    <option value="{{ $mod }}" {{ (isset($module) && $module === $mod) ? 'selected' : '' }}>{{ $mod }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="periodSelect" class="form-label" style="font-size:.82rem;font-weight:700;color:var(--secondary);">Period</label>
                        <select name="period" id="periodSelect" class="form-select" style="font-size:.9rem;border-radius:10px;border-color:var(--border);" required>
                            <option value="">-- Select Period --</option>
                            @if(isset($availablePeriods))
                                @foreach($availablePeriods as $per)
                                    <option value="{{ $per }}" {{ (isset($period) && $period === $per) ? 'selected' : '' }}>{{ $per }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-8 col-lg-9">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.9rem;"></i>
                            <input type="text" name="search" id="searchInput" value="{{ $search }}"
                                   class="form-control"
                                   style="padding-left:2.6rem;font-size:.9rem;border-radius:10px;border-color:var(--border);"
                                   placeholder="Search by Role (e.g. ZPS-MD-1014-000000-PROJ-CHG)…"
                                   autocomplete="off" disabled>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                        <button type="submit" id="searchSubmitBtn" class="btn-primary-custom flex-grow-1"
                                style="padding:.65rem 1rem;font-size:.9rem;background:var(--secondary);border-radius:10px;box-shadow:none;" disabled>
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
                                @if ($roles->total() > 0)
                                    Showing {{ $roles->firstItem() }}–{{ $roles->lastItem() }} of {{ $roles->total() }} roles
                                    @if ($search)
                                        for <strong style="color:var(--secondary);">"{{ $search }}"</strong>
                                    @endif
                                @else
                                    @if ($search)
                                        No roles found for <strong>"{{ $search }}"</strong>
                                    @else
                                        No records available
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($roles->total() > 0)
                        <span style="background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.25rem .75rem;font-size:.75rem;font-weight:700;">
                            {{ $roles->total() }} Role(s)
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
                            @forelse ($roles as $i => $roleData)
                                @php
                                    $roleRecords = $recordsMap[$roleData->role] ?? collect();
                                    $firstRec = $roleRecords->first();
                                    $rowId = 'row-' . md5($roleData->role);
                                @endphp
                                <tr style="border-bottom:1px solid var(--border);transition:background var(--transition);"
                                    onmouseenter="this.style.background='var(--secondary-light)'"
                                    onmouseleave="this.style.background=''">
                                    <td style="padding:.7rem 1rem;color:var(--text-muted);font-size:.78rem;white-space:nowrap;">
                                        {{ $roles->firstItem() + $i }}
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;max-width:260px;">
                                        <span style="font-family:monospace;background:#f1f5f9;padding:.2rem .45rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);font-weight:700;color:var(--secondary);">
                                            {{ $roleData->role ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="padding:.7rem 1rem;color:var(--text);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                        title="{{ $roleData->description_role }}">
                                        {{ $roleData->description_role ?? '—' }}
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;">
                                        @if($roleRecords->count() > 0)
                                            <select class="form-select tcode-selector" 
                                                    data-row-id="{{ $rowId }}"
                                                    style="padding:.2rem 1.6rem .2rem .5rem;font-size:.75rem;font-weight:600;font-family:monospace;border:1px solid #bfdbfe;border-radius:6px;background:#eff6ff;color:#1d4ed8;cursor:pointer;min-width:100px;">
                                                @foreach($roleRecords as $rec)
                                                    <option value="{{ $rec->tcode }}" 
                                                            data-id="{{ $rec->id }}"
                                                            data-edit-url="{{ route('access-matrix.edit', $rec->id) }}"
                                                            data-delete-url="{{ route('access-matrix.destroy', $rec->id) }}">
                                                        {{ $rec->tcode ?: '—' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span style="color:var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;">
                                        <button type="button" class="view-access-btn-{{ $rowId }}" onclick="openAccessModal(this)"
                                            data-role="{{ htmlspecialchars($roleData->role ?? '—') }}"
                                            data-tcode="{{ htmlspecialchars($firstRec->tcode ?? '') }}"
                                            style="display:inline-flex;align-items:center;gap:.4rem;background:var(--secondary-light);color:var(--secondary);border:1px solid var(--border);border-radius:6px;padding:.3rem .75rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);"
                                            onmouseenter="this.style.background='var(--secondary)';this.style.color='#fff';"
                                            onmouseleave="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';">
                                            <i class="bi bi-shield-lock"></i> View Access
                                        </button>
                                    </td>
                                    <td style="padding:.7rem 1rem;white-space:nowrap;">
                                        <div class="d-flex align-items-center gap-1">
                                            {{-- Edit --}}
                                            <a href="{{ $firstRec ? route('access-matrix.edit', $firstRec->id) : '#' }}"
                                               class="edit-btn-{{ $rowId }}"
                                               style="display:inline-flex;align-items:center;gap:.3rem;background:#fef3c7;color:#d97706;border:none;border-radius:6px;padding:.3rem .6rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);text-decoration:none;"
                                               onmouseenter="this.style.filter='brightness(0.95)'"
                                               onmouseleave="this.style.filter=''">
                                                <i class="bi bi-pencil-fill"></i> Edit
                                            </a>
                                            {{-- Delete --}}
                                            <form method="POST" action="{{ $firstRec ? route('access-matrix.destroy', $firstRec->id) : '#' }}"
                                                  class="delete-form-{{ $rowId }}"
                                                  onsubmit="return confirm('Delete this record?\nRole: {{ addslashes($roleData->role) }}\nTCODE: ' + document.querySelector('.tcode-selector[data-row-id=\'{{ $rowId }}\']').value)"
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
                                            {{-- Initial state: no data at all --}}
                                            <div style="width:64px;height:64px;background:var(--secondary-light);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                                <i class="bi bi-folder2-open" style="font-size:1.6rem;color:var(--secondary);"></i>
                                            </div>
                                            <h3 style="font-size:1rem;font-weight:700;color:var(--secondary);margin-bottom:.3rem;">No records available</h3>
                                            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem;">
                                                There are currently no records for this request. Use <strong>Add Record</strong> to get started, or go back to <a href="{{ route('access-matrix.approval') }}" style="color:var(--secondary);">Request Access Matrix</a>.
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
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

                            @foreach ($roles->getUrlRange(max(1, $roles->currentPage() - 2), min($roles->lastPage(), $roles->currentPage() + 2)) as $page => $url)
                                @if ($page == $roles->currentPage())
                                    <span style="padding:.3rem .7rem;border-radius:6px;background:var(--secondary);color:#fff;font-size:.78rem;font-weight:700;">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" style="padding:.3rem .7rem;border-radius:6px;border:1px solid transparent;font-size:.78rem;color:var(--text-muted);text-decoration:none;transition:all var(--transition);"
                                       onmouseenter="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';"
                                       onmouseleave="this.style.background='';this.style.color='var(--text-muted)';">{{ $page }}</a>
                                @endif
                            @endforeach

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

                    {{-- ── Cascading selects: UNIT → BPO ── --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">

                        {{-- UNIT dropdown --}}
                        <div>
                            <label for="modalUnitSelect"
                                style="display:block;font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.4rem;">Unit</label>
                            <div style="position:relative;">
                                <select id="modalUnitSelect"
                                    style="width:100%;padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.85rem;font-weight:600;color:var(--secondary);background:#fff;appearance:none;cursor:pointer;transition:border-color var(--transition);outline:none;"
                                    onfocus="this.style.borderColor='var(--secondary)'"
                                    onblur="this.style.borderColor='var(--border)'">
                                </select>
                                <i class="bi bi-chevron-down" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-muted);font-size:.75rem;"></i>
                            </div>
                        </div>

                        {{-- BPO dropdown --}}
                        <div>
                            <label for="modalBpoSelect"
                                style="display:block;font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.4rem;">BPO</label>
                            <div style="position:relative;">
                                <select id="modalBpoSelect"
                                    style="width:100%;padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.85rem;font-weight:600;color:var(--secondary);background:#fff;appearance:none;cursor:pointer;transition:border-color var(--transition);outline:none;"
                                    onfocus="this.style.borderColor='var(--secondary)'"
                                    onblur="this.style.borderColor='var(--border)'">
                                </select>
                                <i class="bi bi-chevron-down" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-muted);font-size:.75rem;"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Access Owners panel --}}
                    <div style="border:1.5px solid #bbf7d0;border-radius:14px;overflow:hidden;margin-bottom:1.25rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1rem;background:#f0fdf4;border-bottom:1px solid #bbf7d0;">
                            <div style="display:flex;align-items:center;gap:.45rem;">
                                <i class="bi bi-people-fill" style="color:#166534;font-size:.9rem;"></i>
                                <span style="font-size:.72rem;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:.5px;">User Access Matrix</span>
                            </div>
                            <span id="modalOwnerCount" style="font-size:.7rem;font-weight:700;background:#166534;color:#fff;border-radius:20px;padding:.1rem .55rem;"></span>
                        </div>
                        <div id="modalOwnerScroll" style="max-height:260px;overflow-y:auto;padding:1rem;">
                            <div id="modalOwner" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.55rem;"></div>
                        </div>
                    </div>

                    {{-- TCODE badge --}}
                    <div>
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

    // ── Access Modal ────────────────────────────────────────────────
    const accessModal     = document.getElementById('accessModal');
    const unitSelect      = document.getElementById('modalUnitSelect');
    const bpoSelect       = document.getElementById('modalBpoSelect');
    const ownerEl         = document.getElementById('modalOwner');
    const ownerCountEl    = document.getElementById('modalOwnerCount');

    // Hierarchy cache for the currently open modal
    let _hierarchy = [];   // [{unit, bpos:[{bpo, owners:[]}]}]

    // ── Helpers ────────────────────────────────────────────────────
    function setSelectOptions(sel, options, placeholder) {
        sel.innerHTML = '';
        if (placeholder) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = placeholder;
            opt.disabled = true;
            opt.selected = true;
            sel.appendChild(opt);
        }
        options.forEach(function (val) {
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            sel.appendChild(opt);
        });
        // Auto-select if only one meaningful option
        if (options.length === 1) sel.value = options[0];
    }

    function renderOwners(owners) {
        ownerCountEl.textContent = owners.length;
        if (owners.length > 0) {
            ownerEl.innerHTML = owners.map(o =>
                `<div style="display:flex;align-items:center;gap:.4rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:.45rem .75rem;min-width:0;">
                    <i class="bi bi-person-check-fill" style="color:#166534;font-size:.8rem;flex-shrink:0;"></i>
                    <span style="font-size:.78rem;font-weight:600;color:#166534;line-height:1.3;word-break:break-word;">${o}</span>
                 </div>`
            ).join('');
        } else {
            ownerEl.innerHTML = '<span style="color:var(--text-muted);font-size:.82rem;">Select a Unit and BPO to see User Access Matrix</span>';
        }
        document.getElementById('modalOwnerScroll').scrollTop = 0;
    }

    function getOwnersForSelection() {
        const unitVal = unitSelect.value;
        const bpoVal  = bpoSelect.value;
        if (!unitVal || !bpoVal) { renderOwners([]); return; }

        const unitNode = _hierarchy.find(u => u.unit === unitVal);
        if (!unitNode) { renderOwners([]); return; }

        const bpoNode = unitNode.bpos.find(b => b.bpo === bpoVal);
        renderOwners(bpoNode ? bpoNode.owners : []);
    }

    // ── UNIT change → repopulate BPO → refresh owners ──────────────
    unitSelect.addEventListener('change', function () {
        const unitNode = _hierarchy.find(u => u.unit === this.value);
        const bpos = unitNode ? unitNode.bpos.map(b => b.bpo) : [];
        setSelectOptions(bpoSelect, bpos, bpos.length > 1 ? '— Select BPO —' : null);
        getOwnersForSelection();
    });

    // ── BPO change → refresh owners ────────────────────────────────
    bpoSelect.addEventListener('change', getOwnersForSelection);

    // ── Open modal ─────────────────────────────────────────────────
    async function openAccessModal(btn) {
        const role  = btn.dataset.role;
        const tcode = btn.dataset.tcode || '';

        document.getElementById('modalRole').textContent        = role;
        document.getElementById('modalTcodeHeader').textContent = tcode || '—';

        accessModal.style.display = 'flex';
        document.getElementById('modalLoading').style.display        = 'block';
        document.getElementById('modalContentWrapper').style.display = 'none';

        // Reset dropdowns & owners while loading
        _hierarchy = [];
        setSelectOptions(unitSelect, [], null);
        setSelectOptions(bpoSelect,  [], null);
        renderOwners([]);

        try {
            const url = `/access-matrix/sap/role-details?role=${encodeURIComponent(role)}&tcode=${encodeURIComponent(tcode)}`;
            const res  = await fetch(url);
            const data = await res.json();

            if (res.ok) {
                _hierarchy = data.hierarchy || [];

                // ── Populate UNIT dropdown ──────────────────────────
                const units = _hierarchy.map(u => u.unit);
                setSelectOptions(unitSelect, units, units.length > 1 ? '— Select Unit —' : null);

                // ── Populate BPO dropdown for selected unit ─────────
                const firstUnit = _hierarchy[0];
                const bpos = firstUnit ? firstUnit.bpos.map(b => b.bpo) : [];
                setSelectOptions(bpoSelect, bpos, bpos.length > 1 ? '— Select BPO —' : null);

                // ── Auto-render owners if both are auto-selected ────
                getOwnersForSelection();

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

    // ── Module & Period Selection Logic ─────────────────────────────────
    const moduleSelect = document.getElementById('moduleSelect');
    const periodSelect = document.getElementById('periodSelect');
    const searchInput = document.getElementById('searchInput');
    const searchSubmitBtn = document.getElementById('searchSubmitBtn');

    function checkSearchAvailability() {
        const hasModule = moduleSelect.value !== '';
        const hasPeriod = periodSelect.value !== '';
        
        if (hasModule && hasPeriod) {
            searchInput.disabled = false;
            searchSubmitBtn.disabled = false;
        } else {
            searchInput.disabled = true;
            searchSubmitBtn.disabled = true;
        }
    }

    moduleSelect.addEventListener('change', checkSearchAvailability);
    periodSelect.addEventListener('change', checkSearchAvailability);

    // Initial check on load
    checkSearchAvailability();

    // ── TCode Dropdown Change Handler ───────────────────────────────────
    document.querySelectorAll('.tcode-selector').forEach(select => {
        select.addEventListener('change', function() {
            const rowId = this.dataset.rowId;
            const selectedOption = this.options[this.selectedIndex];
            
            // Update View Access button
            const viewBtn = document.querySelector(`.view-access-btn-${rowId}`);
            if (viewBtn) {
                viewBtn.dataset.tcode = selectedOption.value;
            }
            
            // Update Edit button
            const editBtn = document.querySelector(`.edit-btn-${rowId}`);
            if (editBtn) {
                editBtn.href = selectedOption.dataset.editUrl;
            }
            
            // Update Delete form action
            const deleteForm = document.querySelector(`.delete-form-${rowId}`);
            if (deleteForm) {
                deleteForm.action = selectedOption.dataset.deleteUrl;
            }
        });
    });

</script>
@endpush
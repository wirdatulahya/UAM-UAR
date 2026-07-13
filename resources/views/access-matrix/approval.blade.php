@extends('layouts.app')

@section('title', 'Request Access Matrix')

@section('content')
{{-- Navbar --}}
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

            {{-- Right - Profile Dropdown --}}
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
                <a href="{{ route('access-matrix.approval') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.approval') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
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

        {{-- Page Header --}}
        <div class="mb-4 animate-in d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text);margin:0 0 .2rem;">Request Access Matrix</h1>
                <p style="font-size:.88rem;color:var(--text-muted);margin:0;">Manage user access matrix requests and batches</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(\App\Models\UamRequest::count() > 0)
                <form method="POST" action="{{ route('access-matrix.clear') }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete ALL UAM data? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn d-flex align-items-center gap-2" style="background:#fde8e9;color:#c0392b;border:none;border-radius:8px;padding:.5rem 1.25rem;font-weight:600;font-size:.85rem;transition:filter var(--transition);" onmouseenter="this.style.filter='brightness(0.95)'" onmouseleave="this.style.filter=''">
                        <i class="bi bi-trash-fill"></i> Delete Data
                    </button>
                </form>
                @endif

                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" style="background:#0066cc;border:none;border-radius:8px;padding:.5rem 1.25rem;font-weight:600;font-size:.85rem;" data-bs-toggle="modal" data-bs-target="#createUamModal">
                    <i class="bi bi-plus-lg" style="stroke-width: 2px;"></i> CREATE UAM
                </button>
            </div>
        </div>

        {{-- ── Filters & Search ──────────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in animate-in-delay-2" style="gap:1rem;flex-wrap:wrap;">
            <form method="GET" action="{{ route('access-matrix.approval') }}" id="filterForm"
                  class="d-flex align-items-center gap-3 flex-wrap" style="flex:1;">
                <select name="application" class="form-select" style="width:200px;border-radius:8px;font-size:.85rem;color:var(--text-muted);"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">Choose Application</option>
                    @foreach($availableApplications as $app)
                        <option value="{{ $app }}" {{ $filterApplication === $app ? 'selected' : '' }}>{{ $app }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-select" style="width:130px;border-radius:8px;font-size:.85rem;color:var(--text-muted);"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">Year</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ $filterYear === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
                <select name="period" class="form-select" style="width:130px;border-radius:8px;font-size:.85rem;color:var(--text-muted);"
                        onchange="document.getElementById('filterForm').submit()">
                    <option value="">Period</option>
                    @foreach($availablePeriods as $per)
                        <option value="{{ $per }}" {{ $filterPeriod === $per ? 'selected' : '' }}>{{ $per }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2"
                        style="background:#0066cc;border:none;border-radius:8px;padding:.45rem 1.25rem;font-weight:600;font-size:.85rem;">
                    <i class="bi bi-search" style="font-size:.8rem;"></i> SEARCH
                </button>
                @if($filterApplication || $filterYear || $filterPeriod || $search)
                    <a href="{{ route('access-matrix.approval') }}"
                       style="display:inline-flex;align-items:center;gap:.3rem;padding:.45rem .9rem;border-radius:8px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;color:var(--text-muted);text-decoration:none;transition:all var(--transition);"
                       onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';"
                       onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </form>


        </div>

        {{-- ── Request Table ───────────────────────────────────────────────── --}}
        <div class="animate-in animate-in-delay-3">
            <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">

                {{-- Table Header --}}
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        <div style="width:36px;height:36px;background:var(--secondary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-inbox-fill" style="color:var(--secondary);font-size:.95rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.9rem;font-weight:700;color:var(--secondary);">UAM Requests</div>
                            <div style="font-size:.72rem;color:var(--text-muted);">
                                {{ $requests->count() }} request(s)
                                @if($filterApplication || $filterYear || $filterPeriod || $search)
                                    &nbsp;·&nbsp; <span style="color:var(--secondary);font-weight:600;">Filtered</span>
                                @else
                                    &nbsp;in total
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.85rem;color:var(--text);">
                        <thead style="background:#fcfcfc;">
                            <tr>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);width:5%;">No</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Application</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Period</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Modul</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Requested By</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">AO</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Status</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr style="cursor:pointer;transition:background var(--transition);"
                                onmouseenter="this.style.background='var(--secondary-light)'"
                                onmouseleave="this.style.background=''"
                                onclick="window.location='{{ route('access-matrix.sap', ['request_id' => $req->id]) }}'">
                                <td style="padding:1rem 1.25rem;vertical-align:middle;color:var(--text-muted);">{{ $req->no }}</td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;font-weight:500;">{{ $req->application }}</td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">{{ $req->period }} {{ $req->year }}</td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                    <span style="font-family:monospace;background:#f1f5f9;padding:.2rem .45rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);font-weight:600;color:var(--secondary);">
                                        {{ $req->module ?: 'N/A' }}
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                    {{ $req->requester_nik ?: 'N/A' }}
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                    {{ ltrim($req->ao, " \t\n\r\0\x0B:-") ?: 'N/A' }}
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                    @if($req->status == 'Draft')
                                        <span class="badge" style="background:#e3f2fd;color:#0288d1;padding:.35rem .65rem;border-radius:20px;font-weight:600;display:inline-flex;align-items:center;gap:.3rem;">
                                            <i class="bi bi-pencil-fill" style="font-size:.7rem;"></i> {{ $req->status }}
                                        </span>
                                    @elseif($req->status == 'Done')
                                        <span class="badge" style="background:#e8f5e9;color:#2e7d32;padding:.35rem .65rem;border-radius:20px;font-weight:600;display:inline-flex;align-items:center;gap:.3rem;">
                                            <i class="bi bi-check-circle-fill" style="font-size:.7rem;"></i> {{ $req->status }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $req->status }}</span>
                                    @endif
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;text-align:center;">
                                    <div class="dropdown" onclick="event.stopPropagation();">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:0;">
                                            <i class="bi bi-three-dots-vertical" style="font-size:1.1rem;"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" style="border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.08);border-color:var(--border);">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('access-matrix.sap', ['request_id' => $req->id]) }}" style="font-size:.85rem;display:flex;align-items:center;gap:.5rem;">
                                                    <i class="bi bi-eye"></i> View Records
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('access-matrix.clear') }}" style="margin:0;" onsubmit="return confirm('Delete this request and all its records? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="request_id" value="{{ $req->id }}">
                                                    <button type="submit" class="dropdown-item text-danger" style="font-size:.85rem;display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                                                        <i class="bi bi-trash"></i> Delete Request
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="padding:3.5rem 1rem;text-align:center;">
                                    <div style="width:64px;height:64px;background:var(--secondary-light);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                        <i class="bi bi-inbox" style="font-size:1.6rem;color:var(--secondary);"></i>
                                    </div>
                                    <h3 style="font-size:1rem;font-weight:700;color:var(--secondary);margin-bottom:.3rem;">No requests yet</h3>
                                    <p style="font-size:.82rem;color:var(--text-muted);margin:0;">Upload an Excel file above to create your first UAM request.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </main>
</div>

{{-- Create UAM Modal --}}
<div class="modal fade" id="createUamModal" tabindex="-1" aria-labelledby="createUamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border:none;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,0.12);">
            <div class="modal-header" style="border-bottom:1px solid var(--border);padding:1.5rem 1.75rem;">
                <div style="display:flex;align-items:center;gap:.65rem;">
                    <div style="width:36px;height:36px;background:var(--secondary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-file-earmark-arrow-up-fill" style="color:var(--secondary);font-size:.95rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="createUamModalLabel" style="font-size:1.05rem;font-weight:800;color:var(--secondary);margin:0;">New UAM Request</h5>
                        <div style="font-size:.75rem;color:var(--text-muted);">Upload the Excel file to create a new UAM request batch</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:1.75rem;">
                <form method="POST" action="{{ route('access-matrix.import') }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    {{-- File upload area --}}
                    <div id="uploadCard"
                         style="background:#fafbff;border:2px dashed var(--border);border-radius:14px;padding:2rem;text-align:center;cursor:pointer;transition:border-color var(--transition),background var(--transition);"
                         onclick="document.getElementById('fileInput').click();"
                         ondragover="event.preventDefault();this.style.borderColor='var(--secondary)';this.style.background='var(--secondary-light)';"
                         ondragleave="this.style.borderColor='var(--border)';this.style.background='#fafbff';"
                         ondrop="handleDrop(event)">
                        <div style="width:56px;height:56px;background:var(--secondary-light);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                            <i class="bi bi-file-earmark-arrow-up-fill" style="font-size:1.6rem;color:var(--secondary);"></i>
                        </div>
                        <h3 style="font-size:1.05rem;font-weight:700;color:var(--secondary);margin-bottom:.4rem;">
                            Drag &amp; Drop your UAM Excel file here
                        </h3>
                        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem;">
                            Supports <strong>.xlsx</strong>, <strong>.xls</strong>, and <strong>.csv</strong> &nbsp;·&nbsp; Max 10 MB
                        </p>
                        <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:1.25rem;">
                            Expected columns: <code>Role</code>, <code>Description Role</code>, <code>TCODE</code>, <code>UNIT</code>, <code>BPO</code>, <code>Access Owner</code>
                        </p>
    
                        <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" style="display:none;">
    
                        <div id="fileLabel"
                            style="display:inline-flex;align-items:center;gap:.5rem;background:var(--secondary);color:#fff;border:none;border-radius:8px;padding:.6rem 1.5rem;font-size:.85rem;font-weight:600;cursor:pointer;transition:filter var(--transition);"
                            onmouseenter="this.style.filter='brightness(1.1)'"
                            onmouseleave="this.style.filter=''">
                            <i class="bi bi-folder2-open"></i>
                            Browse File
                        </div>
                    </div>
    
                    {{-- File preview --}}
                    <div id="filePreview" style="display:none;margin-top:1.25rem;padding:1rem 1.25rem;background:var(--secondary-light);border-radius:12px;align-items:center;gap:.75rem;">
                        <div style="width:42px;height:42px;background:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                            <i class="bi bi-file-earmark-spreadsheet-fill" style="font-size:1.2rem;color:var(--secondary);"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div id="fileName" style="font-size:.9rem;font-weight:600;color:var(--secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                            <div id="fileSize" style="font-size:.75rem;color:var(--text-muted);"></div>
                        </div>
                        <button type="button" id="removeFile"
                            style="background:none;border:none;padding:.2rem .4rem;color:var(--text-muted);cursor:pointer;border-radius:6px;font-size:1.1rem;flex-shrink:0;"
                            title="Remove file">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
    
                    {{-- Submit button (hidden by default until file is selected) --}}
                    <div id="submitWrapper" style="display:none;margin-top:1.5rem;text-align:right;">
                        <button type="submit" id="submitBtn" class="btn btn-primary" style="background:#0066cc;border:none;border-radius:8px;padding:.6rem 1.75rem;font-weight:600;font-size:.85rem;">
                            <i class="bi bi-upload me-2"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Profile dropdown ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const btn     = document.getElementById('profileDropdownBtn');
        const menu    = document.getElementById('profileDropdownMenu');
        const chevron = document.getElementById('profileChevron');
        let isOpen    = false;

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            isOpen = !isOpen;
            menu.style.display = isOpen ? 'block' : 'none';
            if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
        });

        document.addEventListener('click', function() {
            if (isOpen) {
                isOpen = false;
                menu.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });

        menu.addEventListener('click', function(e) { e.stopPropagation(); });

        // ── File upload handling ──────────────────────────────────────────────
        const fileInput    = document.getElementById('fileInput');
        const filePreview  = document.getElementById('filePreview');
        const fileNameEl   = document.getElementById('fileName');
        const fileSizeEl   = document.getElementById('fileSize');
        const removeBtn    = document.getElementById('removeFile');
        const submitWrapper = document.getElementById('submitWrapper');

        function formatBytes(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function showFile(file) {
            fileNameEl.textContent = file.name;
            fileSizeEl.textContent = formatBytes(file.size);
            filePreview.style.display  = 'flex';
            submitWrapper.style.display = 'block';
        }

        function clearFile() {
            fileInput.value = '';
            filePreview.style.display   = 'none';
            submitWrapper.style.display = 'none';
        }

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) showFile(this.files[0]);
        });

        removeBtn.addEventListener('click', clearFile);
    });

    // ── Drag-and-drop handler ────────────────────────────────────────────────
    function handleDrop(e) {
        e.preventDefault();
        const card = document.getElementById('uploadCard');
        card.style.borderColor = 'var(--border)';
        card.style.background  = '#fafbff';
        const dt = e.dataTransfer;
        if (dt.files && dt.files[0]) {
            const fileInput = document.getElementById('fileInput');
            // Create a new DataTransfer to set files
            const transfer = new DataTransfer();
            transfer.items.add(dt.files[0]);
            fileInput.files = transfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    }
</script>
@endpush
@endsection

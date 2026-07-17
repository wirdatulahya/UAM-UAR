@extends('layouts.app')

@section('title', 'Approval Access Matrix')

@section('content')
{{-- Navbar --}}
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

            {{-- Right - Profile Dropdown --}}
            <div class="position-relative" id="profileDropdownWrapper">
                <button id="profileDropdownBtn" type="button"
                    style="background:none;border:1.5px solid var(--border);border-radius:40px;padding:.35rem .75rem .35rem .45rem;display:flex;align-items:center;gap:.6rem;cursor:pointer;transition:all var(--transition);">
                    <div style="width:32px;height:32px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <i class="bi bi-person-fill" style="color:#fff;font-size:.9rem;"></i>
                        @endif
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

                    <a href="{{ route('profile.index') }}"
                        style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--text);text-decoration:none;transition:background var(--transition);"
                        onmouseenter="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';"
                        onmouseleave="this.style.background='';this.style.color='var(--text)';">
                        <i class="bi bi-person-circle" style="font-size:.9rem;color:var(--text-muted);"></i>
                        My Profile
                    </a>

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
                <a href="{{ route('access-matrix.request.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.request.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Request Access Matrix
                </a>
                <a href="{{ route('access-matrix.approval.sap') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.uam-request.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Accept
                </a>
                <a href="{{ route('access-matrix.approval.sap') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.approval.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
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

        {{-- ── Breadcrumbs ── --}}
        <nav aria-label="breadcrumb" class="animate-in" style="margin-bottom:1rem;">
            <ol class="breadcrumb" style="background:none;padding:0;margin:0;font-size:.78rem;font-weight:500;display:flex;gap:.35rem;list-style:none;">
                <li class="breadcrumb-item d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;transition:color var(--transition);"
                       onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">Dashboard</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item d-flex align-items-center">
                    <a href="{{ route('access-matrix.approval.sap') }}" style="color:var(--text-muted);text-decoration:none;transition:color var(--transition);"
                       onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">Approval Access Matrix</a>
                    <span style="color:var(--text-muted);margin-left:.35rem;">&gt;</span>
                </li>
                <li class="breadcrumb-item active" style="color:var(--secondary);font-weight:600;margin-left:.35rem;" aria-current="page">UAM SAP</li>
            </ol>
        </nav>

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
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--text);margin:0 0 .2rem;">Approval Access Matrix</h1>
                <p style="font-size:.88rem;color:var(--text-muted);margin:0;">Final review of accepted requests</p>
            </div>
        </div>

        {{-- ── Filters & Search ──────────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in animate-in-delay-2" style="gap:1rem;flex-wrap:wrap;">
            <form method="GET" action="{{ route('access-matrix.approval.sap') }}" id="filterForm"
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
                    <a href="{{ route('access-matrix.approval.sap') }}"
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
                            <div style="font-size:.9rem;font-weight:700;color:var(--secondary);">Approval Pending Requests</div>
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
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Summary</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Status</th>
                                <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr style="transition:background var(--transition); {{ $req->status !== 'Review' ? 'cursor:pointer;' : '' }}"
                                onmouseenter="{{ $req->status !== 'Review' ? 'this.style.background=\'var(--secondary-light)\'' : '' }}"
                                onmouseleave="this.style.background=''"
                                onclick="if('{{ $req->status }}' !== 'Review') window.location='{{ route('access-matrix.sap', ['request_id' => $req->id, 'source' => 'stage2']) }}'">
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
                                    @if($req->status === 'Review')
                                        <div style="font-size:.78rem;color:var(--text-muted);font-style:italic;">Pending Accept Review</div>
                                    @else
                                        @php
                                            $approved = $req->records()->where('status', 'Approved')->count();
                                            $returned = $req->records()->where('status', 'Return')->count();
                                        @endphp
                                        <div style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">
                                            <span style="color:#15803d;font-weight:600;">{{ $approved }} Approved</span>, 
                                            <span style="color:#b91c1c;font-weight:600;">{{ $returned }} Returned</span>
                                        </div>
                                    @endif
                                </td>

                                <td style="padding:.9rem 1.25rem;vertical-align:middle;" onclick="event.stopPropagation();">
                                    @php
                                        $si = match($req->status) {
                                            'Draft'         => ['dot' => '#9ca3af', 'color' => '#6b7280', 'icon' => 'bi-circle-half',           'label' => 'Draft'],
                                            'Review'        => ['dot' => '#f59e0b', 'color' => '#92400e', 'icon' => 'bi-circle-fill',           'label' => 'Waiting for Accept Review'],
                                            'Stage 2'       => ['dot' => '#3b82f6', 'color' => '#1d4ed8', 'icon' => 'bi-circle-fill',           'label' => 'Pending Final Approval'],
                                            'Approved','Done'=> ['dot' => '#22c55e', 'color' => '#15803d', 'icon' => 'bi-check-circle-fill',    'label' => 'Approved'],
                                            'Need Revision','Return' => ['dot' => '#ef4444', 'color' => '#b91c1c', 'icon' => 'bi-exclamation-circle-fill','label' => 'Return'],
                                            default         => ['dot' => '#9ca3af', 'color' => '#6b7280', 'icon' => 'bi-circle',               'label' => $req->status],
                                        };
                                    @endphp
                                    <span style="display:inline-flex;align-items:center;gap:.45rem;">
                                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $si['dot'] }};flex-shrink:0;box-shadow:0 0 0 2px {{ $si['dot'] }}22;"></span>
                                        <span style="font-size:.8rem;font-weight:600;color:{{ $si['color'] }};">{{ $si['label'] }}</span>
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;text-align:center;" onclick="event.stopPropagation();">
                                    @if($req->status === 'Stage 2')
                                    <a href="{{ route('access-matrix.sap', ['request_id' => $req->id, 'source' => 'stage2']) }}"
                                       class="btn btn-sm"
                                       style="padding:.3rem .7rem;font-size:.78rem;font-weight:600;color:var(--primary);background:var(--primary-light);border:none;border-radius:6px;transition:filter var(--transition);"
                                       onmouseenter="this.style.filter='brightness(0.95)'" onmouseleave="this.style.filter=''">
                                       <i class="bi bi-box-arrow-in-up-right me-1"></i> Review
                                    </a>
                                    @elseif($req->status === 'Review')
                                    <button class="btn btn-sm" disabled
                                       style="padding:.3rem .7rem;font-size:.78rem;font-weight:600;color:#9ca3af;background:#f3f4f6;border:none;border-radius:6px;cursor:not-allowed;">
                                       <i class="bi bi-lock-fill me-1"></i> Locked
                                    </button>
                                    @else
                                    <a href="{{ route('access-matrix.sap', ['request_id' => $req->id, 'source' => 'stage2']) }}"
                                       class="btn btn-sm"
                                       style="padding:.3rem .7rem;font-size:.78rem;font-weight:600;color:var(--secondary);background:var(--secondary-light);border:none;border-radius:6px;transition:filter var(--transition);"
                                       onmouseenter="this.style.filter='brightness(0.95)'" onmouseleave="this.style.filter=''">
                                       <i class="bi bi-eye-fill me-1"></i> View
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" style="padding:3.5rem 1rem;text-align:center;">
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
    });
</script>
@endpush
@endsection




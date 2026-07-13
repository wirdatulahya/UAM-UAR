@extends('layouts.app')

@section('title', 'Request UAM')

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

        {{-- Page Header --}}
        <div class="mb-4 animate-in">
            <h1 style="font-size:1.6rem;font-weight:800;color:var(--text);margin:0 0 .2rem;">Request UAM</h1>
            <p style="font-size:.88rem;color:var(--text-muted);margin:0;">Manage user access matrix request</p>
        </div>

        {{-- Filters & Search --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in animate-in-delay-1">
            <div class="d-flex align-items-center gap-3">
                <select class="form-select" style="width:220px;border-radius:8px;font-size:.85rem;color:var(--text-muted);">
                    <option selected>Choose Application</option>
                    <option value="SYGAP">SYGAP</option>
                    <option value="EVOLUTION">EVOLUTION</option>
                </select>
                <select class="form-select" style="width:140px;border-radius:8px;font-size:.85rem;color:var(--text-muted);">
                    <option selected>Year</option>
                    <option value="2026">2026</option>
                </select>
                <select class="form-select" style="width:140px;border-radius:8px;font-size:.85rem;color:var(--text-muted);">
                    <option selected>Period</option>
                    <option value="July">July</option>
                </select>
                <button class="btn btn-primary d-flex align-items-center gap-2" style="background:#0066cc;border:none;border-radius:8px;padding:.45rem 1.25rem;font-weight:600;font-size:.85rem;">
                    <i class="bi bi-search" style="font-size:.8rem;"></i> SEARCH
                </button>
            </div>
            <div>
                <div class="input-group" style="width:250px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Search..." style="border-radius:0 8px 8px 0;font-size:.85rem;box-shadow:none;">
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card border-0 animate-in animate-in-delay-2" style="border-radius:12px;box-shadow:var(--card-shadow);overflow:hidden;">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:.85rem;color:var(--text);">
                    <thead style="background:#fcfcfc;">
                        <tr>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);width:5%;">No</th>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Application</th>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Period <i class="bi bi-arrow-down-up" style="font-size:.7rem;color:var(--text-muted);"></i></th>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Batch Name <i class="bi bi-arrow-down-up" style="font-size:.7rem;color:var(--text-muted);"></i></th>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Requested By</th>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Division</th>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);">Status</th>
                            <th style="padding:1rem 1.25rem;font-weight:700;color:#333;border-bottom:1px solid var(--border);text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr style="cursor:pointer;" onclick="window.location='{{ route('access-matrix.sap') }}'">
                            <td style="padding:1rem 1.25rem;vertical-align:middle;color:var(--text-muted);">{{ $req->no }}</td>
                            <td style="padding:1rem 1.25rem;vertical-align:middle;font-weight:500;">{{ $req->application }}</td>
                            <td style="padding:1rem 1.25rem;vertical-align:middle;">{{ $req->period }}</td>
                            <td style="padding:1rem 1.25rem;vertical-align:middle;">{{ $req->batch_name }}</td>
                            <td style="padding:1rem 1.25rem;vertical-align:middle;">{{ $req->requested_by }}</td>
                            <td style="padding:1rem 1.25rem;vertical-align:middle;">{{ $req->division }}</td>
                            <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                @if($req->status == 'Draft')
                                    <span class="badge" style="background:#e3f2fd;color:#0288d1;padding:.35rem .65rem;border-radius:20px;font-weight:600;display:inline-flex;align-items:center;gap:.3rem;">
                                        <i class="bi bi-check-circle-fill" style="font-size:.7rem;"></i> {{ $req->status }}
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
                                        <li><a class="dropdown-item" href="{{ route('access-matrix.sap') }}" style="font-size:.85rem;display:flex;align-items:center;gap:.5rem;"><i class="bi bi-eye"></i> View Details</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

@push('scripts')
<script>
    // Profile dropdown script
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('profileDropdownBtn');
        const menu = document.getElementById('profileDropdownMenu');
        const chevron = document.getElementById('profileChevron');
        let isOpen = false;

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            isOpen = !isOpen;
            menu.style.display = isOpen ? 'block' : 'none';
            if (chevron) {
                chevron.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        });

        document.addEventListener('click', function() {
            if(isOpen) {
                isOpen = false;
                menu.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });

        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
@endpush
@endsection

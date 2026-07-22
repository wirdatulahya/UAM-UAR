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

            {{-- Right — Profile Dropdown --}}
            <x-navbar-right />

        </div>
    </div>
</nav>

<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

                <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Approval Access Matrix', 'url' => route('access-matrix.approval.index')],
            ['label' => 'UAM SAP'],
        ]" />

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
                <p style="font-size:.88rem;color:var(--text-muted);margin:0;">Review and approve submitted user access matrix requests</p>
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
                                onclick="window.location='{{ route('access-matrix.sap', ['request_id' => $req->id, 'source' => 'approval']) }}'">
                                <td style="padding:1rem 1.25rem;vertical-align:middle;color:var(--text-muted);">{{ $req->no }}</td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;font-weight:500;">{{ $req->application }}</td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">{{ $req->full_period }}</td>
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
                                <td style="padding:.9rem 1.25rem;vertical-align:middle;" onclick="event.stopPropagation();">
                                    @php
                                        $si = match($req->status) {
                                            'Draft'         => ['dot' => '#9ca3af', 'color' => '#6b7280', 'icon' => 'bi-circle-half',           'label' => 'Draft'],
                                            'Review'        => ['dot' => '#f59e0b', 'color' => '#92400e', 'icon' => 'bi-circle-fill',           'label' => 'Under Review'],
                                            'Approved','Done'=> ['dot' => '#22c55e', 'color' => '#15803d', 'icon' => 'bi-check-circle-fill',    'label' => 'Approved'],
                                            'Need Revision','Return','Returned' => ['dot' => '#ef4444', 'color' => '#b91c1c', 'icon' => 'bi-exclamation-circle-fill','label' => 'Returned'],
                                            default         => ['dot' => '#9ca3af', 'color' => '#6b7280', 'icon' => 'bi-circle',               'label' => $req->status],
                                        };
                                    @endphp
                                    <span style="display:inline-flex;align-items:center;gap:.45rem;">
                                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $si['dot'] }};flex-shrink:0;box-shadow:0 0 0 2px {{ $si['dot'] }}22;"></span>
                                        <span style="font-size:.8rem;font-weight:600;color:{{ $si['color'] }};">{{ $si['label'] }}</span>
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;text-align:center;" onclick="event.stopPropagation();">
                                    <a href="{{ route('access-matrix.sap', ['request_id' => $req->id, 'source' => 'approval']) }}"
                                       class="btn btn-sm"
                                       style="padding:.3rem .7rem;font-size:.78rem;font-weight:600;color:var(--primary);background:var(--primary-light);border:none;border-radius:6px;transition:filter var(--transition);"
                                       onmouseenter="this.style.filter='brightness(0.95)'" onmouseleave="this.style.filter=''">
                                       <i class="bi bi-eye-fill me-1"></i> View Details
                                    </a>
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
@push('scripts')
<script>
</script>
@endpush
@endsection




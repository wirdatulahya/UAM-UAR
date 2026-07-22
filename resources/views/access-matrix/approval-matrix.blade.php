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
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                    {{ $req->full_period }}
                                    @if($req->is_latest ?? false)
                                        <span style="background-color:#15803d;color:white;font-size:0.65rem;padding:0.15rem 0.4rem;border-radius:12px;margin-left:0.4rem;font-weight:700;display:inline-block;vertical-align:middle;">Latest</span>
                                    @endif
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                    <span style="font-family:monospace;background:#f1f5f9;padding:.2rem .45rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);font-weight:600;color:var(--secondary);">
                                        {{ $req->module ?: 'N/A' }}
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem;vertical-align:middle;">
                                    {{ $req->requester->name ?? 'N/A' }}
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
                                    <div class="dropdown" onclick="event.stopPropagation();">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:0;">
                                            <i class="bi bi-three-dots-vertical" style="font-size:1.1rem;"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" style="border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.08);border-color:var(--border);">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('access-matrix.sap', ['request_id' => $req->id, 'source' => 'stage2']) }}" style="font-size:.85rem;display:flex;align-items:center;gap:.5rem;padding:.5rem 1.25rem;">
                                                    <i class="bi bi-eye"></i> View Records
                                                </a>
                                            </li>
                                            @if($req->status !== 'Draft')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('access-matrix.download-excel', $req->id) }}" style="font-size:.85rem;display:flex;align-items:center;gap:.5rem;color:var(--secondary);padding:.5rem 1.25rem;">
                                                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('access-matrix.download-pdf', $req->id) }}" style="font-size:.85rem;display:flex;align-items:center;gap:.5rem;color:var(--secondary);padding:.5rem 1.25rem;">
                                                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                                                </a>
                                            </li>
                                            @endif
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="openVersionHistoryModal({{ $req->id }}, '{{ htmlspecialchars($req->application) }}')" style="font-size:.85rem;display:flex;align-items:center;gap:.5rem;color:var(--secondary);padding:.5rem 1.25rem;width:100%;text-align:left;border:none;background:transparent;outline:none;box-shadow:none;">
                                                    <i class="bi bi-clock-history"></i> Version History
                                                </button>
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

{{-- Version History Modal --}}
<div class="modal fade" id="versionHistoryModal" tabindex="-1" aria-labelledby="versionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border:none;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,0.12);">
            <div class="modal-header" style="border-bottom:1px solid var(--border);padding:1.5rem 1.75rem;">
                <div style="display:flex;align-items:center;gap:.65rem;">
                    <div style="width:36px;height:36px;background:var(--secondary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-clock-history" style="color:var(--secondary);font-size:.95rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="versionHistoryModalLabel" style="font-size:1.05rem;font-weight:800;color:var(--secondary);margin:0;">Version History</h5>
                        <div style="font-size:.75rem;color:var(--text-muted);">History for <strong id="versionHistoryAppName"></strong></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:1.75rem;" id="versionHistoryContent">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.openVersionHistoryModal = function(id, appName) {
        document.getElementById('versionHistoryAppName').textContent = appName;
        document.getElementById('versionHistoryContent').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
        new bootstrap.Modal(document.getElementById('versionHistoryModal')).show();
        
        fetch(`/access-matrix/request/${id}/history`)
            .then(res => res.json())
            .then(data => {
                let html = '<div class="table-responsive"><table class="table table-hover mb-0" style="font-size:.85rem;">';
                html += '<thead style="background:#fcfcfc;"><tr><th>Version</th><th>Status</th><th>Created</th><th>Modified</th><th>Requested By</th><th>Accepted By</th><th>Approved By</th></tr></thead><tbody>';
                
                const latestNonDraftIndex = data.findIndex(item => item.status !== 'Draft');

                data.forEach((item, index) => {
                    let isLatest = index === latestNonDraftIndex && latestNonDraftIndex !== -1;
                    let badge = isLatest ? '<span class="badge bg-success ms-2">Latest</span>' : '';
                    
                    html += `<tr style="cursor:pointer;" onclick="window.location.href='${item.view_url}'">
                        <td style="font-weight:600;">${item.version} ${badge}</td>
                        <td>${item.status}</td>
                        <td>${item.created_at}</td>
                        <td>${item.updated_at}</td>
                        <td>${item.requester_name}</td>
                        <td>${item.accepted_by}</td>
                        <td>${item.approved_by}</td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                document.getElementById('versionHistoryContent').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('versionHistoryContent').innerHTML = '<div class="alert alert-danger">Failed to load history</div>';
            });
    };
</script>
@endpush
@endsection




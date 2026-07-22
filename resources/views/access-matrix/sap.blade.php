@extends('layouts.app')

@section('title', 'UAM SAP — User Access Matrix')

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
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

{{-- ─── App Shell ──────────────────────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <x-sidebar />

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
                    <i class="bi bi-exclamation-triangle-fill"></i> Validation Error
                </div>
                <ul style="margin:0;padding-left:1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

                @php
            $isApproval = request('source') === 'approval';
            $isStage2   = request('source') === 'stage2';

            if ($isStage2) {
                $moduleRoute = route('access-matrix.approval.index');
                $moduleName  = 'Approval Access Matrix';
                $tableRoute  = route('access-matrix.approval.sap');
            } else {
                $moduleRoute = $isApproval ? route('access-matrix.uam-request.index') : route('access-matrix.request.index');
                $moduleName  = $isApproval ? 'Accept' : 'Request Access Matrix';
                $tableRoute  = $isApproval ? route('access-matrix.uam-request.sap') : route('access-matrix.request.sap');
            }

            $breadcrumbItems = [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => $moduleName, 'url' => $moduleRoute],
                ['label' => 'UAM SAP', 'url' => $tableRoute],
                array_filter([
                    'label' => 'Request Details',
                    'badge' => ($uamRequest && $uamRequest->module) ? $uamRequest->module : null,
                ]),
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbItems" />

        {{-- ── Page Header ── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 animate-in" style="gap:1rem;">
            <div>
                <h1 style="font-size:1.45rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                    <i class="bi bi-pc-display-horizontal me-2" style="color:var(--primary);"></i>UAM SAP Module
                    @if($uamRequest && $uamRequest->module)
                        <span style="font-size:.75rem;font-weight:600;background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.2rem .65rem;vertical-align:middle;margin-left:.5rem;">{{ $uamRequest->module }}</span>
                    @endif
                </h1>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                    @if($uamRequest)
                        Records from request &mdash; {{ $uamRequest->full_period ?: 'N/A' }} &mdash; {{ $uamRequest->record_count }} record(s)
                    @else
                        Search by Role to view, add, edit, or delete access records
                        @if($totalRecords > 0)
                            &nbsp;·&nbsp; <strong>{{ number_format($totalRecords) }}</strong> records in database
                        @endif
                    @endif
                </p>
            </div>
            


        </div>

        {{-- ── Search Bar ── --}}

        <div class="animate-in animate-in-delay-1 mb-4"
             style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.02);">
            <form method="GET" action="{{ route('access-matrix.sap') }}" id="searchForm">
                @if($requestId)
                    <input type="hidden" name="request_id" value="{{ $requestId }}">
                @endif

                @if(isset($uamRequest) && $uamRequest)
                @php
                    $status = $uamRequest->status;
                    
                    // Step 1: Under Review
                    $step1Active = ($status === 'Draft');
                    $step1Completed = ($status === 'Approved' || $status === 'Returned' || $status === 'Return');

                    // Step 2: Final Status
                    $step2Active = ($status === 'Approved' || $status === 'Returned' || $status === 'Return');
                    $isApproved = ($status === 'Approved' || $status === 'Done');
                    $isReturned = ($status === 'Returned' || $status === 'Return' || $status === 'Need Revision');
                @endphp

                {{-- Progress Tracker --}}
                <div style="display:flex; align-items:center; justify-content:center; gap:2.5rem; margin-bottom:1.75rem; padding:0.5rem 1rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem; flex-wrap: wrap;">
                    
                    {{-- Step 1: Under Review --}}
                    <div style="display:flex; align-items:center; gap:0.65rem;">
                        @if($step1Completed)
                            <div style="width:32px; height:32px; background:#22c55e; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; box-shadow:0 0 0 4px rgba(34, 197, 94, 0.15);">
                                <i class="bi bi-check-lg" style="font-size:0.95rem; -webkit-text-stroke: 1px;"></i>
                            </div>
                            <div style="display:flex; flex-direction:column; line-height:1.2;">
                                <span style="font-size:0.82rem; font-weight:800; color:#22c55e; letter-spacing:0.2px;">1. Under Review</span>
                                <span style="font-size:0.68rem; color:var(--text-muted); font-weight:500;">Review Complete</span>
                            </div>
                        @else
                            <div style="width:32px; height:32px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; box-shadow:0 0 0 4px rgba(0, 102, 204, 0.15);">
                                <i class="bi bi-hourglass-split" style="font-size:0.8rem;"></i>
                            </div>
                            <div style="display:flex; flex-direction:column; line-height:1.2;">
                                <span style="font-size:0.82rem; font-weight:800; color:var(--primary); letter-spacing:0.2px;">1. Under Review</span>
                                <span style="font-size:0.68rem; color:var(--text-muted); font-weight:500;">PIC Reviewing</span>
                            </div>
                        @endif
                    </div>

                    {{-- Connecting line 1 --}}
                    <div style="flex:1; max-width:100px; height:3px; background:{{ $step1Completed ? '#22c55e' : 'var(--border)' }}; border-radius:2px;"></div>

                    {{-- Step 2: Final Status --}}
                    <div style="display:flex; align-items:center; gap:0.65rem; @if(!$step2Active) opacity:0.55; @endif">
                        @if($isApproved)
                            <div style="width:32px; height:32px; background:#22c55e; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; box-shadow:0 0 0 4px rgba(34, 197, 94, 0.15);">
                                <i class="bi bi-check-circle-fill" style="font-size:0.95rem;"></i>
                            </div>
                            <div style="display:flex; flex-direction:column; line-height:1.2;">
                                <span style="font-size:0.82rem; font-weight:800; color:#22c55e; letter-spacing:0.2px;">2. Approved</span>
                                <span style="font-size:0.68rem; color:var(--text-muted); font-weight:500;">Authorized</span>
                            </div>
                        @elseif($isReturned)
                            <div style="width:32px; height:32px; background:#ef4444; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; box-shadow:0 0 0 4px rgba(239, 68, 68, 0.15);">
                                <i class="bi bi-exclamation-circle-fill" style="font-size:0.95rem;"></i>
                            </div>
                            <div style="display:flex; flex-direction:column; line-height:1.2;">
                                <span style="font-size:0.82rem; font-weight:800; color:#ef4444; letter-spacing:0.2px;">2. Returned</span>
                                <span style="font-size:0.68rem; color:var(--text-muted); font-weight:500;">Needs Action</span>
                            </div>
                        @else
                            <div style="width:32px; height:32px; background:#fff; border:2px solid var(--text-muted); color:var(--text-muted); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700;">
                                <i class="bi bi-shield-check" style="font-size:0.85rem;"></i>
                            </div>
                            <div style="display:flex; flex-direction:column; line-height:1.2;">
                                <span style="font-size:0.82rem; font-weight:700; color:var(--text-muted); letter-spacing:0.2px;">2. Final Status</span>
                                <span style="font-size:0.68rem; color:var(--text-muted); font-weight:500;">Upcoming</span>
                            </div>
                        @endif
                    </div>

                </div>

                <div style="display:flex;align-items:stretch;gap:.6rem;margin-bottom:1rem;">
                    {{-- Module card — LEFT --}}
                    <div style="flex:1;display:flex;align-items:center;gap:.6rem;padding:.4rem .9rem;background:var(--secondary-light);border:1.5px solid rgba(11,46,109,.13);border-radius:10px;">
                        <div style="width:28px;height:28px;background:var(--secondary);border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 3px 8px rgba(11,46,109,.18);">
                            <i class="bi bi-grid-3x3-gap-fill" style="color:#fff;font-size:.65rem;"></i>
                        </div>
                        <div style="line-height:1.2;">
                            <div style="font-size:.62rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.06em;opacity:.7;">Module</div>
                            <div style="font-size:.88rem;font-weight:800;color:var(--secondary);font-family:monospace;">{{ $uamRequest->module ?: 'N/A' }}</div>
                        </div>
                    </div>
                    {{-- Period card — RIGHT --}}
                    <div style="flex:1;display:flex;align-items:center;justify-content:flex-end;gap:.6rem;padding:.4rem .9rem;background:#fff5f5;border:1.5px solid rgba(192,57,43,.2);border-radius:10px;">
                        <div style="line-height:1.2;text-align:right;">
                            <div style="font-size:.62rem;font-weight:700;color:#c0392b;text-transform:uppercase;letter-spacing:.06em;">Period</div>
                            <div style="font-size:.88rem;font-weight:800;color:#c0392b;font-family:monospace;">{{ $uamRequest->full_period ?: 'N/A' }}</div>
                        </div>
                        <div style="width:28px;height:28px;background:#c0392b;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 3px 8px rgba(192,57,43,.25);">
                            <i class="bi bi-calendar3" style="color:#fff;font-size:.65rem;"></i>
                        </div>
                    </div>
                </div>

                @endif



                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-8 col-lg-9">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.9rem;"></i>
                            <input type="text" name="search" id="searchInput" value="{{ $search }}"
                                   class="form-control"
                                   style="padding-left:2.6rem;font-size:.9rem;border-radius:10px;border-color:var(--border);"
                                   placeholder="Search by Role (e.g. ZPS-MD-1014-000000-PROJ-CHG)…"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                        <button type="submit" id="searchSubmitBtn" class="btn-primary-custom flex-grow-1"
                                style="padding:.65rem 1rem;font-size:.9rem;background:var(--secondary);border-radius:10px;box-shadow:none;">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        @if($search)
                            <a href="{{ route('access-matrix.sap', array_filter(['request_id' => $requestId ?? null])) }}"
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
        @if(!($isStage2View ?? false))
        <div class="animate-in animate-in-delay-2 mb-4">
            <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">

                {{-- Table Header Bar --}}
                @php $isApprovalView = isset($uamRequest) && $uamRequest && $uamRequest->status === 'Review' && isset($isApproval) && $isApproval; @endphp
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        <div style="width:36px;height:36px;background:var(--secondary-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-shield-lock-fill" style="color:var(--secondary);font-size:.95rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.9rem;font-weight:700;color:var(--secondary);display:flex;align-items:center;gap:.5rem;">
                                UAM Records
                                @if($roles->total() > 0)
                                <span style="background:var(--secondary-light);color:var(--secondary);border-radius:20px;padding:.1rem .55rem;font-size:.67rem;font-weight:700;letter-spacing:.02em;">{{ $roles->total() }}</span>
                                @endif
                            </div>
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

                    {{-- Add Role button (top-right, only when editable) --}}
                    @if((Auth::user()->isAdmin() || Auth::user()->isPicAo()) && empty($isApproval) && (!isset($uamRequest) || !$uamRequest || (in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return']))))
                        <a href="{{ route('access-matrix.create', array_filter(['request_id' => $requestId ?? null])) }}"
                           id="addRoleBtn"
                           style="display:inline-flex;align-items:center;gap:.4rem;background:var(--secondary);color:#fff;border:none;border-radius:8px;padding:.42rem 1rem;font-size:.8rem;font-weight:700;text-decoration:none;white-space:nowrap;box-shadow:0 2px 8px rgba(11,46,109,.18);transition:all .18s;"
                           onmouseenter="this.style.background='#0a2355';this.style.transform='translateY(-1px)';"
                           onmouseleave="this.style.background='var(--secondary)';this.style.transform='none';">
                            <i class="bi bi-plus-lg" style="font-size:.8rem;"></i> Add Role
                        </a>
                    @endif

                </div>

                {{-- Table --}}
                <div style="overflow-x:auto; padding-bottom: 6rem; min-height: 200px;">
                    <table class="uam-table" style="width:100%;border-collapse:collapse;font-size:.82rem;table-layout:fixed;">
                        <colgroup>
                            <col style="width:48px;">         {{-- # --}}
                            <col style="width:240px;">        {{-- Role --}}
                            <col style="min-width:0;">        {{-- Description Role (flex) --}}
                            <col style="width:100px;">        {{-- TCODE --}}
                            <col style="width:140px;">        {{-- Access --}}
                            <col style="width:100px;">        {{-- Actions --}}
                            <col style="width:56px;">         {{-- Expand arrow --}}
                        </colgroup>
                        <thead>
                            <tr style="background:var(--secondary-light);">
                                @php
                                    $thStyle = "padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid var(--border);vertical-align:middle;overflow:hidden;";
                                @endphp
                                <th style="{{ $thStyle }}">#</th>
                                <th style="{{ $thStyle }}">Role</th>
                                <th style="{{ $thStyle }}">Description Role</th>
                                <th style="{{ $thStyle }}">TCODE</th>
                                <th style="{{ $thStyle }}">Access</th>
                                <th style="{{ $thStyle }}">Actions</th>
                                <th style="{{ $thStyle }} border-left:1px solid #e5e7eb; text-align:center; padding-left:0; padding-right:0;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $i => $roleData)
                                @php
                                    $roleRecords = $recordsMap[$roleData->role] ?? collect();
                                    $firstRec = $roleRecords->first();
                                    $rowId = 'row-' . md5($roleData->role);
                                    $returnedCount = $roleRecords->where('status', 'Return')->count();
                                    
                                    // A role is considered newly added if its first record is 'Added'
                                    $isNewRole = $firstRec && isset($firstRec->change_type) && $firstRec->change_type === 'Added';
                                @endphp
                                <tr style="border-bottom:1px solid var(--border);transition:background var(--transition);"
                                    onmouseenter="this.style.background='var(--secondary-light)'"
                                    onmouseleave="this.style.background=''">
                                    <td style="padding:.7rem 1rem;color:var(--text-muted);font-size:.78rem;white-space:nowrap;vertical-align:middle;overflow:hidden;">
                                        <span>{{ $roles->firstItem() + $i }}</span>
                                    </td>
                                    <td style="padding:.7rem 1rem;overflow:hidden;vertical-align:middle;">
                                        <span style="font-family:monospace;background:#f1f5f9;padding:.2rem .45rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);font-weight:700;color:var(--secondary);display:inline-block;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $roleData->role ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="padding:.7rem 1rem;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle;"
                                        title="{{ $roleData->description_role }}">
                                        {{ $roleData->description_role ?? '—' }}
                                    </td>
                                    <td style="padding:.7rem 1rem;overflow:hidden;vertical-align:middle;">
                                        <span style="color:var(--text-muted);">—</span>
                                    </td>
                                    <td style="padding:.7rem 1rem;overflow:hidden;vertical-align:middle;"></td>
                                    <td style="padding:.7rem 1rem;overflow:hidden;vertical-align:middle;"></td>
                                    {{-- Expand arrow column --}}
                                    <td style="padding:.7rem 0;border-left:1px solid #e5e7eb;text-align:center;vertical-align:middle;">
                                        <button type="button" onclick="toggleSubRows('{{ $rowId }}')" id="btn-toggle-{{ $rowId }}"
                                            style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;background:transparent;color:var(--text-muted);border:none;cursor:pointer;transition:all var(--transition);"
                                            onmouseenter="this.style.color='var(--secondary)';"
                                            onmouseleave="this.style.color='var(--text-muted)';">
                                            <i class="bi bi-chevron-down" id="icon-sub-{{ $rowId }}" style="transition:transform .2s;font-size:1rem;"></i>
                                            @if($returnedCount > 0)
                                                <span style="position:absolute; top:-6px; right:-8px; background:#ef4444; color:#fff; font-size:0.65rem; font-weight:700; padding:0.15rem 0.35rem; border-radius:12px; border:2px solid #fff; line-height:1; transform: scale(0.9);" title="{{ $returnedCount }} returned item(s)">{{ $returnedCount }}</span>
                                            @endif
                                        </button>
                                    </td>
                                </tr>

                                {{-- EXPANDED TCODE ROWS — rendered for all records (single or multiple) --}}
                                @foreach($roleRecords as $rec)
                                    @php
                                        $isNewRec = isset($rec->change_type) && $rec->change_type === 'Added';
                                    @endphp
                                        <tr class="subrow-{{ $rowId }}" style="display:none; border-bottom:1px solid transparent; background: #f1f5f9; transition:background var(--transition), border-color 300ms ease-in-out;"
                                            onmouseenter="this.style.background='var(--secondary-light)'"
                                            onmouseleave="this.style.background='#f1f5f9'">
                                            
                                            {{-- # column --}}
                                            <td style="padding:0;border:none;vertical-align:middle;">
                                                <div class="anim-wrapper" style="max-height:0;opacity:0;overflow:hidden;transition:max-height 300ms ease-in-out,opacity 300ms ease-in-out;">
                                                    <div style="padding:.7rem 1rem;display:flex;align-items:center;">
                                                        <div style="width:12px;height:12px;border-left:1.5px solid var(--border);border-bottom:1.5px solid var(--border);margin-left:auto;"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Role column --}}
                                            <td style="padding:0;overflow:hidden;border:none;vertical-align:middle;">
                                                <div class="anim-wrapper" style="max-height:0;opacity:0;overflow:hidden;transition:max-height 300ms ease-in-out,opacity 300ms ease-in-out;">
                                                    <div style="padding:.7rem 1rem;opacity:0.6;">
                                                        <span style="font-family:monospace;background:#f1f5f9;padding:.2rem .45rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);font-weight:700;color:var(--secondary);display:inline-block;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                            {{ $roleData->role ?? '—' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Description Role column --}}
                                            <td style="padding:0;overflow:hidden;border:none;vertical-align:middle;">
                                                <div class="anim-wrapper" style="max-height:0;opacity:0;overflow:hidden;transition:max-height 300ms ease-in-out,opacity 300ms ease-in-out;">
                                                    <div style="padding:.7rem 1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;opacity:0.6;" title="{{ $roleData->description_role }}">
                                                        {{ $roleData->description_role ?? '—' }}
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- TCODE column --}}
                                            <td style="padding:0;overflow:hidden;border:none;vertical-align:middle;">
                                                <div class="anim-wrapper" style="max-height:0;opacity:0;overflow:hidden;transition:max-height 300ms ease-in-out,opacity 300ms ease-in-out;">
                                                    <div style="padding:.7rem 1rem;">
                                                        <span style="font-family:monospace;background:#eff6ff;padding:.2rem .5rem;border-radius:4px;font-size:.78rem;border:1px solid #bfdbfe;font-weight:700;color:#1d4ed8;display:inline-block;">
                                                            {{ $rec->tcode ?: '—' }}
                                                        </span>
                                                        @if(isset($rec->change_type) && $rec->change_type !== 'Unchanged')
                                                            @php
                                                                $badgeColors = [
                                                                    'Added'    => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
                                                                    'Modified' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'border' => '#fef08a'],
                                                                    'Deleted'  => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fecaca'],
                                                                ];
                                                                $c = $badgeColors[$rec->change_type] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#e5e7eb'];
                                                            @endphp
                                                            <span style="margin-left:.35rem; font-size:.65rem; font-weight:700; padding:.15rem .45rem; border-radius:12px; background:{{ $c['bg'] }}; color:{{ $c['text'] }}; border:1px solid {{ $c['border'] }}; vertical-align:middle;">
                                                                {{ $rec->change_type }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Access column --}}
                                            <td style="padding:0;overflow:hidden;border:none;vertical-align:middle;">
                                                <div class="anim-wrapper" style="max-height:0;opacity:0;overflow:hidden;transition:max-height 300ms ease-in-out,opacity 300ms ease-in-out;">
                                                    <div style="padding:.7rem 1rem;">
                                                        <button type="button" class="view-access-btn-{{ $rowId }}-{{ $rec->id }}" onclick="openAccessModal(this)"
                                                            data-row-id="{{ $rowId }}-{{ $rec->id }}"
                                                            data-role="{{ htmlspecialchars($roleData->role ?? '—') }}"
                                                            data-tcode="{{ htmlspecialchars($rec->tcode ?? '') }}"
                                                            data-request-id="{{ $requestId ?? '' }}"
                                                            style="display:inline-flex;align-items:center;gap:.4rem;background:var(--secondary-light);color:var(--secondary);border:1px solid var(--border);border-radius:6px;padding:.3rem .75rem;font-size:.72rem;font-weight:600;cursor:pointer;transition:all var(--transition);white-space:nowrap;"
                                                            onmouseenter="this.style.background='var(--secondary)';this.style.color='#fff';"
                                                            onmouseleave="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';">
                                                            <i class="bi bi-shield-lock"></i> View Access
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Actions column --}}
                                            <td style="padding:0;overflow:hidden;border:none;vertical-align:middle;">
                                                <div class="anim-wrapper" style="max-height:0;opacity:0;overflow:hidden;transition:max-height 300ms ease-in-out,opacity 300ms ease-in-out;">
                                                    <div style="padding:.7rem 1rem;">
                                                        @if($isApprovalView ?? false)
                                                            {{-- ── Approval view: radio buttons ── --}}
                                                            <div style="display:flex;flex-direction:column;align-items:flex-start;gap:.3rem;">
                                                                <label style="display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;white-space:nowrap;">
                                                                    <input type="radio" form="approvalDecisionForm" name="decisions[{{ $rec->id }}]" value="Approved" required style="accent-color:#22c55e;width:14px;height:14px;cursor:pointer;" onchange="validateDecisionForm(); autoSaveDecision({{ $rec->id }}, this.value);" {{ $rec->status === 'Approved' ? 'checked' : '' }}>
                                                                    <span style="font-size:.75rem;color:#15803d;font-weight:700;">Approve</span>
                                                                </label>
                                                                <label style="display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;white-space:nowrap;">
                                                                    <input type="radio" form="approvalDecisionForm" name="decisions[{{ $rec->id }}]" value="Return" style="accent-color:#ef4444;width:14px;height:14px;cursor:pointer;" onchange="validateDecisionForm(); autoSaveDecision({{ $rec->id }}, this.value);" {{ $rec->status === 'Return' ? 'checked' : '' }}>
                                                                    <span style="font-size:.75rem;color:#c0392b;font-weight:700;">Return</span>
                                                                </label>
                                                            </div>
                                                        @else
                                                            @php
                                                                $recStatus     = $rec->status ?? 'Pending';
                                                                $reqStatus     = $uamRequest->status ?? '';
                                                                $hasDecision   = $uamRequest && !in_array($reqStatus, ['Draft', 'Need Revision']);
                                                                $isEditable    = (!$uamRequest
                                                                    || in_array($reqStatus, ['Draft', 'Need Revision'])
                                                                    || ($reqStatus === 'Return' && $recStatus === 'Return'))
                                                                    && empty($isApproval);
                                                                $statusColor = match($recStatus) {
                                                                    'Approved' => '#15803d',
                                                                    'Return'   => '#b91c1c',
                                                                    default    => '#92400e',
                                                                };
                                                                $statusIcon  = match($recStatus) {
                                                                    'Approved' => 'bi-check-circle-fill',
                                                                    'Return'   => 'bi-arrow-counterclockwise',
                                                                    default    => 'bi-hourglass-split',
                                                                };
                                                                $statusLabel = match($recStatus) {
                                                                    'Approved' => 'Approved',
                                                                    'Return'   => 'Returned',
                                                                    default    => 'Pending Review',
                                                                };
                                                            @endphp
                                                            <div style="display:flex;flex-direction:column;align-items:flex-start;gap:.4rem;">
                                                                {{-- Status indicator (shown whenever a decision has been made) --}}
                                                                @if($hasDecision)
                                                                <span style="display:inline-flex;align-items:center;gap:.3rem;color:{{ $statusColor }};font-size:.75rem;font-weight:600;white-space:nowrap;"
                                                                      title="Decision for TCODE: {{ $rec->tcode }}">
                                                                    <i class="bi {{ $statusIcon }}"></i>
                                                                    {{ $statusLabel }}
                                                                </span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Expand arrow column (Action Menu for sub-rows) --}}
                                            <td style="padding:0;border:none;border-left:1px solid #e5e7eb;vertical-align:middle;text-align:center;">
                                                <div class="anim-wrapper" style="max-height:0;opacity:0;overflow:hidden;transition:max-height 300ms ease-in-out,opacity 300ms ease-in-out;">
                                                    <div style="padding:.7rem 0;display:flex;justify-content:center;align-items:center;">
                                                        @if((Auth::user()->isAdmin() || Auth::user()->isPicAo()) && empty($isApproval) && (!isset($uamRequest) || !$uamRequest || (in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return']))))
                                                            <div class="dropdown">
                                                                <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration:none;">
                                                                    <i class="bi bi-three-dots-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:10px;font-size:0.8rem;">
                                                                    <li>
                                                                        <button type="button" class="dropdown-item" style="display:flex;align-items:center;gap:0.4rem;padding:0.4rem 1rem;color:var(--primary);"
                                                                                onclick="openAddTcodeModal('{{ $roleData->role }}')">
                                                                            <i class="bi bi-plus-circle"></i> Add TCODE
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        <form action="{{ route('access-matrix.destroy-role', ['role' => $roleData->role]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Role and ALL its associated TCODEs?');">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <input type="hidden" name="request_id" value="{{ $uamRequest ? $uamRequest->id : '' }}">
                                                                            <button type="submit" class="dropdown-item text-danger" style="display:flex;align-items:center;gap:0.4rem;padding:0.4rem 1rem;">
                                                                                <i class="bi bi-trash"></i> Delete Role
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                    <li>
                                                                        <form action="{{ route('access-matrix.destroy', $rec->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this specific TCODE?');">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="dropdown-item text-danger" style="display:flex;align-items:center;gap:0.4rem;padding:0.4rem 1rem;">
                                                                                <i class="bi bi-x-circle"></i> Delete TCODE
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" style="padding:3.5rem 1rem;text-align:center;">
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
                                                <i class="bi bi-plus-lg"></i> Add New Role
                                            </a>
                                        @else
                                            {{-- Initial state: no data at all --}}
                                            <div style="width:64px;height:64px;background:var(--secondary-light);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                                <i class="bi bi-folder2-open" style="font-size:1.6rem;color:var(--secondary);"></i>
                                            </div>
                                            <h3 style="font-size:1rem;font-weight:700;color:var(--secondary);margin-bottom:.3rem;">No records available</h3>
                                            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem;">
                                                There are currently no records for this request. Use <strong>Add Role</strong> to get started, or go back to <a href="{{ $moduleRoute }}" style="color:var(--secondary);">{{ $moduleName }}</a>.
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

                {{-- Overall Decision + Final Submit (Stage 2) --}}
                @if($isStage2 && isset($uamRequest) && $uamRequest->status === 'Stage 2')
                <div id="overallDecisionContainer" style="padding:1.25rem 1.25rem;border-top:1.5px solid var(--border);background:linear-gradient(135deg, #f0f4ff 0%, #f8fafc 100%);">
                    <form action="{{ route('access-matrix.final-decide', $uamRequest->id) }}" method="POST" id="finalDecisionForm">
                        @csrf
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
                            <div style="flex:1;min-width:240px;">
                                <div style="font-size:.82rem;font-weight:800;color:var(--secondary);margin-bottom:.35rem;display:flex;align-items:center;gap:.4rem;">
                                    <i class="bi bi-clipboard2-check" style="font-size:.9rem;"></i> Final Approval Decision
                                </div>
                                <div style="font-size:.72rem;color:var(--text-muted);line-height:1.4;margin-bottom:.6rem;" id="stage2Summary">
                                    TCODE review completed by {{ $uamRequest->requester->name ?? 'Reviewer' }}. Please select your overall decision.
                                </div>
                                <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
                                    <label style="display:inline-flex;align-items:center;gap:.4rem;cursor:pointer;margin-right:1rem;">
                                        <input type="radio" name="overall_decision" value="Approved" required style="accent-color:#15803d;width:16px;height:16px;cursor:pointer;" onchange="validateFinalDecisionForm()">
                                        <span style="font-size:.85rem;color:#15803d;font-weight:700;">Overall Approve</span>
                                    </label>
                                    <label style="display:inline-flex;align-items:center;gap:.4rem;cursor:pointer;">
                                        <input type="radio" name="overall_decision" value="Return" required style="accent-color:#ef4444;width:16px;height:16px;cursor:pointer;" onchange="validateFinalDecisionForm()">
                                        <span style="font-size:.85rem;color:#c0392b;font-weight:700;">Overall Return</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div style="flex:2;min-width:200px;display:flex;flex-direction:column;gap:.2rem;margin-right:1rem;">
                                <label for="finalComment" style="font-size:.72rem;font-weight:700;color:var(--secondary);display:flex;align-items:center;gap:.3rem;">
                                    Final Approver Comment
                                    <div style="position:relative;width:0;height:0;display:flex;align-items:center;">
                                        <span id="finalCommentHint" style="color:#ef4444;font-weight:500;font-size:.68rem;position:absolute;left:0;white-space:nowrap;">— required, minimum 3 words</span>
                                    </div>
                                </label>
                                <textarea name="approver_comment" id="finalComment" rows="2"
                                          placeholder="Add notes or approval/revision instructions…"
                                          style="flex:1;width:100%;border:1.5px solid var(--border);border-radius:8px;padding:.4rem .7rem;font-size:.8rem;color:var(--text);resize:none;transition:border-color .2s;outline:none;font-family:inherit;min-height:58px;max-height:90px;"
                                          onfocus="this.style.borderColor='var(--secondary)'"
                                          onblur="this.style.borderColor='var(--border)'"
                                          oninput="validateFinalDecisionForm()"
                                          required></textarea>
                            </div>

                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.35rem;">
                                <button type="submit" id="finalSubmitBtn"
                                        disabled
                                        style="display:inline-flex;align-items:center;gap:.35rem;background:#15803d;color:#fff;border:none;border-radius:8px;padding:.5rem 1.25rem;font-size:.8rem;font-weight:700;cursor:not-allowed;white-space:nowrap;box-shadow:0 2px 6px rgba(21,128,61,.2);transition:all .18s;letter-spacing:.1px;opacity:.45;"
                                        onmouseenter="if(!this.disabled){this.style.background='#166534';this.style.transform='translateY(-1px)';}"
                                        onmouseleave="this.style.background='#15803d';this.style.transform='none';">
                                    <i class="bi bi-send-fill" style="font-size:.7rem;"></i> Submit Final Decision
                                </button>
                            </div>
                        </div>
                    </form>
                    <script>
                        function validateFinalDecisionForm() {
                            const countW = typeof countWords === 'function' ? countWords : function(str) {
                                return str.trim().split(/\s+/).filter(function(w){ return w.length > 0; }).length;
                            };
                            
                            const radioChecked = document.querySelector('input[name="overall_decision"]:checked') !== null;
                            const commentField = document.getElementById('finalComment');
                            const commentHint = document.getElementById('finalCommentHint');
                            const submitBtn = document.getElementById('finalSubmitBtn');
                            
                            const words = countW(commentField.value);
                            const valid = radioChecked && words >= 3;
                            
                            submitBtn.disabled = !valid;
                            submitBtn.style.opacity = valid ? '1' : '.45';
                            submitBtn.style.cursor = valid ? 'pointer' : 'not-allowed';
                            
                            if (words >= 3) {
                                commentHint.textContent = '— ✓ Valid';
                                commentHint.style.color = '#15803d';
                                commentField.style.borderColor = 'var(--border)';
                            } else {
                                commentHint.textContent = '— required, minimum 3 words (' + words + '/3)';
                                commentHint.style.color = '#ef4444';
                                commentField.style.borderColor = '#ef4444';
                            }
                        }
                        
                        document.addEventListener('DOMContentLoaded', validateFinalDecisionForm);
                    </script>
                </div>
                @endif

            </div>
        </div>

        {{-- ── Approver Comment Banner ── --}}
        @if(isset($uamRequest) && $uamRequest && !empty($uamRequest->approver_comment))
        @php
            $commentStatus = $uamRequest->status ?? '';
            $isApproved    = $commentStatus === 'Approved';
            $commentBorder = $isApproved ? '#bbf7d0' : '#fca5a5';
            $commentBg     = $isApproved ? '#f0fdf4' : '#fff7f7';
            $commentIcon   = $isApproved ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
            $commentColor  = $isApproved ? '#15803d' : '#b91c1c';
            $commentLabel  = $isApproved ? 'Approver Note' : 'Approver Feedback — Action Required';
        @endphp
        <div class="animate-in mb-4" style="padding:.9rem 1.1rem;background:{{ $commentBg }};border:1.5px solid {{ $commentBorder }};border-radius:14px;display:flex;align-items:flex-start;gap:.75rem;box-shadow:0 2px 10px rgba(0,0,0,.04);">
            <div style="width:32px;height:32px;background:{{ $commentColor }};border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;box-shadow:0 3px 8px rgba(0,0,0,.12);">
                <i class="bi {{ $commentIcon }}" style="color:#fff;font-size:.85rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.72rem;font-weight:700;color:{{ $commentColor }};text-transform:uppercase;letter-spacing:.06em;margin-bottom:.35rem;">
                    {{ $commentLabel }}
                </div>
                <div style="font-size:.84rem;color:#374151;line-height:1.6;white-space:pre-wrap;word-break:break-word;">{{ $uamRequest->approver_comment }}</div>
            </div>
        </div>
        @endif

        @endif

        {{-- ── Approval History (Audit Trail) ── --}}
        @if(isset($uamRequest) && $uamRequest && $uamRequest->approvalHistories->count() > 0)
        <div class="animate-in mb-4" style="background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.02);">
            <div style="padding:.75rem 1.25rem;background:#f3f4f6;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:.6rem;">
                <div style="width:28px;height:28px;background:#d1d5db;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-clock-history" style="color:#4b5563;font-size:.8rem;"></i>
                </div>
                <div>
                    <div style="font-size:.82rem;font-weight:700;color:#374151;line-height:1.2;">Approval History</div>
                    <div style="font-size:.68rem;color:#6b7280;">Audit trail of past decisions</div>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;display:flex;flex-direction:column;gap:1rem;">
                @foreach($uamRequest->approvalHistories as $history)
                    @php
                        $isHistApprove = $history->status === 'Approved';
                        $histColor     = $isHistApprove ? '#15803d' : '#b91c1c';
                        $histIcon      = $isHistApprove ? 'bi-check-circle-fill' : 'bi-arrow-counterclockwise';
                        $histBg        = $isHistApprove ? '#f0fdf4' : '#fef2f2';
                    @endphp
                    <div style="display:flex;gap:1rem;opacity:0.85;">
                        <div style="width:36px;height:36px;background:{{ $histBg }};border:1px solid {{ $histColor }}33;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
                            <i class="bi {{ $histIcon }}" style="color:{{ $histColor }};font-size:.9rem;"></i>
                        </div>
                        <div style="flex:1;min-width:0;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;padding:.75rem 1rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;flex-wrap:wrap;gap:.5rem;">
                                <div style="display:flex;align-items:center;gap:.5rem;">
                                    <span style="font-size:.78rem;font-weight:700;color:{{ $histColor }};text-transform:uppercase;letter-spacing:.05em;">{{ $history->status }}</span>
                                    <span style="color:#9ca3af;font-size:.7rem;">•</span>
                                    <span style="font-size:.75rem;font-weight:600;color:#4b5563;"><i class="bi bi-person-fill" style="margin-right:.2rem;"></i>{{ $history->approver_name ?: 'System' }}</span>
                                </div>
                                <div style="font-size:.7rem;color:#9ca3af;font-weight:500;">
                                    {{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <div style="font-size:.82rem;color:#4b5563;line-height:1.5;white-space:pre-wrap;word-break:break-word;">{{ $history->comment ?: 'No comment provided.' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        {{-- Submit Action (for Requester) --}}
        @if((Auth::user()->isAdmin() || Auth::user()->isPicAo()) && isset($uamRequest) && $uamRequest && in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return']) && empty($isApproval))
            <div class="d-flex justify-content-end mt-4 animate-in animate-in-delay-3" style="margin-bottom: 2rem;">
                <form action="{{ route('access-matrix.submit', $uamRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to submit this request for review? You will not be able to edit records after submitting.');" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary" id="submitApprovalBtn"
                            style="background:#0066cc;border:none;border-radius:10px;padding:.75rem 2.25rem;font-weight:700;font-size:.88rem;box-shadow:0 4px 14px rgba(0,102,204,.25);letter-spacing:.3px;display:flex;align-items:center;gap:.5rem;cursor:pointer;transition:all var(--transition);"
                            onmouseenter="this.style.background='#0052a3';this.style.transform='translateY(-1px)';"
                            onmouseleave="this.style.background='#0066cc';this.style.transform='none';">
                        <i class="bi bi-send-fill" style="font-size:.85rem;"></i>
                        Submit Request
                    </button>
                </form>
            </div>
        @endif

        {{-- Approval Decision (for Approver when status is Review) --}}
        @if($isApprovalView)
            <div class="animate-in animate-in-delay-3 mt-4" style="margin-bottom:2rem;">
                <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">
                    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border);background:var(--secondary-light);display:flex;align-items:center;gap:.55rem;">
                        <div style="width:30px;height:30px;background:var(--secondary);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-patch-check-fill" style="color:#fff;font-size:.85rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:800;color:var(--secondary);line-height:1.1;">Approval Decision</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">Review the UAM records above, then submit your decision</div>
                        </div>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('access-matrix.approve-decision', $uamRequest->id) }}" method="POST" id="approvalDecisionForm" style="padding:.85rem 1.1rem;">
                        @csrf
                        @if($errors->any())
                            <div style="background:#fde8e9;border-left:4px solid #c0392b;border-radius:7px;padding:.45rem .8rem;margin-bottom:.75rem;font-size:.78rem;color:#7b0d0f;display:flex;align-items:center;gap:.4rem;">
                                <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div style="display:flex;align-items:stretch;gap:.85rem;flex-wrap:wrap;">
                            <div style="flex:1;min-width:280px;display:flex;flex-direction:column;justify-content:center;">
                                <div style="font-size:.8rem;font-weight:700;color:var(--secondary);margin-bottom:.2rem;">Submit Approval</div>
                                <div style="font-size:.7rem;color:var(--text-muted);line-height:1.4;">
                                    Please make sure you have selected <strong>Approve</strong> or <strong>Return</strong> for every TCODE record in the table above before submitting your decision.
                                </div>
                            </div>
                            
                            {{-- General Comment --}}
                            <div style="flex:2;min-width:200px;display:flex;flex-direction:column;gap:.2rem;">
                                <label for="approverComment" style="font-size:.72rem;font-weight:700;color:var(--secondary);display:flex;align-items:center;gap:.3rem;">
                                    General Comment
                                    <div style="position:relative;width:0;height:0;display:flex;align-items:center;">
                                        <span id="commentHint" style="color:#ef4444;font-weight:500;font-size:.68rem;position:absolute;left:0;white-space:nowrap;">— required, minimum 3 words</span>
                                    </div>
                                </label>
                                <textarea name="approver_comment" id="approverComment" rows="2"
                                          placeholder="Add notes or approval/revision instructions…"
                                          style="flex:1;width:100%;border:1.5px solid var(--border);border-radius:8px;padding:.4rem .7rem;font-size:.8rem;color:var(--text);resize:none;transition:border-color .2s;outline:none;font-family:inherit;min-height:58px;max-height:90px;"
                                          onfocus="this.style.borderColor='var(--secondary)'"
                                          onblur="this.style.borderColor='var(--border)'"
                                          oninput="validateDecisionForm(); debouncedAutoSaveComment(this.value);" required>{{ old('approver_comment', $uamRequest->approver_comment) }}</textarea>
                            </div>

                            <div style="display:flex;flex-direction:column;gap:.35rem;justify-content:flex-end;align-self:flex-end;">
                                <button type="submit" id="submitDecisionBtn"
                                        disabled
                                        style="display:inline-flex;align-items:center;gap:.35rem;background:var(--secondary);color:#fff;border:none;border-radius:8px;padding:.42rem 1rem;font-size:.78rem;font-weight:700;cursor:not-allowed;white-space:nowrap;box-shadow:0 2px 6px rgba(11,46,109,.2);transition:all .18s;letter-spacing:.1px;opacity:.45;"
                                        onmouseenter="if(!this.disabled){this.style.background='#0a2355';this.style.transform='translateY(-1px)';}"
                                        onmouseleave="this.style.background='var(--secondary)';this.style.transform='none';">
                                    <i class="bi bi-check2-circle" style="font-size:.72rem;"></i> Complete TCODE Review
                                </button>
                                <a href="{{ route('access-matrix.approval.sap') }}"
                                   style="display:inline-flex;align-items:center;justify-content:center;gap:.25rem;padding:.38rem .8rem;border:1.5px solid var(--border);border-radius:8px;font-size:.73rem;font-weight:600;color:var(--text-muted);text-decoration:none;transition:all .18s;white-space:nowrap;"
                                   onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';"
                                   onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';">
                                    <i class="bi bi-arrow-left" style="font-size:.65rem;"></i> Back to List
                                </a>
                            </div>

                        </div>

                    </form>
                </div>
            </div>
            <script>
            function countWords(str) {
                return str.trim().split(/\s+/).filter(function(w){ return w.length > 0; }).length;
            }

            function validateDecisionForm() {
                const allRadios = document.querySelectorAll('input[type="radio"][name^="decisions"]');
                const uniqueNames = new Set([...allRadios].map(r => r.name));
                const allSelected = uniqueNames.size > 0 && [...uniqueNames].every(name => document.querySelector(`input[name="${name}"]:checked`));

                const commentField     = document.getElementById('approverComment');
                const comment          = commentField.value;
                const submitBtn        = document.getElementById('submitDecisionBtn');
                const commentHint      = document.getElementById('commentHint');
                const words            = countWords(comment);

                // "Complete TCODE Review" enabled when all TCODEs decided + comment >= 3 words
                const valid = allSelected && words >= 3;
                submitBtn.disabled = !valid;
                submitBtn.style.opacity = valid ? '1' : '.45';
                submitBtn.style.cursor  = valid ? 'pointer' : 'not-allowed';

                // Hint colour: green when satisfied, red otherwise
                if (words >= 3) {
                    commentHint.textContent = '— ✓ Valid';
                    commentHint.style.color = '#15803d';
                    commentField.style.borderColor = 'var(--border)';
                } else {
                    commentHint.textContent = '— required, minimum 3 words (' + words + '/' + 3 + ')';
                    commentHint.style.color = '#ef4444';
                    commentField.style.borderColor = '#ef4444';
                }
            }


            // Run once on load so button state matches any pre-filled values
            document.addEventListener('DOMContentLoaded', validateDecisionForm);
            </script>
        @endif



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

                {{-- Empty / Error state --}}
                <div id="modalError" style="display:none;text-align:center;padding:3rem 1.5rem;">
                    <div style="width:48px;height:48px;background:#fef2f2;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;color:#ef4444;border:1.5px solid #fee2e2;">
                        <i class="bi bi-exclamation-triangle" style="font-size:1.3rem;"></i>
                    </div>
                    <h4 style="font-size:.95rem;font-weight:700;color:var(--secondary);margin-bottom:.3rem;" id="modalErrorTitle">No access data found</h4>
                    <p style="font-size:.82rem;color:var(--text-muted);margin:0;max-width:320px;margin-inline:auto;" id="modalErrorMsg">
                        No application owners or matrix data is registered for this role and TCode.
                    </p>
                </div>

                {{-- Content --}}
                <div id="modalContentWrapper" style="display:none;">

                    {{-- ── Cascading selects: BPO → UNIT ── --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">

                        {{-- BPO dropdown — LEFT (primary selection) --}}
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

                        {{-- UNIT — RIGHT (dropdown based on BPO) --}}
                        <div>
                            <label for="modalUnitSelect"
                                style="display:block;font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.4rem;">Unit</label>
                            <div style="position:relative;">
                                <select id="modalUnitSelect"
                                    style="width:100%;padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.85rem;font-weight:600;color:var(--secondary);background:#fff;appearance:none;cursor:pointer;transition:border-color var(--transition);outline:none;"
                                    onfocus="this.style.borderColor='var(--secondary)'"
                                    onblur="this.style.borderColor='var(--border)'">
                                    <option value="">— select BPO first —</option>
                                </select>
                                <i class="bi bi-chevron-down" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-muted);font-size:.75rem;"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Application Owners panel --}}
                    <div style="border:1.5px solid #bbf7d0;border-radius:14px;overflow:hidden;margin-bottom:1.25rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1rem;background:#f0fdf4;border-bottom:1px solid #bbf7d0;">
                            <div style="display:flex;align-items:center;gap:.45rem;">
                                <i class="bi bi-people-fill" style="color:#166534;font-size:.9rem;"></i>
                                <span style="font-size:.72rem;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:.5px;">User Access Matrix</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <span id="modalOwnerCount" style="font-size:.7rem;font-weight:700;background:#166534;color:#fff;border-radius:20px;padding:.1rem .55rem;"></span>
                                {{-- Edit toggle --}}
                                @if((Auth::user()->isAdmin() || Auth::user()->isPicAo()) && (!$uamRequest || (in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return']))) && empty($isApproval))
                                <button id="editOwnersBtn" type="button" onclick="toggleEditOwners()"
                                    style="font-size:.72rem;font-weight:700;padding:.2rem .65rem;border-radius:20px;border:1.5px solid #166534;background:#fff;color:#166534;cursor:pointer;transition:all .15s;">
                                    <i class="bi bi-pencil-fill me-1"></i>Edit
                                </button>
                                @endif
                            </div>
                        </div>
                        <div id="modalOwnerScroll" style="max-height:260px;overflow-y:auto;padding:1rem;">
                            <div id="modalOwner" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.55rem;"></div>
                        </div>
                        {{-- Add owner row (shown in edit mode) --}}
                        <div id="addOwnerRow" style="display:none;padding:.65rem 1rem;border-top:1px solid #bbf7d0;background:#f8fffe;">
                            <div style="display:flex;gap:.5rem;align-items:center;">
                                <input id="newOwnerInput" type="text" placeholder="Type owner name and press Add…"
                                    style="flex:1;padding:.4rem .75rem;border:1.5px solid #bbf7d0;border-radius:8px;font-size:.82rem;outline:none;"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault();addOwner();}">
                                <button type="button" onclick="addOwner()"
                                    style="padding:.4rem .9rem;background:#166534;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;white-space:nowrap;">
                                    <i class="bi bi-plus-lg me-1"></i>Add
                                </button>
                            </div>
                        </div>
                        {{-- Save / Cancel row (shown in edit mode) --}}
                        <div id="saveOwnerRow" style="display:none;padding:.6rem 1rem;border-top:1px solid #bbf7d0;background:#f0fdf4;display:none;justify-content:flex-end;gap:.5rem;">
                            <button type="button" onclick="cancelEditOwners()"
                                style="padding:.35rem .9rem;border:1.5px solid var(--border);border-radius:8px;font-size:.82rem;font-weight:600;color:var(--text-muted);background:#fff;cursor:pointer;">Cancel</button>
                            <button type="button" onclick="saveOwners()"
                                style="padding:.35rem 1rem;background:#166534;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;">
                                <i class="bi bi-check-lg me-1"></i>Save
                            </button>
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

    {{-- Add TCODE Modal --}}
    <div class="modal fade" id="addTcodeModal" tabindex="-1" aria-labelledby="addTcodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border:none;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.1);">
                <form id="addTcodeForm" method="POST" action="">
                    @csrf
                    <div class="modal-header" style="background:#f8f9fa;border-bottom:1px solid #e5e7eb;border-radius:12px 12px 0 0;padding:1rem 1.5rem;">
                        <h5 class="modal-title" id="addTcodeModalLabel" style="font-weight:700;color:var(--secondary);font-size:1.05rem;">
                            <i class="bi bi-plus-circle me-2"></i>Add TCODE
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding:1.5rem;">
                        <div class="mb-3">
                            <label for="addTcodeRole" class="form-label fw-bold">Role</label>
                            <input type="text" id="addTcodeRole" class="form-control" readonly style="background:#f3f4f6;font-family:monospace;font-weight:600;">
                        </div>
                        <div class="mb-3">
                            <label for="addTcodeCode" class="form-label fw-bold">TCODE</label>
                            <input type="text" id="addTcodeCode" name="tcode" class="form-control" placeholder="e.g. SU01" required>
                            <small class="text-muted" style="font-size:0.75rem;">Comma separated for multiple</small>
                        </div>
                    </div>
                    <div class="modal-footer" style="background:#f8f9fa;border-top:1px solid #e5e7eb;border-radius:0 0 12px 12px;">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal" style="font-weight:600;">Cancel</button>
                        <button type="submit" class="btn-primary-custom" style="padding:.5rem 1.5rem;font-weight:700;">
                            <i class="bi bi-save me-1"></i> Save TCODE
                        </button>
                    </div>
                </form>
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
    // Profile Dropdown logic removed since it is now natively handled by Bootstrap.

    document.getElementById('logoutForm').addEventListener('submit', function () {
        const btn = document.getElementById('logoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out…';
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
    let _hierarchy      = [];   // [{unit, bpos:[{bpo, owners:[]}]}]
    let _currentOwners  = [];   // owners currently shown (mutable in edit mode)
    let _editMode       = false;
    let _currentRecordIds = [];  // DB record IDs for saving
    let _currentRole    = '';
    let _currentTcode   = '';

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
        _currentOwners = [...owners];
        ownerCountEl.textContent = owners.length;
        if (owners.length > 0) {
            ownerEl.innerHTML = owners.map((o, idx) =>
                `<div style="display:flex;align-items:center;gap:.4rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:.45rem .75rem;min-width:0;">
                    <i class="bi bi-person-check-fill" style="color:#166534;font-size:.8rem;flex-shrink:0;"></i>
                    <span style="flex:1;font-size:.78rem;font-weight:600;color:#166534;line-height:1.3;word-break:break-word;">${o}</span>
                    ${_editMode ? `<button type="button" onclick="deleteOwner(${idx})" style="background:none;border:none;color:#c0392b;cursor:pointer;font-size:.85rem;padding:0;flex-shrink:0;line-height:1;" title="Remove"><i class="bi bi-x-circle-fill"></i></button>` : ''}
                 </div>`
            ).join('');
        } else {
            ownerEl.innerHTML = '<span style="color:var(--text-muted);font-size:.82rem;">Select a BPO to see User Access Matrix</span>';
        }
        document.getElementById('modalOwnerScroll').scrollTop = 0;
    }

    // ── Edit mode helpers ───────────────────────────────────────────
    function toggleEditOwners() {
        _editMode = true;
        document.getElementById('editOwnersBtn').style.display = 'none';
        document.getElementById('addOwnerRow').style.display = 'block';
        document.getElementById('saveOwnerRow').style.display = 'flex';
        renderOwners(_currentOwners);
    }

    function cancelEditOwners() {
        _editMode = false;
        document.getElementById('editOwnersBtn').style.display = '';
        document.getElementById('addOwnerRow').style.display = 'none';
        document.getElementById('saveOwnerRow').style.display = 'none';
        // Re-fetch fresh from hierarchy
        getOwnersForSelection();
    }

    function deleteOwner(idx) {
        _currentOwners.splice(idx, 1);
        renderOwners(_currentOwners);
    }

    function addOwner() {
        const input = document.getElementById('newOwnerInput');
        const val = input.value.trim();
        if (!val) return;
        if (_currentOwners.includes(val)) { input.value = ''; return; }
        _currentOwners.push(val);
        input.value = '';
        renderOwners(_currentOwners);
    }

    async function saveOwners() {
        const bpoVal  = bpoSelect.value;
        const unitVal = unitSelect.value;
        if (!bpoVal) { alert('Please select a BPO first.'); return; }

        const saveBtn = document.querySelector('#saveOwnerRow button:last-child');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';

        try {
            const res = await fetch('/access-matrix/sap/update-owners', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    role:    _currentRole,
                    tcode:   _currentTcode,
                    unit:    unitVal,
                    bpo:     bpoVal,
                    owners:  _currentOwners,
                    record_ids: _currentRecordIds,
                }),
            });
            const data = await res.json();
            if (res.ok) {
                // Update local hierarchy so UI stays in sync
                for (const unitNode of _hierarchy) {
                    const bpoNode = unitNode.bpos.find(b => b.bpo === bpoVal);
                    if (bpoNode) { bpoNode.owners = [..._currentOwners]; break; }
                }
                cancelEditOwners();
            } else {
                alert('Save failed: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            alert('Network error while saving.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save';
        }
    }

    function filterOwnersByUnit() {
        const bpoVal = bpoSelect.value;
        const unitVal = unitSelect.value;
        
        if (!bpoVal || !unitVal) {
            renderOwners([]);
            return;
        }
        
        const bpoNode = _hierarchy.find(node => node.bpo === bpoVal);
        let allOwners = [];
        if (bpoNode && bpoNode.units) {
            const unitNode = bpoNode.units.find(u => u.unit === unitVal);
            if (unitNode && unitNode.owners) {
                allOwners = unitNode.owners;
            }
        }
        
        allOwners = allOwners.sort();
        renderOwners(allOwners);
    }

    function populateUnitsForBpo() {
        const bpoVal = bpoSelect.value;
        if (!bpoVal) {
            setSelectOptions(unitSelect, [], '— select BPO first —');
            renderOwners([]);
            return;
        }

        const bpoNode = _hierarchy.find(node => node.bpo === bpoVal);
        let foundUnits = [];
        if (bpoNode && bpoNode.units) {
            foundUnits = bpoNode.units
                .map(u => u.unit)
                .sort();
        }

        setSelectOptions(unitSelect, foundUnits, '— Select Unit —');
        
        // Auto-select if there's only 1 unit
        if (foundUnits.length === 1) {
            unitSelect.value = foundUnits[0];
        }
        
        filterOwnersByUnit();
    }

    // ── BPO and Unit changes ───────────────
    bpoSelect.addEventListener('change', populateUnitsForBpo);
    unitSelect.addEventListener('change', filterOwnersByUnit);

    // ── Open modal ─────────────────────────────────────────────────
    async function openAccessModal(btn) {
        const role  = btn.dataset.role;
        const rowId = btn.dataset.rowId;
        const selectEl = document.querySelector(`.tcode-selector[data-row-id="${rowId}"]`);
        const tcode = selectEl ? selectEl.value : (btn.dataset.tcode || '');
        const requestId = btn.dataset.requestId || '';

        document.getElementById('modalRole').textContent        = role;
        document.getElementById('modalTcodeHeader').textContent = tcode || '—';
        if (document.getElementById('modalTcodeBadge')) {
            document.getElementById('modalTcodeBadge').textContent = tcode || '—';
        }

        accessModal.style.display = 'flex';
        document.getElementById('modalLoading').style.display        = 'block';
        document.getElementById('modalContentWrapper').style.display = 'none';
        document.getElementById('modalError').style.display          = 'none';

        // Reset edit mode & state
        _editMode = false;
        _currentRole  = role;
        _currentTcode = tcode;
        _currentRecordIds = [];
        if (document.getElementById('editOwnersBtn')) {
            document.getElementById('editOwnersBtn').style.display = '';
        }
        document.getElementById('addOwnerRow').style.display   = 'none';
        document.getElementById('saveOwnerRow').style.display  = 'none';

        // Reset dropdowns & owners while loading
        _hierarchy = [];
        setSelectOptions(bpoSelect, [], null);
        setSelectOptions(unitSelect, [], '— select BPO first —');
        renderOwners([]);

        function showModalError(title, message) {
            document.getElementById('modalLoading').style.display        = 'none';
            document.getElementById('modalContentWrapper').style.display = 'none';
            document.getElementById('modalError').style.display          = 'block';
            document.getElementById('modalErrorTitle').textContent      = title;
            document.getElementById('modalErrorMsg').textContent        = message;
        }

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 8000); // 8s timeout

        try {
            let url = `/access-matrix/sap/role-details?role=${encodeURIComponent(role)}&tcode=${encodeURIComponent(tcode)}&_=${Date.now()}`;
            if (requestId) {
                url += `&request_id=${encodeURIComponent(requestId)}`;
            }
            const res  = await fetch(url, { signal: controller.signal });
            clearTimeout(timeoutId);

            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned an invalid format response.');
            }

            const data = await res.json();

            if (res.ok) {
                _hierarchy        = data.hierarchy || [];
                _currentRecordIds = data.record_ids || [];

                if (_hierarchy.length === 0) {
                    showModalError("No access data found", "No application owners or matrix data is registered for this role and TCode.");
                    return;
                }

                // ── Collect ALL unique BPOs ─────────
                const allBpos = _hierarchy.map(node => node.bpo).filter(b => b);

                const hasMultipleBpos = allBpos.length > 1;
                setSelectOptions(bpoSelect, allBpos, hasMultipleBpos ? '— Select BPO —' : null);

                if (!hasMultipleBpos && allBpos.length === 1) {
                    // Only one BPO — auto-select and fill Unit
                    populateUnitsForBpo();
                } else {
                    setSelectOptions(unitSelect, [], '— select BPO first —');
                    renderOwners([]);
                }

                document.getElementById('modalLoading').style.display        = 'none';
                document.getElementById('modalContentWrapper').style.display = 'block';
                document.getElementById('modalError').style.display          = 'none';

                // Scroll owners panel to top each open
                document.getElementById('modalOwnerScroll').scrollTop = 0;

            } else {
                showModalError("No access data found", data.error || "No application owners or matrix data is registered for this role and TCode.");
            }
        } catch (e) {
            clearTimeout(timeoutId);
            if (e.name === 'AbortError') {
                showModalError("Request Timeout", "The request took too long to complete. Please try again.");
            } else {
                showModalError("Connection Failed", e.message || "Failed to retrieve access details from server.");
            }
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

    // ── Search always enabled ─────────────────────────────────────────
    const searchInput = document.getElementById('searchInput');
    const searchSubmitBtn = document.getElementById('searchSubmitBtn');
    if (searchInput) searchInput.disabled = false;
    if (searchSubmitBtn) searchSubmitBtn.disabled = false;

    // Sub-row TCODE toggle

    window.toggleSubRows = function(rowId) {
        const subrows = document.querySelectorAll('.subrow-' + rowId);
        const icon = document.getElementById('icon-sub-' + rowId);
        const textToggle = document.getElementById('text-toggle-' + rowId);
        
        if (subrows.length === 0) return;
        
        const isHidden = subrows[0].style.display === 'none' || subrows[0].style.display === '';
        
        if (icon) {
            icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        }
        
        if (textToggle) {
            textToggle.textContent = isHidden ? 'Hide TCODE' : 'View TCODE';
        }
        
        // Hide parent action buttons when expanded
        const viewBtn = document.querySelector('.view-access-btn-' + rowId);
        const editBtn = document.querySelector('.edit-btn-' + rowId);
        const deleteForm = document.querySelector('.delete-form-' + rowId);
        
        if (viewBtn) viewBtn.style.display = isHidden ? 'none' : 'inline-flex';
        if (editBtn) editBtn.style.display = isHidden ? 'none' : 'inline-flex';
        if (deleteForm) deleteForm.style.display = isHidden ? 'none' : 'block';

        if (isHidden) {
            // Expand with animation
            subrows.forEach(row => {
                row.style.display = 'table-row';
                // Force reflow
                void row.offsetWidth;
                row.style.borderColor = 'var(--border)';
                row.querySelectorAll('.anim-wrapper').forEach(wrapper => {
                    wrapper.style.maxHeight = '200px';
                    wrapper.style.opacity = '1';
                });
            });
            setTimeout(() => {
                // Check if still expanded
                if (icon && icon.style.transform === 'rotate(180deg)') {
                    subrows.forEach(row => {
                        row.querySelectorAll('.anim-wrapper').forEach(wrapper => {
                            wrapper.style.overflow = 'visible';
                        });
                    });
                }
            }, 300);
        } else {
            // Collapse with animation
            subrows.forEach(row => {
                row.style.borderColor = 'transparent';
                row.querySelectorAll('.anim-wrapper').forEach(wrapper => {
                    wrapper.style.overflow = 'hidden';
                    wrapper.style.maxHeight = '0';
                    wrapper.style.opacity = '0';
                });
            });
            // Wait for transition to finish before hiding
            setTimeout(() => {
                subrows.forEach(row => {
                    // Check if it's still supposed to be hidden (user didn't quickly double click)
                    if (icon && icon.style.transform === 'rotate(0deg)') {
                        row.style.display = 'none';
                    }
                });
            }, 300);
        }
    };

    // Auto-save helpers
    window.autoSaveDecision = function(recordId, decision) {
        const reqId = "{{ $uamRequest ? $uamRequest->id : '' }}";
        if (!reqId) return;
        fetch(`/access-matrix/approval/${reqId}/auto-save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                record_id: recordId,
                decision: decision
            })
        });
    };

    let _commentDebounceTimer = null;
    window.debouncedAutoSaveComment = function(comment) {
        const reqId = "{{ $uamRequest ? $uamRequest->id : '' }}";
        if (!reqId) return;
        clearTimeout(_commentDebounceTimer);
        _commentDebounceTimer = setTimeout(() => {
            fetch(`/access-matrix/approval/${reqId}/auto-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    approver_comment: comment
                })
            });
        }, 500);
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof validateDecisionForm === 'function') {
            validateDecisionForm();
        }
    });

    // ── Add TCODE Modal Logic ───────────────────────────────────────────────
    let addTcodeGlobalMatrix = {};
    let addTcodeBpos = [];
    const addTcodeReqId = "{{ $uamRequest ? $uamRequest->id : '' }}";

    // Assuming global matrix is fetched via same mechanism
    if (addTcodeReqId) {
        fetch(`/access-matrix/request/${addTcodeReqId}/matrix-map`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    addTcodeGlobalMatrix = data.matrix || {};
                    // Build unified BPO list
                    const bposSet = new Set();
                    Object.values(addTcodeGlobalMatrix).forEach(bpoMap => {
                        Object.keys(bpoMap).forEach(b => bposSet.add(b));
                    });
                    addTcodeBpos = Array.from(bposSet).sort();
                }
            })
            .catch(err => console.error("Error fetching matrix map:", err));
    }

    const addTcodeModalEl = document.getElementById('addTcodeModal');
    let addTcodeModal = null;

    if (addTcodeModalEl) {
        addTcodeModal = new bootstrap.Modal(addTcodeModalEl);
    }

    window.openAddTcodeModal = function(role) {
        if (!addTcodeModal) return;
        document.getElementById('addTcodeRole').value = role;
        
        // Update form action
        const form = document.getElementById('addTcodeForm');
        // Ensure request_id is correctly set as a hidden input
        let reqInput = form.querySelector('input[name="request_id"]');
        if (!reqInput) {
            reqInput = document.createElement('input');
            reqInput.type = 'hidden';
            reqInput.name = 'request_id';
            form.appendChild(reqInput);
        }
        reqInput.value = addTcodeReqId;

        form.action = `/access-matrix/sap/role/${role}/tcode`;

        // Reset input
        const tcodeInp = document.getElementById('addTcodeCode');
        tcodeInp.value = '';

        addTcodeModal.show();
    };
</script>
@endpush



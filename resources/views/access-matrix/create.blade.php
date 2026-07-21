@extends('layouts.app')

@section('title', 'Add UAM Record')

@section('content')

{{-- Navbar --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="navbar-brand-wrapper">
                <div class="brand-dot"><i class="bi bi-shield-lock-fill"></i></div>
                <div>
                    <div class="brand-text-main">AccessHub</div>
                    <div class="brand-text-sub">PT Telkom Infrastruktur Indonesia</div>
                </div>
            </a>
            <x-navbar-right />
        </div>
    </div>
</nav>

<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <aside class="sidebar d-none d-lg-block" style="width:230px;flex-shrink:0;">
        <div class="sidebar-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="sidebar-nav-item"><i class="bi bi-grid-fill"></i> Dashboard</a>
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
                <a href="{{ route('access-matrix.uam-request.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.uam-request.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Accept
                </a>
                <a href="{{ route('access-matrix.approval.index') }}" class="sidebar-nav-item {{ request()->routeIs('access-matrix.approval.*') ? 'active' : '' }}" style="padding-left: 2.75rem; font-size: .8rem; border-left: none;">
                    Approval Access Matrix
                </a>
            </div>
        </div>
        <a href="#" class="sidebar-nav-item" aria-disabled="true"><i class="bi bi-clipboard2-check-fill"></i> Access Review
            <span class="ms-auto badge" style="background:var(--primary-light);color:var(--primary);font-size:.62rem;font-weight:700;padding:.2rem .45rem;border-radius:6px;">Soon</span></a>

    </aside>

    <main class="flex-grow-1 page-content px-4">

                <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Request Access Matrix', 'url' => route('access-matrix.request.index')],
            ['label' => 'UAM SAP', 'url' => route('access-matrix.request.sap')],
            ['label' => 'Add Record'],
        ]" />

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in">
            <div>
                <h1 style="font-size:1.35rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                    <i class="bi bi-plus-circle-fill me-2" style="color:var(--primary);"></i>Add New UAM Record
                </h1>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0;">Create a new User Access Matrix entry</p>
            </div>
            <a href="{{ route('access-matrix.sap', $requestId ? ['request_id' => $requestId] : []) }}"
               style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:10px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:600;color:var(--text-muted);text-decoration:none;transition:all var(--transition);"
               onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';"
               onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        {{-- Form Card --}}
        <div class="animate-in animate-in-delay-1" style="max-width:720px;">
            <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);">

                <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                    <div style="font-size:.88rem;font-weight:700;color:var(--secondary);">
                        <i class="bi bi-shield-lock-fill me-2"></i>Role Details
                    </div>
                </div>

                <div style="padding:1.75rem 1.5rem;">

                    @if ($errors->any())
                        <div style="background:var(--primary-light);border-left:4px solid var(--primary);border-radius:10px;color:#7b0d0f;font-size:.875rem;padding:.75rem 1rem;margin-bottom:1.25rem;">
                            <div style="font-weight:600;margin-bottom:.3rem;"><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:</div>
                            <ul style="margin:0;padding-left:1.2rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('access-matrix.store') }}" id="createForm">
                        @csrf
                        @if($requestId)
                            <input type="hidden" name="request_id" value="{{ $requestId }}">
                        @endif

                        {{-- Role --}}
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                Role <span style="color:var(--primary);">*</span>
                            </label>
                            <input type="text" id="role" name="role"
                                   class="form-control @error('role') is-invalid @enderror"
                                   value="{{ old('role') }}"
                                   placeholder="e.g. ZPS-MD-1014-000000-PROJ-CHG"
                                   style="font-family:monospace;font-weight:600;">
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description Role --}}
                        <div class="mb-3">
                            <label for="description_role" class="form-label">Description Role</label>
                            <textarea id="description_role" name="description_role" rows="3"
                                      class="form-control @error('description_role') is-invalid @enderror"
                                      placeholder="Describe what this role is for…">{{ old('description_role') }}</textarea>
                            @error('description_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Module & Period: inherited from the parent UAM request --}}
                        @if($uamRequest)
                            <input type="hidden" name="module" value="{{ $uamRequest->module }}">
                            <input type="hidden" name="period" value="{{ $uamRequest->period }}">
                            {{-- Read-only context badge --}}
                            <div class="mb-3" style="display:flex;align-items:center;gap:.5rem;padding:.5rem .85rem;background:var(--secondary-light);border:1.5px solid rgba(11,46,109,.12);border-radius:10px;">
                                <i class="bi bi-info-circle-fill" style="color:var(--secondary);font-size:.82rem;flex-shrink:0;"></i>
                                <span style="font-size:.78rem;color:var(--secondary);">
                                    This role will be added to
                                    <strong>{{ $uamRequest->module }}</strong>
                                    &nbsp;·&nbsp;
                                    <strong>{{ $uamRequest->full_period }}</strong>
                                </span>
                            </div>
                        @else
                            {{-- Fallback: show fields when no parent request exists --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">
                                    <label for="module" class="form-label">Module <span style="color:var(--primary);">*</span></label>
                                    <input type="text" id="module" name="module"
                                           class="form-control @error('module') is-invalid @enderror"
                                           value="{{ old('module') }}"
                                           placeholder="e.g. PS" required>
                                    @error('module')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="period" class="form-label">Period <span style="color:var(--primary);">*</span></label>
                                    <select id="period" name="period"
                                            class="form-select @error('period') is-invalid @enderror" required>
                                        <option value="" disabled {{ old('period') ? '' : 'selected' }}>-- Select Period --</option>
                                        <option value="Q1" {{ old('period') == 'Q1' ? 'selected' : '' }}>Q1 (First Period)</option>
                                        <option value="Q2" {{ old('period') == 'Q2' ? 'selected' : '' }}>Q2 (Second Period)</option>
                                        <option value="Q3" {{ old('period') == 'Q3' ? 'selected' : '' }}>Q3 (Third Period)</option>
                                    </select>
                                    @error('period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endif

                        {{-- TCODE (multi-entry) --}}
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">TCODE</label>
                                <button type="button" id="addTcodeBtn" onclick="addTcodeRow()"
                                    title="Add another TCODE"
                                    style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#22c55e;color:#fff;border:none;cursor:pointer;flex-shrink:0;box-shadow:0 2px 6px rgba(34,197,94,.25);transition:transform .15s,box-shadow .15s;"
                                    onmouseenter="this.style.transform='scale(1.12)';this.style.boxShadow='0 4px 10px rgba(34,197,94,.38)';"
                                    onmouseleave="this.style.transform='';this.style.boxShadow='0 2px 6px rgba(34,197,94,.25)';">
                                    <i class="bi bi-plus-lg" style="font-size:.72rem;line-height:1;"></i>
                                </button>
                            </div>
                            <div id="tcodeList" style="display:flex;flex-direction:column;gap:.45rem;">
                                {{-- First TCODE row (always present) --}}
                                <div class="tcode-row" style="display:flex;align-items:center;gap:.4rem;">
                                    <input type="text" name="tcode[]"
                                           class="form-control @error('tcode.0') is-invalid @enderror"
                                           value="{{ is_array(old('tcode')) ? old('tcode.0', '') : old('tcode', '') }}"
                                           placeholder="e.g. SU01"
                                           style="font-family:monospace;flex:1;">
                                    <button type="button" class="remove-tcode-btn"
                                        onclick="removeTcodeRow(this)"
                                        disabled
                                        title="Remove this TCODE"
                                        style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fde8e9;color:#c0392b;border:1px solid #fca5a5;cursor:not-allowed;opacity:.35;flex-shrink:0;transition:all .15s;">
                                        <i class="bi bi-x-lg" style="font-size:.68rem;"></i>
                                    </button>
                                </div>
                            </div>
                            @error('tcode.*')
                                <div style="color:#dc2626;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- BPO — LEFT --}}
                            <div class="col-12 col-sm-6">
                                <label for="bpo" class="form-label">BPO</label>
                                <input list="bpo-options" id="bpo" name="bpo" class="form-control @error('bpo') is-invalid @enderror" placeholder="Select or type BPO" autocomplete="off" value="{{ old('bpo') }}">
                                <datalist id="bpo-options"></datalist>
                                @error('bpo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- UNIT — RIGHT --}}
                            <div class="col-12 col-sm-6">
                                <label for="unit" class="form-label">UNIT</label>
                                <input list="unit-options" id="unit" name="unit" class="form-control @error('unit') is-invalid @enderror" placeholder="Select or type Unit" autocomplete="off" value="{{ old('unit') }}">
                                <datalist id="unit-options"></datalist>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- Access Owner --}}
                            <div class="col-12 col-sm-6">
                                <label for="access_owner" class="form-label">User Access Matrix</label>
                                <input list="ao-options" id="access_owner" name="access_owner" class="form-control @error('access_owner') is-invalid @enderror" placeholder="Select or type Access Owner" autocomplete="off" value="{{ old('access_owner') }}">
                                <datalist id="ao-options"></datalist>
                                @error('access_owner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" id="saveBtn" class="btn-primary-custom"
                                    style="width:auto;padding:.65rem 2rem;font-size:.9rem;display:inline-flex;align-items:center;gap:.5rem;">
                                <i class="bi bi-check-lg"></i> Save Record
                            </button>
                             <a href="{{ route('access-matrix.sap') }}"
                               style="font-size:.85rem;color:var(--text-muted);text-decoration:none;font-weight:500;transition:color var(--transition);"
                               onmouseenter="this.style.color='var(--primary)'"
                               onmouseleave="this.style.color='var(--text-muted)'">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
</div>

@endsection

@push('scripts')
<script>
    // Profile dropdown
    const profileBtn  = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    const chevron     = document.getElementById('profileChevron');
    profileBtn.addEventListener('click', e => {
        e.stopPropagation();
        const open = profileMenu.style.display === 'block';
        profileMenu.style.display = open ? 'none' : 'block';
        chevron.style.transform   = open ? '' : 'rotate(180deg)';
    });
    document.addEventListener('click', () => { profileMenu.style.display = 'none'; chevron.style.transform = ''; });

    // Save button spinner + TCODE dynamic rows
    document.getElementById('createForm').addEventListener('submit', function () {
        const btn = document.getElementById('saveBtn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
    });

    function addTcodeRow() {
        const list = document.getElementById('tcodeList');
        const row  = document.createElement('div');
        row.className = 'tcode-row';
        row.style.cssText = 'display:flex;align-items:center;gap:.4rem;';
        // New row starts with only the × button
        row.innerHTML = `
            <input type="text" name="tcode[]"
                   class="form-control"
                   placeholder="e.g. SU01"
                   style="font-family:monospace;flex:1;">
            <button type="button" class="remove-tcode-btn"
                onclick="removeTcodeRow(this)"
                title="Remove this TCODE"
                style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fde8e9;color:#c0392b;border:1px solid #fca5a5;cursor:pointer;flex-shrink:0;transition:all .15s;"
                onmouseenter="this.style.filter='brightness(0.93)'"
                onmouseleave="this.style.filter=''">
                <i class="bi bi-x-lg" style="font-size:.68rem;"></i>
            </button>`;
        list.appendChild(row);
        syncTcodeButtons();
        row.querySelector('input').focus();
    }

    function removeTcodeRow(btn) {
        btn.closest('.tcode-row').remove();
        syncTcodeButtons();
    }

    // Single source of truth: ensures "×" is disabled when there is only one row.
    function syncTcodeButtons() {
        const rows = document.querySelectorAll('#tcodeList .tcode-row');

        rows.forEach(function(row) {
            // ── Remove (×) button ────────────────────────────────────────────
            const removeBtn = row.querySelector('.remove-tcode-btn');
            if (removeBtn) {
                const only = rows.length === 1;
                removeBtn.disabled     = only;
                removeBtn.style.opacity = only ? '.35' : '1';
                removeBtn.style.cursor  = only ? 'not-allowed' : 'pointer';
            }
        });
    }

    // Run once on load to set initial state
    syncTcodeButtons();

    // ── Dynamic Dropdowns logic ──────────────────────────────────────
    let globalMatrix = {};
    const requestId = '{{ $requestId ?? ($uamRequest->id ?? "") }}';

    if (requestId) {
        fetch(`/access-matrix/request/${requestId}/matrix-map`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    globalMatrix = data.matrix || {};
                    refreshDropdowns();
                }
            })
            .catch(err => console.error("Error fetching matrix map:", err));
    }

    const tcodeList = document.getElementById('tcodeList');
    const bpoInput = document.getElementById('bpo');
    const unitInput = document.getElementById('unit');
    const aoInput = document.getElementById('access_owner');
    
    const bpoDatalist = document.getElementById('bpo-options');
    const unitDatalist = document.getElementById('unit-options');
    const aoDatalist = document.getElementById('ao-options');

    tcodeList.addEventListener('input', function(e) {
        if (e.target.tagName === 'INPUT') refreshDropdowns();
    });

    bpoInput.addEventListener('input', refreshDropdowns);
    unitInput.addEventListener('input', refreshDropdowns);

    function refreshDropdowns(e) {
        const tcodes = Array.from(document.querySelectorAll('input[name="tcode[]"]'))
                            .map(i => i.value.trim())
                            .filter(v => v !== '');

        const selectedBpo = bpoInput.value.trim();
        const selectedUnit = unitInput.value.trim();

        if (tcodes.length === 0) {
            bpoDatalist.innerHTML = '';
            unitDatalist.innerHTML = '';
            aoDatalist.innerHTML = '';
            return;
        }

        // Determine valid BPOs (intersection across all tcodes)
        let validBpos = null;
        for (let tc of tcodes) {
            const map = globalMatrix[tc] || {};
            const bpos = Object.keys(map);
            if (validBpos === null) validBpos = bpos;
            else validBpos = validBpos.filter(b => bpos.includes(b));
        }

        populateDatalist(bpoDatalist, validBpos || []);

        // If a BPO is selected, determine valid Units
        if (!selectedBpo) {
            unitDatalist.innerHTML = '';
            aoDatalist.innerHTML = '';
            return;
        }

        let validUnits = null;
        for (let tc of tcodes) {
            const units = Object.keys(globalMatrix[tc] && globalMatrix[tc][selectedBpo] ? globalMatrix[tc][selectedBpo] : {});
            if (validUnits === null) validUnits = units;
            else validUnits = validUnits.filter(u => units.includes(u));
        }

        populateDatalist(unitDatalist, validUnits || []);

        // If a Unit is selected, determine valid AOs
        if (!selectedUnit) {
            aoDatalist.innerHTML = '';
            return;
        }

        let validAos = null;
        for (let tc of tcodes) {
            const aos = (globalMatrix[tc] && globalMatrix[tc][selectedBpo] && globalMatrix[tc][selectedBpo][selectedUnit]) ? globalMatrix[tc][selectedBpo][selectedUnit] : [];
            if (validAos === null) validAos = aos;
            else validAos = validAos.filter(a => aos.includes(a));
        }

        populateDatalist(aoDatalist, validAos || []);
    }

    function populateDatalist(datalistEl, optionsArray) {
        datalistEl.innerHTML = '';
        if (!optionsArray || optionsArray.length === 0) return;
        
        // Remove duplicates and sort
        const uniqueOptions = [...new Set(optionsArray)].sort();
        
        uniqueOptions.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            datalistEl.appendChild(option);
        });
    }
</script>
@endpush




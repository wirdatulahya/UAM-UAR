@extends('layouts.app')

@section('title', 'Add Role')

@section('content')

{{-- Navbar --}}
{{-- ─── App Shell ─────────────────────────────────────────────────────── --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Sidebar (Fixed full-height) --}}
<x-sidebar />

{{-- Content Wrapper --}}
<div class="app-content-wrapper">

    {{-- Topbar --}}
    <header class="app-topbar">
        <div class="topbar-left">
            <button class="btn-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
        {{-- Right — Profile Dropdown --}}
        <x-navbar-right />
    </header>

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

                <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Request Access Matrix', 'url' => route('access-matrix.request.index')],
            ['label' => 'UAM SAP', 'url' => route('access-matrix.request.sap')],
            ['label' => 'Add Role'],
        ]" />

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;letter-spacing:-.5px;">
                    Add New Role
                </h1>
                <p style="font-size:.88rem;color:var(--text-muted);margin:0;">Create a new User Access Matrix entry</p>
            </div>
            <a href="{{ route('access-matrix.sap', $requestId ? ['request_id' => $requestId] : []) }}"
               style="display:inline-flex;align-items:center;gap:.45rem;background:none;border:1.5px solid var(--border);border-radius:var(--input-radius);padding:.52rem 1.15rem;font-size:.85rem;font-weight:700;color:var(--text-muted);text-decoration:none;transition:all var(--transition);"
               onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';this.style.background='var(--secondary-light)'"
               onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';this.style.background=''">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        {{-- Form Card --}}
        <div class="animate-in animate-in-delay-1" style="max-width:720px;">
            <div class="card-premium">
                <div class="card-header-premium">
                    <div style="font-size:.9rem;font-weight:800;color:var(--secondary);display:flex;align-items:center;gap:.5rem;">
                        <div style="width:32px;height:32px;background:linear-gradient(135deg,var(--primary-light),#ffc1c2);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--primary);">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        Role Details
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
                            <div class="mb-4" style="display:flex;align-items:center;gap:.6rem;padding:.7rem 1rem;background:var(--secondary-light);border:1px solid rgba(11,46,109,.12);border-radius:var(--input-radius);">
                                <i class="bi bi-info-circle-fill" style="color:var(--secondary);font-size:.9rem;flex-shrink:0;"></i>
                                <span style="font-size:.82rem;color:var(--secondary);">
                                    This role will be added to
                                    <strong style="color:var(--primary);">{{ $uamRequest->module }}</strong>
                                    &nbsp;·&nbsp;
                                    <strong style="color:var(--primary);">{{ $uamRequest->full_period }}</strong>
                                </span>
                            </div>
                        @else
                            {{-- Fallback: show fields when no parent request exists --}}
                            <div class="row g-3 mb-4">
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
                            {{-- BPO -- pulled from Master Data --}}
                            <div class="col-12 col-sm-6">
                                <label for="bpo" class="form-label">BPO</label>
                                <select id="bpo" name="bpo" class="form-select @error('bpo') is-invalid @enderror">
                                    <option value="">-- Pilih BPO --</option>
                                </select>
                                @error('bpo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- UNIT -- filtered by selected BPO --}}
                            <div class="col-12 col-sm-6">
                                <label for="unit" class="form-label">UNIT</label>
                                <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror" disabled>
                                    <option value="">-- Pilih BPO dahulu --</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- User Access Matrix -- filtered by BPO + Unit from matrix map --}}
                            <div class="col-12 col-sm-6">
                                <label for="access_owner" class="form-label">User</label>
                                <select id="access_owner" name="access_owner" class="form-select @error('access_owner') is-invalid @enderror" disabled>
                                    <option value="">-- Pilih Unit dahulu --</option>
                                </select>
                                @error('access_owner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" id="saveBtn" class="btn-primary-custom"
                                    style="width:auto;padding:.65rem 2rem;font-size:.9rem;display:inline-flex;align-items:center;gap:.5rem;">
                                <i class="bi bi-check-lg"></i> Save Role
                            </button>
                             <a href="{{ route('access-matrix.sap', array_filter(['request_id' => $requestId ?? null])) }}"
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
    // Profile dropdown handled globally by Bootstrap

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

    // ── 1. Pre-fill old values (from validation failure) ─────────────────────
    const OLD_BPO  = '{{ old('bpo') }}';
    const OLD_UNIT = '{{ old('unit') }}';
    const OLD_AO   = '{{ old('access_owner') }}';

    const bpoSelect  = document.getElementById('bpo');
    const unitSelect = document.getElementById('unit');
    const aoSelect   = document.getElementById('access_owner');

    // ── 2. Load all active BPOs from Master Data ─────────────────────────────
    // Store BPO objects (with id) so we can fetch units by id later
    let allBpos = [];

    fetch('/api/master-data/bpos')
        .then(r => r.json())
        .then(bpos => {
            allBpos = bpos;
            bpos.forEach(b => {
                const o = document.createElement('option');
                o.value = b.name;
                o.dataset.id = b.id;
                o.textContent = b.name;
                bpoSelect.appendChild(o);
            });
            // Restore old value after validation failure
            if (OLD_BPO) {
                bpoSelect.value = OLD_BPO;
                const selOpt = bpoSelect.querySelector(`option[value="${OLD_BPO}"]`);
                if (selOpt) loadUnits(selOpt.dataset.id, OLD_UNIT);
            }
        })
        .catch(err => console.error('Error loading BPOs:', err));

    // ── 3. When BPO changes → load Units ─────────────────────────────────────
    bpoSelect.addEventListener('change', function () {
        const selOpt = this.options[this.selectedIndex];
        const bpoId  = selOpt ? selOpt.dataset.id : null;

        unitSelect.innerHTML = '<option value="">-- Pilih Unit --</option>';
        unitSelect.disabled = true;
        aoSelect.innerHTML = '<option value="">-- Pilih Unit dahulu --</option>';
        aoSelect.disabled = true;

        if (!bpoId) return;
        loadUnits(bpoId, null);
    });

    function loadUnits(bpoId, preselectUnit) {
        fetch(`/api/master-data/bpos/${bpoId}/units`)
            .then(r => r.json())
            .then(units => {
                unitSelect.innerHTML = '<option value="">-- Pilih Unit --</option>';
                units.forEach(u => {
                    const o = document.createElement('option');
                    o.value = u.name;
                    o.textContent = u.name;
                    if (preselectUnit && u.name === preselectUnit) o.selected = true;
                    unitSelect.appendChild(o);
                });
                unitSelect.disabled = false;
                if (preselectUnit && unitSelect.value === preselectUnit) {
                    loadAos(bpoSelect.value, preselectUnit, OLD_AO);
                }
            })
            .catch(err => console.error('Error loading Units:', err));
    }

    // ── 4. When Unit changes → filter Users from the matrix map ──────────────
    let globalMatrix = {};
    const requestId = '{{ $requestId ?? ($uamRequest->id ?? "") }}';

    if (requestId) {
        fetch(`/access-matrix/request/${requestId}/matrix-map`)
            .then(r => r.json())
            .then(data => { if (data.success) globalMatrix = data.matrix || {}; })
            .catch(err => console.error('Error fetching matrix map:', err));
    }

    unitSelect.addEventListener('change', function () {
        const bpoCode  = bpoSelect.value;
        const unitCode = this.value;
        aoSelect.innerHTML = '<option value="">-- Pilih User --</option>';
        aoSelect.disabled = true;
        if (!unitCode) return;
        loadAos(bpoCode, unitCode, null);
    });

    function loadAos(bpoCode, unitCode, preselectAo) {
        const tcodes = Array.from(document.querySelectorAll('input[name="tcode[]"]'))
                           .map(i => i.value.trim()).filter(v => v !== '');

        let validAos = null;
        if (tcodes.length > 0) {
            for (const tc of tcodes) {
                const aos = (globalMatrix[tc] &&
                             globalMatrix[tc][bpoCode] &&
                             globalMatrix[tc][bpoCode][unitCode])
                    ? globalMatrix[tc][bpoCode][unitCode]
                    : [];
                if (validAos === null) validAos = [...aos];
                else validAos = validAos.filter(a => aos.includes(a));
            }
        }

        const aoList = validAos ? [...new Set(validAos)].sort() : [];

        aoSelect.innerHTML = aoList.length === 0
            ? '<option value="">-- Tidak ada user untuk kombinasi ini --</option>'
            : '<option value="">-- Pilih User --</option>';

        aoList.forEach(ao => {
            const o = document.createElement('option');
            o.value = ao;
            o.textContent = ao;
            if (preselectAo && ao === preselectAo) o.selected = true;
            aoSelect.appendChild(o);
        });
        aoSelect.disabled = aoList.length === 0;
    }

    // Re-calculate AOs when TCODE input changes
    document.getElementById('tcodeList').addEventListener('input', function (e) {
        if (e.target.tagName === 'INPUT' && unitSelect.value) {
            loadAos(bpoSelect.value, unitSelect.value, aoSelect.value);
        }
    });
</script>
@endpush




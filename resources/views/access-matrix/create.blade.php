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

                        {{-- Access Mappings Section --}}
                        <div class="mb-4">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <i class="bi bi-diagram-3-fill" style="color:var(--secondary);"></i> Access Mappings <span style="color:var(--primary);">*</span>
                            </label>
                            
                            {{-- Container for Saved Mappings --}}
                            <div id="savedMappingsList" style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1rem;">
                                <!-- Mappings will be injected here -->
                            </div>

                            {{-- Hidden input to store JSON mappings payload --}}
                            <input type="hidden" name="mappings" id="mappingsPayload" value="{{ old('mappings', '[]') }}">
                            @error('mappings')
                                <div style="color:#dc2626;font-size:.8rem;margin-bottom:1rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                            @enderror

                            {{-- Mapping Builder Card --}}
                            <div class="mapping-builder-card" style="background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:12px;padding:1.25rem;">
                                <div style="font-size:.85rem;font-weight:700;color:var(--secondary);margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;">
                                    <span><i class="bi bi-plus-circle-dotted"></i> Build Mapping</span>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label for="builder_bpo" class="form-label" style="font-size:.75rem;margin-bottom:.2rem;">BPO</label>
                                        <select id="builder_bpo" class="form-select">
                                            <option value="">-- Pilih BPO --</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="builder_unit" class="form-label" style="font-size:.75rem;margin-bottom:.2rem;">UNIT</label>
                                        <select id="builder_unit" class="form-select" disabled>
                                            <option value="">-- Pilih BPO dahulu --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label mb-0" style="font-size:.75rem;">Users</label>
                                        <button type="button" onclick="addBuilderUserRow()"
                                            title="Add another User"
                                            style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#22c55e;color:#fff;border:none;cursor:pointer;flex-shrink:0;transition:transform .15s;">
                                            <i class="bi bi-plus" style="font-size:1rem;line-height:1;"></i>
                                        </button>
                                    </div>
                                    <div id="builderUserList" style="display:flex;flex-direction:column;gap:.45rem;">
                                        <div class="builder-user-row" style="display:flex;align-items:center;gap:.4rem;">
                                            <div style="position:relative;flex:1;" class="user-input-wrapper">
                                                <select class="form-select user-input-field">
                                                    <option value="">-- Pilih User --</option>
                                                </select>
                                            </div>
                                            <button type="button" class="remove-user-btn" onclick="removeBuilderUserRow(this)" disabled
                                                style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fde8e9;color:#c0392b;border:1px solid #fca5a5;cursor:not-allowed;opacity:.35;flex-shrink:0;">
                                                <i class="bi bi-x-lg" style="font-size:.68rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" id="addMappingBtn" class="btn btn-sm" onclick="saveCurrentMapping()"
                                            style="background:var(--secondary);color:#fff;border-radius:8px;padding:.4rem 1.25rem;font-size:.8rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;transition:all var(--transition);">
                                        <i class="bi bi-save"></i> Save Mapping
                                    </button>
                                </div>
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
    document.getElementById('createForm').addEventListener('submit', function (e) {
        if (mappings.length === 0) {
            e.preventDefault();
            alert('Please add at least one Access Mapping before saving the role.');
            return;
        }
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

    // ── MAPPINGS LOGIC ──────────────────────────────────────────────────────────
    let mappings = [];
    const mappingsPayload = document.getElementById('mappingsPayload');
    const savedMappingsList = document.getElementById('savedMappingsList');
    const builderBpo = document.getElementById('builder_bpo');
    const builderUnit = document.getElementById('builder_unit');
    const builderUserList = document.getElementById('builderUserList');
    const requestId = '{{ $requestId ?? ($uamRequest->id ?? "") }}';
    
    // Load initial mappings from old input
    try {
        const initial = JSON.parse(mappingsPayload.value);
        if (Array.isArray(initial)) {
            mappings = initial;
            renderMappings();
        }
    } catch (e) {}

    let allBpos = [];
    let allUsers = [];

    // Load BPOs
    fetch('/api/master-data/bpos')
        .then(r => r.json())
        .then(bpos => {
            allBpos = bpos;
            bpos.forEach(b => {
                const o = document.createElement('option');
                o.value = b.name;
                o.dataset.id = b.id;
                o.textContent = b.name;
                builderBpo.appendChild(o);
            });
        });

    // Load Users
    fetch('/api/master-data/users')
        .then(r => r.json())
        .then(users => {
            allUsers = users;
            // Populate the initial user row
            const selects = document.querySelectorAll('.user-input-field');
            selects.forEach(sel => {
                users.forEach(u => {
                    const o = document.createElement('option');
                    o.value = u.name;
                    o.textContent = u.name;
                    sel.appendChild(o);
                });
            });
        });

    // Builder BPO Change
    builderBpo.addEventListener('change', function () {
        const selOpt = this.options[this.selectedIndex];
        const bpoId  = selOpt ? selOpt.dataset.id : null;

        builderUnit.innerHTML = '<option value="">-- Pilih Unit --</option>';
        builderUnit.disabled = true;

        if (!bpoId) return;

        fetch(`/api/master-data/bpos/${bpoId}/units`)
            .then(r => r.json())
            .then(units => {
                builderUnit.innerHTML = '<option value="">-- Pilih Unit --</option>';
                units.forEach(u => {
                    const o = document.createElement('option');
                    o.value = u.name;
                    o.textContent = u.name;
                    builderUnit.appendChild(o);
                });
                builderUnit.disabled = false;
            });
    });

    // Builder User Rows
    function addBuilderUserRow() {
        const list = document.getElementById('builderUserList');
        const row  = document.createElement('div');
        row.className = 'builder-user-row';
        row.style.cssText = 'display:flex;align-items:center;gap:.4rem;';
        
        let selectHtml = `<select class="form-select user-input-field"><option value="">-- Pilih User --</option>`;
        allUsers.forEach(u => {
            selectHtml += `<option value="${u.name}">${u.name}</option>`;
        });
        selectHtml += `</select>`;

        row.innerHTML = `
            <div style="position:relative;flex:1;" class="user-input-wrapper">
                ${selectHtml}
            </div>
            <button type="button" class="remove-user-btn" onclick="removeBuilderUserRow(this)"
                style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fde8e9;color:#c0392b;border:1px solid #fca5a5;cursor:pointer;flex-shrink:0;">
                <i class="bi bi-x-lg" style="font-size:.68rem;"></i>
            </button>`;
        list.appendChild(row);
        syncUserButtons();
    }

    function removeBuilderUserRow(btn) {
        btn.closest('.builder-user-row').remove();
        syncUserButtons();
    }

    function syncUserButtons() {
        const rows = document.querySelectorAll('#builderUserList .builder-user-row');
        rows.forEach(function(row) {
            const removeBtn = row.querySelector('.remove-user-btn');
            if (removeBtn) {
                const only = rows.length === 1;
                removeBtn.disabled     = only;
                removeBtn.style.opacity = only ? '.35' : '1';
                removeBtn.style.cursor  = only ? 'not-allowed' : 'pointer';
            }
        });
    }
    syncUserButtons();

    // Save Mapping
    function saveCurrentMapping() {
        const bpo = builderBpo.value.trim();
        const unit = builderUnit.value.trim();
        
        if (!bpo || !unit) {
            alert('Please select BPO and Unit.');
            return;
        }

        const userInputs = document.querySelectorAll('.user-input-field');
        const users = [];
        userInputs.forEach(i => {
            const v = i.value.trim();
            if (v) users.push(v);
        });

        if (users.length === 0) {
            alert('Please add at least one User.');
            return;
        }

        mappings.push({ bpo, unit, users: [...new Set(users)] });
        updateMappingsPayload();
        renderMappings();

        // Reset builder
        builderBpo.value = '';
        builderUnit.innerHTML = '<option value="">-- Pilih BPO dahulu --</option>';
        builderUnit.disabled = true;
        
        // Reset user list to 1 empty row
        const userList = document.getElementById('builderUserList');
        userList.innerHTML = '';
        addBuilderUserRow();
    }

    function removeMapping(index) {
        mappings.splice(index, 1);
        updateMappingsPayload();
        renderMappings();
    }

    function updateMappingsPayload() {
        mappingsPayload.value = JSON.stringify(mappings);
    }

    function renderMappings() {
        savedMappingsList.innerHTML = '';
        if (mappings.length === 0) {
            savedMappingsList.style.display = 'none';
            return;
        }
        savedMappingsList.style.display = 'flex';

        mappings.forEach((m, idx) => {
            const card = document.createElement('div');
            card.style.cssText = 'background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:1rem;display:flex;justify-content:space-between;align-items:flex-start;box-shadow:0 1px 3px rgba(0,0,0,0.05);';
            
            const usersHtml = m.users.map(u => `<div style="font-size:.78rem;background:#f1f5f9;color:#334155;padding:.2rem .6rem;border-radius:4px;display:inline-block;margin-right:.3rem;margin-top:.3rem;"><i class="bi bi-person-fill"></i> ${u}</div>`).join('');
            
            card.innerHTML = `
                <div>
                    <div style="font-size:.7rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.2rem;">${m.bpo}</div>
                    <div style="font-size:.85rem;font-weight:700;color:var(--secondary);margin-bottom:.4rem;">${m.unit}</div>
                    <div>${usersHtml}</div>
                </div>
                <button type="button" onclick="removeMapping(${idx})" title="Delete Mapping"
                        style="background:none;border:none;color:#ef4444;cursor:pointer;padding:.3rem;border-radius:6px;transition:background .15s;"
                        onmouseenter="this.style.background='#fef2f2'" onmouseleave="this.style.background='none'">
                    <i class="bi bi-trash-fill"></i>
                </button>
            `;
            savedMappingsList.appendChild(card);
        });
    }
</script>
@endpush




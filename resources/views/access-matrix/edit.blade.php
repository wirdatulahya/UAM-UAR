@extends('layouts.app')

@section('title', 'Edit UAM Record')

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
    <x-sidebar />

    <main class="flex-grow-1 page-content px-4">

                <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Request Access Matrix', 'url' => route('access-matrix.request.index')],
            ['label' => 'UAM SAP', 'url' => route('access-matrix.request.sap')],
            ['label' => 'Edit Record'],
        ]" />

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in">
            <div>
                <h1 style="font-size:1.35rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                    <i class="bi bi-pencil-square me-2" style="color:var(--primary);"></i>Edit UAM Record
                </h1>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                    Editing record ID <strong>#{{ $uamRecord->id }}</strong> &nbsp;·&nbsp;
                    <span style="font-family:monospace;background:#f1f5f9;padding:.15rem .35rem;border-radius:4px;font-size:.78rem;border:1px solid var(--border);">{{ $uamRecord->role }}</span>
                </p>
            </div>
            <a href="{{ route('access-matrix.sap', ['search' => $uamRecord->role]) }}"
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

                    <form method="POST" action="{{ route('access-matrix.update', $uamRecord->id) }}" id="editForm">
                        @csrf
                        @method('PUT')

                        {{-- Role --}}
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                Role <span style="color:var(--primary);">*</span>
                            </label>
                            <input type="text" id="role" name="role"
                                   class="form-control @error('role') is-invalid @enderror"
                                   value="{{ old('role', $uamRecord->role) }}"
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
                                      placeholder="Describe what this role is for…">{{ old('description_role', $uamRecord->description_role) }}</textarea>
                            @error('description_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- Module --}}
                            <div class="col-12 col-sm-6">
                                <label for="module" class="form-label">
                                    Module <span style="color:var(--primary);">*</span>
                                </label>
                                <input type="text" id="module" name="module"
                                       class="form-control @error('module') is-invalid @enderror"
                                       value="{{ old('module', $uamRecord->module) }}"
                                       placeholder="e.g. PS" required>
                                @error('module')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Period --}}
                            <div class="col-12 col-sm-6">
                                <label for="period" class="form-label">
                                    Period <span style="color:var(--primary);">*</span>
                                </label>
                                <select id="period" name="period"
                                        class="form-select @error('period') is-invalid @enderror"
                                        required>
                                    <option value="" disabled>-- Select Period --</option>
                                    <option value="Q1" {{ old('period', $uamRecord->period) == 'Q1' ? 'selected' : '' }}>Q1 (First Period)</option>
                                    <option value="Q2" {{ old('period', $uamRecord->period) == 'Q2' ? 'selected' : '' }}>Q2 (Second Period)</option>
                                    <option value="Q3" {{ old('period', $uamRecord->period) == 'Q3' ? 'selected' : '' }}>Q3 (Third Period)</option>
                                </select>
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- TCODE --}}
                            <div class="col-12 col-sm-6">
                                <label for="tcode" class="form-label">TCODE</label>
                                <input type="text" id="tcode" name="tcode"
                                       class="form-control @error('tcode') is-invalid @enderror"
                                       value="{{ old('tcode', $uamRecord->tcode) }}"
                                       placeholder="e.g. SU01"
                                       style="font-family:monospace;">
                                @error('tcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        <div class="row g-3 mb-3">
                            {{-- BPO — LEFT --}}
                            <div class="col-12 col-sm-6">
                                <label for="bpo" class="form-label">BPO</label>
                                <select id="bpo" name="bpo" class="form-select @error('bpo') is-invalid @enderror" data-selected="{{ old('bpo', $uamRecord->bpo) }}">
                                    <option value="">-- Type a TCODE first --</option>
                                </select>
                                @error('bpo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- UNIT — RIGHT --}}
                            <div class="col-12 col-sm-6">
                                <label for="unit" class="form-label">UNIT</label>
                                <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror" data-selected="{{ old('unit', $uamRecord->unit) }}">
                                    <option value="">-- Select BPO first --</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- Application Owner --}}
                            <div class="col-12 col-sm-6">
                                <label for="access_owner" class="form-label">User Access Matrix (AO)</label>
                                <select id="access_owner" name="access_owner" class="form-select @error('access_owner') is-invalid @enderror" data-selected="{{ old('access_owner', $uamRecord->access_owner) }}">
                                    <option value="">-- Select Unit first --</option>
                                </select>
                                @error('access_owner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <button type="submit" id="saveBtn" class="btn-primary-custom"
                                    style="width:auto;padding:.65rem 2rem;font-size:.9rem;display:inline-flex;align-items:center;gap:.5rem;">
                                <i class="bi bi-check-lg"></i> Save Changes
                            </button>
                            <a href="{{ route('access-matrix.sap', ['search' => $uamRecord->role]) }}"
                               style="font-size:.85rem;color:var(--text-muted);text-decoration:none;font-weight:500;transition:color var(--transition);"
                               onmouseenter="this.style.color='var(--primary)'"
                               onmouseleave="this.style.color='var(--text-muted)'">
                                Cancel
                            </a>

                            {{-- Danger zone: delete this record --}}
                            <div class="ms-auto">
                                <form method="POST" action="{{ route('access-matrix.destroy', $uamRecord->id) }}"
                                      onsubmit="return confirm('Delete this record?\nRole: {{ addslashes($uamRecord->role) }}\nTCODE: {{ addslashes($uamRecord->tcode ?? '—') }}\n\nThis cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="display:inline-flex;align-items:center;gap:.4rem;background:none;border:1.5px solid #fecaca;border-radius:8px;padding:.5rem 1rem;font-size:.82rem;font-weight:600;color:#dc2626;cursor:pointer;transition:all var(--transition);"
                                        onmouseenter="this.style.background='#fef2f2';this.style.borderColor='#dc2626';"
                                        onmouseleave="this.style.background='none';this.style.borderColor='#fecaca';">
                                        <i class="bi bi-trash-fill"></i> Delete Record
                                    </button>
                                </form>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            {{-- Record meta info --}}
            <div style="margin-top:1rem;padding:.75rem 1rem;background:#f8f9fa;border-radius:10px;border:1px solid var(--border);font-size:.75rem;color:var(--text-muted);display:flex;gap:1.5rem;flex-wrap:wrap;">
                <span><i class="bi bi-calendar3 me-1"></i> Created: {{ $uamRecord->created_at?->format('d M Y, H:i') ?? '—' }}</span>
                <span><i class="bi bi-pencil me-1"></i> Last updated: {{ $uamRecord->updated_at?->format('d M Y, H:i') ?? '—' }}</span>
                <span><i class="bi bi-hash me-1"></i> Record ID: #{{ $uamRecord->id }}</span>
            </div>
        </div>

    </main>
</div>

@endsection

@push('scripts')
<script>
    // Profile dropdown handled globally by Bootstrap

    document.getElementById('editForm').addEventListener('submit', function () {
        const btn = document.getElementById('saveBtn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
    });

    // ── Dynamic Dropdowns logic ──────────────────────────────────────
    let globalMatrix = {};
    const requestId = '{{ $uamRecord->request_id ?? "" }}';

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

    const tcodeEl = document.getElementById('tcode');
    const bpoSelect = document.getElementById('bpo');
    const unitSelect = document.getElementById('unit');
    const aoSelect = document.getElementById('access_owner');

    tcodeEl.addEventListener('input', refreshDropdowns);
    bpoSelect.addEventListener('change', refreshDropdowns);
    unitSelect.addEventListener('change', refreshDropdowns);

    function refreshDropdowns(e) {
        const tc = tcodeEl.value.trim();

        // Get selected values or initial data-selected attribute if currently empty
        const selectedBpo = bpoSelect.value || bpoSelect.dataset.selected;
        const selectedUnit = unitSelect.value || unitSelect.dataset.selected;
        const selectedAo = aoSelect.value || aoSelect.dataset.selected;

        if (!tc) {
            setOptions(bpoSelect, [], '-- Type a TCODE first --');
            setOptions(unitSelect, [], '-- Select BPO first --');
            setOptions(aoSelect, [], '-- Select Unit first --');
            return;
        }

        const map = globalMatrix[tc] || {};
        const validBpos = Object.keys(map);

        if (validBpos.length === 0) {
            setOptions(bpoSelect, [], '-- No BPOs found for TCODE --');
            setOptions(unitSelect, [], '-- Select BPO first --');
            setOptions(aoSelect, [], '-- Select Unit first --');
            return;
        }

        setOptions(bpoSelect, validBpos, '-- Select BPO --', selectedBpo);

        // Clear data-selected after initial set
        bpoSelect.removeAttribute('data-selected');

        if (!bpoSelect.value) {
            setOptions(unitSelect, [], '-- Select BPO first --');
            setOptions(aoSelect, [], '-- Select Unit first --');
            return;
        }

        const validUnits = Object.keys(map[bpoSelect.value] || {});
        if (validUnits.length === 0) {
            setOptions(unitSelect, [], '-- No Units found --');
            setOptions(aoSelect, [], '-- Select Unit first --');
            return;
        }

        setOptions(unitSelect, validUnits, '-- Select Unit --', selectedUnit);
        unitSelect.removeAttribute('data-selected');

        if (!unitSelect.value) {
            setOptions(aoSelect, [], '-- Select Unit first --');
            return;
        }

        const validAos = map[bpoSelect.value][unitSelect.value] || [];
        if (validAos.length === 0) {
            setOptions(aoSelect, [], '-- No Application Owners found --');
            return;
        }

        setOptions(aoSelect, validAos, '-- Select Application Owner --', selectedAo);
        aoSelect.removeAttribute('data-selected');
    }

    function setOptions(selectEl, optionsArray, placeholder, selectedValue = null) {
        selectEl.innerHTML = `<option value="">${placeholder}</option>`;
        let valueFound = false;
        optionsArray.sort().forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            option.textContent = opt;
            if (opt === selectedValue) {
                option.selected = true;
                valueFound = true;
            }
            selectEl.appendChild(option);
        });
        if (!valueFound && optionsArray.length > 0) {
            selectEl.value = "";
        }
        selectEl.disabled = optionsArray.length === 0;
    }

</script>
@endpush




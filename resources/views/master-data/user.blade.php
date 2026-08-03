@extends('layouts.app')

@section('title', 'Master User')

@section('content')
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<x-sidebar />

<div class="app-content-wrapper">
    <header class="app-topbar">
        <div class="topbar-left">
            <button class="btn-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <x-navbar-right />
    </header>

    <main class="flex-grow-1 page-content px-4">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Master Data'],
            ['label' => 'User'],
        ]" />

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 animate-in">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;letter-spacing:-.5px;">Master User</h1>
                <p style="font-size:.88rem;color:var(--text-muted);margin:0;">Manage User master data used in Add Role.</p>
            </div>
            <button onclick="openAddModal()"
                style="display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,var(--primary),#ff5c62);color:#fff;border:none;border-radius:12px;padding:.6rem 1.3rem;font-size:.88rem;font-weight:700;cursor:pointer;box-shadow:0 4px 14px var(--primary-glow);transition:all var(--transition);"
                onmouseenter="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px var(--primary-glow)'"
                onmouseleave="this.style.transform='';this.style.boxShadow='0 4px 14px var(--primary-glow)'">
                <i class="bi bi-plus-lg"></i> Add User
            </button>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="animate-in" style="background:#dcfce7;border-left:4px solid #16a34a;border-radius:10px;color:#166534;font-size:.875rem;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="animate-in" style="background:var(--primary-light);border-left:4px solid var(--primary);border-radius:10px;color:#7b0d0f;font-size:.875rem;padding:.75rem 1rem;margin-bottom:1.25rem;">
                <ul style="margin:0;padding-left:1.2rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Table --}}
        <div class="card-premium animate-in animate-in-delay-1">
            <div class="card-header-premium d-flex align-items-center justify-content-between">
                <span style="font-size:.9rem;font-weight:800;color:var(--secondary);">
                    <i class="bi bi-people-fill me-2" style="color:var(--primary);"></i>User List
                </span>
                <span style="font-size:.78rem;color:var(--text-muted);">Total: {{ $users->count() }} records</span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                    <thead>
                        <tr style="background:var(--secondary-light);">
                            <th style="padding:.75rem 1.25rem;text-align:left;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);width:56px;">#</th>
                            <th style="padding:.75rem 1.25rem;text-align:left;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);">User Name</th>
                            <th style="padding:.75rem 1.25rem;text-align:right;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr style="border-top:1px solid var(--border);transition:background var(--transition);"
                            onmouseenter="this.style.background='var(--secondary-light)'"
                            onmouseleave="this.style.background=''">
                            <td style="padding:.9rem 1.25rem;color:var(--text-muted);font-size:.8rem;">{{ $loop->iteration }}</td>
                            <td style="padding:.9rem 1.25rem;font-weight:700;color:var(--secondary);">{{ $user->name }}</td>
                            <td style="padding:.9rem 1.25rem;text-align:right;">
                                <form method="POST" action="{{ route('master-data.user.destroy', $user) }}"
                                      onsubmit="return confirm('Delete User {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        style="display:inline-flex;align-items:center;gap:.35rem;background:#fef2f2;border:1.5px solid #fecaca;border-radius:8px;padding:.38rem .8rem;font-size:.78rem;font-weight:700;color:#dc2626;cursor:pointer;transition:all var(--transition);"
                                        onmouseenter="this.style.background='#dc2626';this.style.color='#fff';this.style.borderColor='#dc2626'"
                                        onmouseleave="this.style.background='#fef2f2';this.style.color='#dc2626';this.style.borderColor='#fecaca'">
                                        <i class="bi bi-trash-fill"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="padding:3rem;text-align:center;color:var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
                                No User data yet. Click <strong>Add User</strong> to add new records.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

{{-- Add Modal --}}
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:18px;padding:2rem;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:scaleIn .25s ease;">
        <h3 style="font-size:1.1rem;font-weight:800;color:var(--secondary);margin:0 0 1.25rem;">Add New User</h3>
        <form method="POST" action="{{ route('master-data.user.store') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label">User Name <span style="color:var(--primary);">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. OFF 1 OPTICAL TRANSPORT PLANNING" required autofocus>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="closeAddModal()"
                    style="background:none;border:1.5px solid var(--border);border-radius:10px;padding:.5rem 1.25rem;font-size:.875rem;font-weight:600;color:var(--text-muted);cursor:pointer;">Cancel</button>
                <button type="submit" class="btn-primary-custom" style="width:auto;padding:.5rem 1.5rem;font-size:.875rem;">Save</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddModal()  { document.getElementById('addModal').style.display = 'flex'; }
    function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

    document.getElementById('addModal').addEventListener('click', function(e) { if(e.target===this) closeAddModal(); });
</script>
@endpush

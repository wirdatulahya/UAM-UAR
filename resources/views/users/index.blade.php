@extends('layouts.app')

@section('title', 'User Management — Administration')

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('dashboard') }}" class="navbar-brand-wrapper">
                    <div class="brand-dot"><i class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <div class="brand-text-main">AccessHub</div>
                        <div class="brand-text-sub">PT Telkom Infrastruktur Indonesia</div>
                    </div>
                </a>
            </div>
            <x-navbar-right />
        </div>
    </div>
</nav>

{{-- ─── App Shell ──────────────────────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    <x-sidebar />

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

        <div class="d-flex align-items-center justify-content-between mb-4 mt-3">
            <div>
                <h1 style="font-size:1.4rem;font-weight:700;color:var(--text);margin-bottom:.3rem;letter-spacing:-.01em;">User Management</h1>
                <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Manage user accounts, roles, and access.</p>
            </div>
            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus-fill"></i> Add New User
            </button>
        </div>

        {{-- Search and Table Card --}}
        <div class="animate-in animate-in-delay-1" style="background:#fff;border:1.5px solid var(--border);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.02);overflow:hidden;">
            
            {{-- Search Bar --}}
            <div style="padding: 1.25rem; border-bottom: 1.5px solid var(--border);">
                <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2">
                    <div class="input-group" style="max-width: 400px;">
                        <span class="input-group-text bg-white" style="border:1.5px solid var(--border); border-right:none; color:var(--text-muted);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, username, or email..." value="{{ $search }}" style="border:1.5px solid var(--border); border-left:none; box-shadow:none;">
                    </div>
                    <button type="submit" class="btn btn-outline-secondary" style="border-width:1.5px;">Search</button>
                    @if($search)
                        <a href="{{ route('users.index') }}" class="btn btn-link text-muted text-decoration-none">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.825rem;width:100%;table-layout:fixed;border-collapse:collapse;">
                    <thead style="background:#f8fafc;border-bottom:1.5px solid var(--border);">
                        <tr>
                            <th style="padding:.75rem 1.25rem;font-weight:700;color:var(--text-muted);width:250px;">Full Name</th>
                            <th style="padding:.75rem 1rem;font-weight:700;color:var(--text-muted);width:150px;">Username</th>
                            <th style="padding:.75rem 1rem;font-weight:700;color:var(--text-muted);width:200px;">Email</th>
                            <th style="padding:.75rem 1rem;font-weight:700;color:var(--text-muted);width:120px;">System Role</th>
                            <th style="padding:.75rem 1rem;font-weight:700;color:var(--text-muted);width:100px;">Status</th>
                            <th style="padding:.75rem 1rem;font-weight:700;color:var(--text-muted);width:130px;">Last Login</th>
                            <th style="padding:.75rem 1rem;font-weight:700;color:var(--text-muted);text-align:right;width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr style="border-bottom:1px solid #f1f5f9;transition:background .2s;" onmouseenter="this.style.background='#f8fafc'" onmouseleave="this.style.background='transparent'">
                            <td style="padding:.75rem 1.25rem;vertical-align:middle;color:var(--text);font-weight:500;">
                                <div class="d-flex align-items-center gap-2">
                                    @if($user->profile_photo_path)
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                                    @else
                                        <div style="width:28px;height:28px;border-radius:50%;background:var(--secondary-light);color:var(--secondary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.65rem;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <span class="text-truncate" style="max-width:180px;">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td style="padding:.75rem 1rem;vertical-align:middle;color:var(--text-muted);">{{ $user->username }}</td>
                            <td style="padding:.75rem 1rem;vertical-align:middle;color:var(--text-muted);text-overflow:ellipsis;overflow:hidden;white-space:nowrap;" title="{{ $user->email }}">{{ $user->email }}</td>
                            <td style="padding:.75rem 1rem;vertical-align:middle;">
                                <span style="background:var(--secondary-light);color:var(--secondary);padding:.2rem .5rem;border-radius:6px;font-size:.7rem;font-weight:600;">
                                    {{ strtoupper(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td style="padding:.75rem 1rem;vertical-align:middle;">
                                @if($user->account_status === 'Active')
                                    <span style="color:#10b981;font-weight:600;font-size:.75rem;display:flex;align-items:center;gap:.3rem;">
                                        <i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Active
                                    </span>
                                @else
                                    <span style="color:#ef4444;font-weight:600;font-size:.75rem;display:flex;align-items:center;gap:.3rem;">
                                        <i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td style="padding:.75rem 1rem;vertical-align:middle;color:var(--text-muted);font-size:.75rem;">
                                {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}
                            </td>
                            <td style="padding:.75rem 1rem;vertical-align:middle;text-align:right;">
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size:.8rem;border-radius:12px;">
                                        <li>
                                            <button type="button" class="dropdown-item" onclick="openEditModal({{ json_encode($user) }})">
                                                <i class="bi bi-pencil me-2"></i> Edit User
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item" onclick="openResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                <i class="bi bi-key me-2"></i> Reset Password
                                            </button>
                                        </li>
                                        <li>
                                            <form action="{{ route('users.toggle-status', $user) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    @if($user->account_status === 'Active')
                                                        <i class="bi bi-person-fill-slash me-2 text-warning"></i> Deactivate
                                                    @else
                                                        <i class="bi bi-person-fill-check me-2 text-success"></i> Activate
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i> Delete User
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding:2rem;text-align:center;color:var(--text-muted);">
                                <i class="bi bi-people" style="font-size:2rem;opacity:.5;"></i>
                                <div class="mt-2">No users found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if ($users->hasPages())
                <div style="padding: 1rem 1.25rem; border-top: 1.5px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fff;">
                    <div style="font-size: .8rem; color: var(--text-muted);">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                    </div>
                    <div class="d-flex gap-1">
                        @if ($users->onFirstPage())
                            <span class="btn btn-sm btn-outline-secondary disabled" style="border-width:1.5px;"><i class="bi bi-chevron-left"></i></span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary" style="border-width:1.5px;"><i class="bi bi-chevron-left"></i></a>
                        @endif

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary" style="border-width:1.5px;"><i class="bi bi-chevron-right"></i></a>
                        @else
                            <span class="btn btn-sm btn-outline-secondary disabled" style="border-width:1.5px;"><i class="bi bi-chevron-right"></i></span>
                        @endif
                    </div>
                </div>
            @endif

        </div>

    </main>
</div>

{{-- ─── Create User Modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 10px 30px rgba(0,0,0,.1);">
            <div class="modal-header" style="border-bottom:1.5px solid var(--border);padding:1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight:700;font-size:1.1rem;letter-spacing:-.01em;">
                    <i class="bi bi-person-plus-fill me-2" style="color:var(--secondary);"></i> Create New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:1.5rem;">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">System Role</label>
                        <select name="role" class="form-select" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="ao">Application Owner (AO)</option>
                            <option value="pic_ao">PIC AO</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1.5px solid var(--border);padding:1rem 1.5rem;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-weight:600;border:1.5px solid var(--border);">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" style="font-weight:600;"><i class="bi bi-save me-1"></i> Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Edit User Modal ───────────────────────────────────────────── --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 10px 30px rgba(0,0,0,.1);">
            <div class="modal-header" style="border-bottom:1.5px solid var(--border);padding:1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight:700;font-size:1.1rem;letter-spacing:-.01em;">
                    <i class="bi bi-pencil-square me-2" style="color:var(--secondary);"></i> Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding:1.5rem;">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Full Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Username</label>
                        <input type="text" name="username" id="editUsername" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Email Address</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">System Role</label>
                        <select name="role" id="editRole" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="ao">Application Owner (AO)</option>
                            <option value="pic_ao">PIC AO</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1.5px solid var(--border);padding:1rem 1.5rem;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-weight:600;border:1.5px solid var(--border);">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" style="font-weight:600;"><i class="bi bi-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Reset Password Modal ──────────────────────────────────────── --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 10px 30px rgba(0,0,0,.1);">
            <div class="modal-header" style="border-bottom:1.5px solid var(--border);padding:1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight:700;font-size:1.1rem;letter-spacing:-.01em;">
                    <i class="bi bi-key-fill me-2" style="color:var(--secondary);"></i> Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="modal-body" style="padding:1.5rem;">
                    
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem;">
                        Resetting password for: <strong id="resetUserName" style="color:var(--text);"></strong>
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">New Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1.5px solid var(--border);padding:1rem 1.5rem;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-weight:600;border:1.5px solid var(--border);">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" style="font-weight:600;"><i class="bi bi-check-lg me-1"></i> Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditModal(user) {
        document.getElementById('editName').value = user.name;
        document.getElementById('editUsername').value = user.username;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editRole').value = user.role;
        document.getElementById('editUserForm').action = `/users/${user.id}`;
        
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }

    function openResetPasswordModal(userId, userName) {
        document.getElementById('resetUserName').textContent = userName;
        document.getElementById('resetPasswordForm').action = `/users/${userId}/reset-password`;
        
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    }
</script>
@endpush

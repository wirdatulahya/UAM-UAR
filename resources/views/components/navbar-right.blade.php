<div class="d-flex align-items-center gap-3">
    @php
        $unreadCount = Auth::user()->unreadNotifications->count();
        $notifications = Auth::user()->notifications()->take(5)->get();
    @endphp

    {{-- Notification Bell --}}
    <div class="dropdown" id="notificationDropdownWrapper">
        <button type="button" class="position-relative" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside" style="background:none;border:none;color:var(--text-muted);font-size:1.2rem;padding:0;transition:color var(--transition);" onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">
            <i class="bi bi-bell"></i>
            @if($unreadCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem;padding:.2rem .4rem;margin-top:5px;margin-left:-5px;">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>

        {{-- Notification Dropdown Menu --}}
        <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow" style="width:320px;border-radius:14px;z-index:200;overflow:hidden;margin-top:12px;">
            <div style="padding:1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--secondary-light);">
                <div style="font-size:.85rem;font-weight:700;color:var(--secondary);">Notifications</div>
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.mark-all') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" style="background:none;border:none;padding:0;color:var(--primary);font-size:.7rem;font-weight:600;cursor:pointer;">Mark all as read</button>
                    </form>
                @endif
            </div>

            <div style="max-height:300px;overflow-y:auto;">
                @forelse($notifications as $notification)
                    <a href="{{ $notification->data['url'] ?? '#' }}" class="d-flex align-items-start gap-3 p-3 notification-item dropdown-item text-wrap" style="text-decoration:none;border-bottom:1px solid var(--border);background:{{ $notification->read_at ? '#fff' : '#f8f9fa' }};transition:background .2s;white-space:normal;" data-id="{{ $notification->id }}" onclick="markNotificationAsRead('{{ $notification->id }}')">
                        <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi {{ $notification->data['icon'] ?? 'bi-bell-fill' }}" style="color:var(--primary);font-size:1rem;"></i>
                        </div>
                        <div style="flex-grow:1;">
                            <div style="font-size:.8rem;font-weight:600;color:var(--secondary);margin-bottom:.15rem;">{{ $notification->data['title'] }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);line-height:1.3;margin-bottom:.3rem;">{{ $notification->data['description'] }}</div>
                            <div style="font-size:.65rem;color:#adb5bd;font-weight:500;">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        @if(!$notification->read_at)
                            <div style="width:8px;height:8px;border-radius:50%;background:var(--danger);margin-top:.4rem;flex-shrink:0;"></div>
                        @endif
                    </a>
                @empty
                    <div style="padding:2rem 1rem;text-align:center;color:var(--text-muted);">
                        <i class="bi bi-bell-slash" style="font-size:2rem;color:var(--border);margin-bottom:.5rem;display:block;"></i>
                        <div style="font-size:.8rem;font-weight:500;">No new notifications</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    {{-- Divider --}}
    <div style="width:1px;height:24px;background:var(--border);"></div>

    {{-- Profile Dropdown --}}
    <div class="dropdown" id="profileDropdownWrapper">
        <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
            style="background:none;border:none;padding:0;display:flex;align-items:center;gap:.65rem;cursor:pointer;transition:opacity var(--transition);" onmouseenter="this.style.opacity='0.8'" onmouseleave="this.style.opacity='1'">
            <div style="width:36px;height:36px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                @if(Auth::user()->profile_photo_path)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i class="bi bi-person-fill" style="color:#fff;font-size:1.1rem;"></i>
                @endif
            </div>
            <div class="d-none d-sm-flex align-items-center gap-2">
                <span style="font-size:.85rem;font-weight:700;color:var(--secondary);text-transform:uppercase;">{{ Auth::user()->name }}</span>
                <i class="bi bi-chevron-down" style="font-size:.7rem;color:var(--text-muted);"></i>
            </div>
        </button>

        {{-- Dropdown Menu --}}
        <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow" style="width:200px;border-radius:14px;z-index:200;overflow:hidden;margin-top:12px;">

            {{-- User Info Header --}}
            <div style="padding:.85rem 1rem .75rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                <div style="font-size:.8rem;font-weight:700;color:var(--secondary);">{{ Auth::user()->name }}</div>
                <div style="font-size:.7rem;color:var(--text-muted);">{{ Auth::user()->email }}</div>
            </div>

            {{-- Profile (active) --}}
            <a href="{{ route('profile.index') }}" class="dropdown-item"
                style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--secondary);background:var(--secondary-light);">
                <i class="bi bi-person" style="font-size:1.05rem;"></i> My Profile
            </a>


            {{-- Logout --}}
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button id="logoutBtn" type="submit" class="dropdown-item"
                    style="width:100%;text-align:left;background:none;border:none;border-top:1px solid var(--border);padding:.72rem 1rem;font-size:.85rem;font-weight:600;color:#dc3545;display:flex;align-items:center;gap:.65rem;cursor:pointer;transition:background var(--transition);"
                    onmouseenter="this.style.background='#fff5f5';"
                    onmouseleave="this.style.background='none';">
                    <i class="bi bi-box-arrow-right" style="font-size:1.05rem;"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    if (!window.profileDropdownInitialized) {
        window.profileDropdownInitialized = true;
        
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForm = document.getElementById('logoutForm');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function () {
                    const btn = document.getElementById('logoutBtn');
                    if (btn) {
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Signing Out...';
                        btn.disabled = true;
                    }
                });
            }
        });

        // Mark single notification as read via AJAX
        function markNotificationAsRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => response.json()).then(data => {
                // Ignore errors, we're navigating away anyway
            });
        }
    }
</script>
@endpush

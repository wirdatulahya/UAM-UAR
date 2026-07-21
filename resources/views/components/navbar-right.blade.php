<div class="d-flex align-items-center gap-3">
    @php
        $unreadCount = Auth::user()->unreadNotifications->count();
        $notifications = Auth::user()->notifications()->take(5)->get();
    @endphp

    {{-- Notification Bell --}}
    <div class="position-relative" id="notificationDropdownWrapper">
        <button type="button" id="notificationDropdownBtn" class="position-relative" style="background:none;border:none;color:var(--text-muted);font-size:1.2rem;padding:0;transition:color var(--transition);" onmouseenter="this.style.color='var(--secondary)'" onmouseleave="this.style.color='var(--text-muted)'">
            <i class="bi bi-bell"></i>
            @if($unreadCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem;padding:.2rem .4rem;margin-top:5px;margin-left:-5px;">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>

        {{-- Notification Dropdown Menu --}}
        <div id="notificationDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 12px);width:320px;background:#fff;border:1.5px solid var(--border);border-radius:14px;box-shadow:0 8px 32px rgba(11,46,109,.13);z-index:200;overflow:hidden;">
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
                    <a href="{{ $notification->data['url'] ?? '#' }}" class="d-flex align-items-start gap-3 p-3 notification-item" style="text-decoration:none;border-bottom:1px solid var(--border);background:{{ $notification->read_at ? '#fff' : '#f8f9fa' }};transition:background .2s;" data-id="{{ $notification->id }}" onclick="markNotificationAsRead('{{ $notification->id }}')">
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
    <div class="position-relative" id="profileDropdownWrapper">
        <button id="profileDropdownBtn" type="button"
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
                <i class="bi bi-chevron-down" id="profileChevron" style="font-size:.7rem;color:var(--text-muted);transition:transform var(--transition);"></i>
            </div>
        </button>

        {{-- Dropdown Menu --}}
        <div id="profileDropdownMenu"
            style="display:none;position:absolute;right:0;top:calc(100% + 12px);width:200px;background:#fff;border:1.5px solid var(--border);border-radius:14px;box-shadow:0 8px 32px rgba(11,46,109,.13);z-index:200;overflow:hidden;">

            {{-- User Info Header --}}
            <div style="padding:.85rem 1rem .75rem;border-bottom:1px solid var(--border);background:var(--secondary-light);">
                <div style="font-size:.8rem;font-weight:700;color:var(--secondary);">{{ Auth::user()->name }}</div>
                <div style="font-size:.7rem;color:var(--text-muted);">{{ Auth::user()->email }}</div>
            </div>

            {{-- Profile (active) --}}
            <a href="{{ route('profile.index') }}"
                style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--secondary);background:var(--secondary-light);text-decoration:none;">
                <i class="bi bi-person" style="font-size:1.05rem;"></i> My Profile
            </a>

            {{-- Change Password --}}
            <a href="{{ route('password.change') }}"
                style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--text-muted);text-decoration:none;transition:background var(--transition);"
                onmouseenter="this.style.background='var(--secondary-light)';this.style.color='var(--secondary)';"
                onmouseleave="this.style.background='none';this.style.color='var(--text-muted)';">
                <i class="bi bi-key" style="font-size:1.05rem;"></i> Change Password
            </a>

            {{-- Logout --}}
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button id="logoutBtn" type="submit"
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
            // Profile Dropdown
            const profileBtn  = document.getElementById('profileDropdownBtn');
            const profileMenu = document.getElementById('profileDropdownMenu');
            const chevron     = document.getElementById('profileChevron');
            const logoutForm  = document.getElementById('logoutForm');

            // Notification Dropdown
            const notifBtn  = document.getElementById('notificationDropdownBtn');
            const notifMenu = document.getElementById('notificationDropdownMenu');

            if (profileBtn && profileMenu) {
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = profileMenu.style.display === 'block';
                    profileMenu.style.display = isOpen ? 'none' : 'block';
                    if (notifMenu) notifMenu.style.display = 'none'; // Close other
                    if (chevron) chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
                });
            }

            if (notifBtn && notifMenu) {
                notifBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = notifMenu.style.display === 'block';
                    notifMenu.style.display = isOpen ? 'none' : 'block';
                    if (profileMenu) profileMenu.style.display = 'none'; // Close other
                    if (chevron) chevron.style.transform = '';
                });
            }

            document.addEventListener('click', function () {
                if (profileMenu) profileMenu.style.display = 'none';
                if (notifMenu) notifMenu.style.display = 'none';
                if (chevron) chevron.style.transform = '';
            });

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

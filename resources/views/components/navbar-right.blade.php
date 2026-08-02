<div class="d-flex align-items-center gap-3">
    @php
        $unreadCount = Auth::user()->unreadNotifications->count();
        $notifications = Auth::user()->notifications()->take(5)->get();
    @endphp

    @if(!Auth::user()->requires_onboarding)
    {{-- Notification Bell --}}
    <div class="dropdown" id="notificationDropdownWrapper">
        <button type="button"
            class="position-relative"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            data-bs-auto-close="outside"
            style="background:var(--surface-alt,#f7f8fc);border:1.5px solid var(--border);border-radius:10px;color:var(--text-muted);font-size:1.05rem;padding:.42rem .6rem;transition:all var(--transition);line-height:1;cursor:pointer;"
            onmouseenter="this.style.borderColor='var(--secondary)';this.style.color='var(--secondary)';this.style.background='var(--secondary-light)'"
            onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)';this.style.background='var(--surface-alt,#f7f8fc)'">
            <i class="bi bi-bell"></i>
            @if($unreadCount > 0)
                <span class="position-absolute"
                      style="top:-4px;right:-4px;width:16px;height:16px;background:var(--primary);border-radius:50%;font-size:.58rem;font-weight:800;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff;animation:pulse-glow 2s infinite;">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </button>

        {{-- Notification Dropdown --}}
        <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg"
             style="width:330px;border-radius:18px;z-index:300;overflow:hidden;margin-top:10px;box-shadow:0 20px 60px rgba(11,46,109,.15),0 4px 16px rgba(0,0,0,.08)!important;">

            {{-- Header --}}
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,var(--secondary-light),#fff);">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <i class="bi bi-bell-fill" style="color:var(--secondary);font-size:.9rem;"></i>
                    <span style="font-size:.85rem;font-weight:700;color:var(--secondary);">Notifications</span>
                    @if($unreadCount > 0)
                        <span style="background:var(--primary);color:#fff;border-radius:99px;padding:.1rem .45rem;font-size:.65rem;font-weight:800;">{{ $unreadCount }}</span>
                    @endif
                </div>
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.mark-all') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" style="background:none;border:none;padding:0;color:var(--secondary);font-size:.72rem;font-weight:700;cursor:pointer;text-decoration:underline;">
                            Mark all read
                        </button>
                    </form>
                @endif
            </div>

            {{-- Notification List --}}
            <div style="max-height:290px;overflow-y:auto;background:#fff;">
                @forelse($notifications as $notification)
                    <a href="{{ $notification->data['url'] ?? '#' }}"
                       class="d-flex align-items-start gap-3 dropdown-item text-wrap"
                       style="text-decoration:none;padding:.85rem 1.25rem;border-bottom:1px solid var(--border);background:{{ $notification->read_at ? '#fff' : 'linear-gradient(135deg,#f0f5ff,#fff)' }};transition:background var(--transition);white-space:normal;"
                       data-id="{{ $notification->id }}"
                       onclick="markNotificationAsRead('{{ $notification->id }}')"
                       onmouseenter="this.style.background='var(--secondary-light)'"
                       onmouseleave="this.style.background='{{ $notification->read_at ? '#fff' : 'linear-gradient(135deg,#f0f5ff,#fff)' }}'">
                        <div style="width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--primary-light),#ffc1c2);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px var(--primary-glow);">
                            <i class="bi {{ $notification->data['icon'] ?? 'bi-bell-fill' }}" style="color:var(--primary);font-size:.95rem;"></i>
                        </div>
                        <div style="flex-grow:1;min-width:0;">
                            <div style="font-size:.8rem;font-weight:700;color:var(--secondary);margin-bottom:.15rem;white-space:normal;">{{ $notification->data['title'] }}</div>
                            <div style="font-size:.74rem;color:var(--text-muted);line-height:1.4;margin-bottom:.3rem;white-space:normal;">{{ $notification->data['description'] }}</div>
                            <div style="font-size:.65rem;color:var(--text-light);font-weight:600;">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        @if(!$notification->read_at)
                            <div style="width:8px;height:8px;border-radius:50%;background:var(--primary);margin-top:.5rem;flex-shrink:0;box-shadow:0 0 6px var(--primary-glow);"></div>
                        @endif
                    </a>
                @empty
                    <div style="padding:2.5rem 1rem;text-align:center;color:var(--text-muted);">
                        <div style="width:52px;height:52px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                            <i class="bi bi-bell-slash" style="font-size:1.5rem;color:var(--text-light);"></i>
                        </div>
                        <div style="font-size:.82rem;font-weight:600;color:var(--text-muted);">All caught up!</div>
                        <div style="font-size:.75rem;color:var(--text-light);margin-top:.2rem;">No new notifications</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="divider-vertical"></div>
    @endif

    {{-- Profile Dropdown --}}
    <div class="dropdown" id="profileDropdownWrapper">
        <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
            style="background:none;border:none;padding:0;display:flex;align-items:center;gap:.65rem;cursor:pointer;">
            {{-- Avatar with gradient ring --}}
            <div style="padding:2px;background:linear-gradient(135deg,var(--primary),#1a4d9e);border-radius:50%;transition:transform var(--transition-slow),box-shadow var(--transition-slow);"
                 onmouseenter="this.style.transform='scale(1.08)';this.style.boxShadow='0 4px 16px var(--primary-glow)'"
                 onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div style="width:34px;height:34px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;border:2px solid #fff;">
                    @if(Auth::user()->profile_photo_path)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <span style="font-size:.82rem;font-weight:800;color:#fff;line-height:1;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    @endif
                </div>
            </div>
            <div class="d-none d-sm-flex align-items-center">
                <span style="font-size:.82rem;font-weight:700;color:var(--secondary);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Auth::user()->name }}</span>
            </div>
            <i class="bi bi-chevron-down d-none d-sm-block" style="font-size:.65rem;color:var(--text-muted);"></i>
        </button>

        {{-- Dropdown Menu --}}
        <div class="dropdown-menu dropdown-menu-end p-0 border-0"
             style="width:220px;border-radius:18px;z-index:300;overflow:hidden;margin-top:10px;box-shadow:0 20px 60px rgba(11,46,109,.15),0 4px 16px rgba(0,0,0,.08);">

            {{-- User Info --}}
            <div style="padding:1.1rem 1.25rem;border-bottom:1px solid var(--border);background:linear-gradient(135deg,var(--secondary-light),#fff);">
                <div style="display:flex;align-items:center;gap:.65rem;">
                    <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--primary),#ff5c62);display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 4px 12px var(--primary-glow);overflow:hidden;">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div style="min-width:0;">
                        <div style="font-size:.82rem;font-weight:700;color:var(--secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->name }}</div>
                        <div style="font-size:.68rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            {{-- Profile Link --}}
            <a href="{{ route('profile.index') }}"
               class="dropdown-item"
               style="display:flex;align-items:center;gap:.65rem;padding:.78rem 1.25rem;font-size:.84rem;font-weight:500;color:var(--secondary);transition:background var(--transition),color var(--transition);"
               onmouseenter="this.style.background='var(--secondary-light)'"
               onmouseleave="this.style.background=''">
                <div style="width:30px;height:30px;border-radius:9px;background:var(--secondary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-person-fill" style="font-size:.9rem;color:var(--secondary);"></i>
                </div>
                My Profile
            </a>

            {{-- Divider --}}
            <div style="height:1px;background:var(--border);margin:0;"></div>

            {{-- Logout --}}
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button id="logoutBtn" type="submit"
                    style="width:100%;text-align:left;background:none;border:none;padding:.78rem 1.25rem;font-size:.84rem;font-weight:600;color:#dc2626;display:flex;align-items:center;gap:.65rem;cursor:pointer;transition:background var(--transition);"
                    onmouseenter="this.style.background='#fff5f5'"
                    onmouseleave="this.style.background='none'">
                    <div style="width:30px;height:30px;border-radius:9px;background:#fff5f5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-box-arrow-right" style="font-size:.9rem;color:#dc2626;"></i>
                    </div>
                    Sign Out
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

        function markNotificationAsRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => response.json()).catch(() => {});
        }
    }
</script>
@endpush

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

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

        {{-- Success flash --}}
        @if (session('success'))
            <div class="d-flex align-items-center gap-2 mb-4 animate-in"
                 style="background:#dcfce7;border:0;border-left:4px solid #16a34a;border-radius:12px;color:#166534;font-size:.875rem;padding:.8rem 1.1rem;"
                 role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── Welcome Hero ─────────────────────────────────────────── --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="animate-in" style="background:linear-gradient(135deg,#071f4d 0%,#0B2E6D 45%,#1a4d9e 100%);border-radius:24px;padding:2.5rem 2.5rem 2rem;position:relative;overflow:hidden;">

                    {{-- Decorative elements --}}
                    <div style="position:absolute;width:320px;height:320px;background:radial-gradient(circle,rgba(227,30,36,.18) 0%,transparent 70%);border-radius:50%;right:-80px;top:-100px;pointer-events:none;"></div>
                    <div style="position:absolute;width:180px;height:180px;background:rgba(255,255,255,.04);border-radius:50%;right:120px;bottom:-70px;pointer-events:none;"></div>
                    <div style="position:absolute;width:80px;height:80px;background:rgba(255,255,255,.06);border-radius:20px;right:2.5rem;top:2rem;pointer-events:none;transform:rotate(15deg);"></div>

                    <div style="position:relative;z-index:1;">
                        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:99px;padding:.28rem .85rem;font-size:.72rem;font-weight:700;color:rgba(255,255,255,.8);letter-spacing:.5px;text-transform:uppercase;margin-bottom:.9rem;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#4ade80;box-shadow:0 0 8px rgba(74,222,128,.6);display:inline-block;"></span>
                            Welcome back
                        </div>
                        <h1 style="color:#fff;font-size:2rem;font-weight:900;margin-bottom:.3rem;line-height:1.15;letter-spacing:-.5px;">
                            {{ Auth::user()->name }}
                        </h1>
                        <p style="color:rgba(255,255,255,.55);font-size:.9rem;margin-bottom:1.75rem;font-weight:500;">
                            AccessHub &nbsp;·&nbsp; PT Telkom Infrastruktur Indonesia
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            @if(Auth::user()->nik)
                                <span style="display:inline-flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.15);border-radius:99px;padding:.32rem .9rem;font-size:.77rem;font-weight:600;">
                                    <i class="bi bi-person-badge"></i>{{ Auth::user()->nik }}
                                </span>
                            @endif
                            <span style="display:inline-flex;align-items:center;gap:.45rem;background:rgba(227,30,36,.25);backdrop-filter:blur(8px);color:rgba(255,255,255,.9);border:1px solid rgba(227,30,36,.3);border-radius:99px;padding:.32rem .9rem;font-size:.77rem;font-weight:600;">
                                <i class="bi bi-shield-fill"></i>{{ Auth::user()->role === 'pic_ao' ? 'PIC AO' : (Auth::user()->role === 'ao' ? 'AO' : ucfirst(Auth::user()->role)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section Title ──────────────────────────────────────────── --}}
        <div class="d-flex align-items-center gap-3 mb-3 animate-in animate-in-delay-1">
            <h2 style="font-size:1rem;font-weight:800;color:var(--text);margin:0;letter-spacing:-.2px;">Modules</h2>
            <div style="flex:1;height:1px;background:var(--border);"></div>
        </div>

        {{-- ── Module Cards ─────────────────────────────────────────── --}}
        <div class="row g-3 mb-5">

            @php
                $uamRoute = route('access-matrix.request.index');
                if (Auth::user()->isManager()) {
                    $uamRoute = route('access-matrix.uam-request.index');
                } elseif (Auth::user()->isAo()) {
                    $uamRoute = route('access-matrix.approval.index');
                }

                $modules = [
                    [
                        'icon'    => 'bi-table',
                        'gradient'=> 'linear-gradient(135deg,#dbeafe,#bfdbfe)',
                        'icon_color'=> '#1e40af',
                        'accent'  => '#3b82f6',
                        'title'   => 'User Access Matrix',
                        'desc'    => 'Manage and track user access permissions across all systems.',
                        'tag'     => 'UAM',
                        'status'  => 'active',
                        'route'   => $uamRoute,
                        'delay'   => '.06s',
                    ],
                    [
                        'icon'    => 'bi-clipboard2-check-fill',
                        'gradient'=> 'linear-gradient(135deg,#ede9fe,#ddd6fe)',
                        'icon_color'=> '#6d28d9',
                        'accent'  => '#7c3aed',
                        'title'   => 'User Access Review',
                        'desc'    => 'Review and evaluate user access rights periodically with automated intelligence.',
                        'tag'     => 'UAR',
                        'status'  => 'active',
                        'route'   => route('uar.index'),
                        'delay'   => '.12s',
                    ],
                ];
            @endphp

            @foreach ($modules as $mod)
            <div class="col-12 col-sm-6 col-xl-4 animate-in" style="animation-delay: {{ $mod['delay'] }};">
                @if ($mod['status'] === 'active')
                <a href="{{ $mod['route'] }}" style="text-decoration:none;color:inherit;display:block;height:100%;">
                @else
                <div style="height:100%;">
                @endif
                    <div style="background:#fff;border:1.5px solid var(--border);border-radius:20px;padding:1.6rem;height:100%;position:relative;overflow:hidden;transition:transform var(--transition-slow),box-shadow var(--transition-slow),border-color var(--transition);"
                         class="module-card {{ $mod['status'] === 'active' ? 'module-active' : '' }}">

                        {{-- Background decoration --}}
                        <div style="position:absolute;width:100px;height:100px;background:{{ $mod['gradient'] }};border-radius:50%;right:-20px;top:-20px;opacity:.5;pointer-events:none;"></div>

                        {{-- Icon --}}
                        <div style="width:52px;height:52px;border-radius:16px;background:{{ $mod['gradient'] }};display:flex;align-items:center;justify-content:center;margin-bottom:1.1rem;box-shadow:0 4px 12px rgba(0,0,0,.06);">
                            <i class="bi {{ $mod['icon'] }}" style="font-size:1.4rem;color:{{ $mod['icon_color'] }};"></i>
                        </div>

                        {{-- Tag --}}
                        <div style="display:inline-block;background:{{ $mod['gradient'] }};color:{{ $mod['icon_color'] }};border-radius:6px;padding:.15rem .55rem;font-size:.65rem;font-weight:800;letter-spacing:.5px;margin-bottom:.6rem;">{{ $mod['tag'] }}</div>

                        <h3 style="font-size:1rem;font-weight:800;color:var(--text);margin-bottom:.4rem;letter-spacing:-.2px;">{{ $mod['title'] }}</h3>
                        <p style="font-size:.81rem;color:var(--text-muted);margin-bottom:1.1rem;line-height:1.55;">{{ $mod['desc'] }}</p>

                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            @if ($mod['status'] === 'active')
                            <span style="display:inline-flex;align-items:center;gap:.35rem;background:#dcfce7;color:#166534;border-radius:99px;padding:.26rem .75rem;font-size:.71rem;font-weight:700;">
                                <i class="bi bi-check-circle-fill" style="font-size:.65rem;"></i> Active
                            </span>
                            <span style="color:{{ $mod['icon_color'] }};font-size:.85rem;transition:transform var(--transition);" class="card-arrow">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                            </span>
                            @elseif ($mod['status'] === 'inactive')
                            <span style="display:inline-flex;align-items:center;gap:.35rem;background:#f3f4f6;color:var(--text-muted);border-radius:99px;padding:.26rem .75rem;font-size:.71rem;font-weight:700;">
                                <i class="bi bi-clock-history" style="font-size:.65rem;"></i> Coming Soon
                            </span>
                            @endif
                        </div>
                    </div>
                @if ($mod['status'] === 'active')
                </a>
                @else
                </div>
                @endif
            </div>
            @endforeach

        </div>
    </main>

</div>

@endsection

@push('styles')
<style>
    .module-card { cursor: default; }
    .module-active { cursor: pointer; }
    .module-active:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 8px 32px rgba(11,46,109,.12), 0 2px 8px rgba(0,0,0,.06) !important;
        border-color: var(--secondary) !important;
    }
    .module-active:hover .card-arrow {
        transform: translateX(4px);
    }
</style>
@endpush

@push('scripts')
@endpush

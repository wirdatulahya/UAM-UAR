@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- ─── Navbar ─────────────────────────────────────────────────────── --}}
<nav class="app-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">
                {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="navbar-brand-wrapper">
                <div class="brand-dot">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <div class="brand-text-main">AccessHub</div>
                    <div class="brand-text-sub">PT Telkom Infrastruktur Indonesia</div>
                </div>
            </a>
            </div>

            {{-- Right — Profile Dropdown --}}
            <x-navbar-right />
        </div>
    </div>
</nav>

{{-- ─── App Shell (Sidebar + Main) ────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

        {{-- Success/Error flash --}}
        @if (session('success'))
            <div class="alert d-flex align-items-center gap-2 mb-4 animate-in"
                 style="background:#e8f5e9;border:0;border-left:4px solid #2e7d32;border-radius:10px;color:#1b5e20;font-size:.875rem;padding:.75rem 1rem;"
                 role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── Welcome Hero ─────────────────────────────────────────── --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="animate-in" style="background:linear-gradient(135deg,var(--secondary) 0%,#1a4d9e 60%, #163f82 100%);border-radius:20px;padding:2.5rem 2.5rem 2rem;position:relative;overflow:hidden;">
                    {{-- decorative circle --}}
                    <div style="position:absolute;width:280px;height:280px;background:rgba(227,30,36,.15);border-radius:50%;right:-60px;top:-80px;"></div>
                    <div style="position:absolute;width:160px;height:160px;background:rgba(255,255,255,.05);border-radius:50%;right:80px;bottom:-60px;"></div>

                    <div style="position:relative;z-index:1;">
                        <p style="color:rgba(255,255,255,.65);font-size:.82rem;font-weight:500;margin-bottom:.35rem;letter-spacing:.3px;text-transform:uppercase;">
                            Welcome back
                        </p>
                        <h1 style="color:#fff;font-size:1.85rem;font-weight:800;margin-bottom:.35rem;line-height:1.2;">
                            {{ Auth::user()->name }}
                        </h1>
                        <p style="color:rgba(255,255,255,.7);font-size:.92rem;margin-bottom:1.75rem;">
                            AccessHub &nbsp;·&nbsp; PT Telkom Infrastruktur Indonesia
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span style="background:rgba(255,255,255,.15);backdrop-filter:blur(4px);color:#fff;border-radius:20px;padding:.3rem .85rem;font-size:.78rem;font-weight:600;">
                                <i class="bi bi-person-badge me-1"></i>{{ Auth::user()->username }}
                            </span>
                            <span style="background:rgba(255,255,255,.15);backdrop-filter:blur(4px);color:#fff;border-radius:20px;padding:.3rem .85rem;font-size:.78rem;font-weight:600;">
                                <i class="bi bi-envelope me-1"></i>{{ Auth::user()->email }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Module Cards ─────────────────────────────────────────── --}}
        <div class="row g-3">

            @php
                $uamRoute = route('access-matrix.request.index');
                if (Auth::user()->isManager()) {
                    $uamRoute = route('access-matrix.uam-request.index');
                } elseif (Auth::user()->isAo()) {
                    $uamRoute = route('access-matrix.approval.index');
                }

                $modules = [
                    [
                        'icon' => 'bi-table',
                        'color' => '#0B2E6D',
                        'bg' => '#e8edf7',
                        'title' => 'User Access Matrix',
                        'desc' => 'Manage and track user access permissions across all systems.',
                        'status' => 'active',
                        'route' => $uamRoute
                    ],

                    [
                        'icon' => 'bi-ui-checks-grid',
                        'color' => '#0B2E6D',
                        'bg' => '#e8edf7',
                        'title' => 'Access User Review',
                        'desc' => 'Review and approve access matrices submitted by users.',
                        'status' => 'inactive',
                        'route' => route('access-matrix.approval.index')
                    ],
                ];
            @endphp

            @foreach ($modules as $i => $mod)
            <div class="col-12 col-sm-6 col-xl-3 animate-in" style="animation-delay: {{ $i * 0.07 }}s;">
                @if ($mod['status'] === 'active')
                <a href="{{ $mod['route'] }}" class="h-100 d-block" style="text-decoration:none; color:inherit;">
                @else
                <div class="h-100">
                @endif
                    <div class="h-100" style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem;position:relative;overflow:hidden;transition:transform .22s,box-shadow .22s;cursor:{{ $mod['status'] === 'active' ? 'pointer' : 'default' }};"
                         onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--card-shadow)';if('{{ $mod['status'] }}' === 'active'){this.style.borderColor='var(--secondary)';}"
                         onmouseleave="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border)';">

                        <div style="width:48px;height:48px;background:{{ $mod['bg'] }};border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                            <i class="bi {{ $mod['icon'] }}" style="font-size:1.3rem;color:{{ $mod['color'] }};"></i>
                        </div>

                        <h3 style="font-size:.95rem;font-weight:700;color:var(--secondary);margin-bottom:.35rem;">{{ $mod['title'] }}</h3>
                        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.5;">{{ $mod['desc'] }}</p>

                        @if ($mod['status'] === 'active')
                        <span style="display:inline-flex;align-items:center;gap:.35rem;background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:.25rem .7rem;font-size:.72rem;font-weight:700;">
                            <i class="bi bi-check-circle-fill" style="font-size:.7rem;"></i> Active
                        </span>
                        @elseif ($mod['status'] === 'coming-soon')
                        <span style="display:inline-flex;align-items:center;gap:.35rem;background:var(--primary-light);color:var(--primary);border-radius:20px;padding:.25rem .7rem;font-size:.72rem;font-weight:700;">
                            <i class="bi bi-clock-history" style="font-size:.7rem;"></i> Coming soon
                        </span>
                        @endif
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

@push('scripts')
@endpush




@extends('layouts.app')

@section('title', 'Approval Access Matrix')

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
    </div>
</nav>

{{-- ─── App Shell (Sidebar + Main) ────────────────────────────────── --}}
<div class="d-flex" style="min-height:calc(100vh - 57px);">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main Content --}}
    <main class="flex-grow-1 page-content px-4">

                <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Approval Access Matrix'],
        ]" />

        {{-- ── Page Header ── --}}
        <div class="mb-4 animate-in">
            <h1 style="font-size:1.45rem;font-weight:800;color:var(--secondary);margin:0 0 .2rem;">
                <i class="bi bi-ui-checks-grid me-2" style="color:var(--primary);"></i>
                Approval Access Matrix
            </h1>
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;">
                Future feature placeholder. Ready for redevelopment.
            </p>
        </div>

        {{-- ── Placeholder Content ── --}}
        <div class="row g-4 animate-in animate-in-delay-1">
            <div class="col-12">
                <div style="background:#fff;border:1.5px dashed var(--border);border-radius:16px;padding:3rem 1.5rem;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);">
                    <div style="width:64px;height:64px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
                        <i class="bi bi-tools" style="font-size:1.8rem;color:var(--secondary);"></i>
                    </div>
                    <h3 style="font-size:1.15rem;font-weight:800;color:var(--secondary);margin-bottom:.5rem;">Module Temporarily Disabled</h3>
                    <p style="font-size:.85rem;max-width:400px;margin-bottom:0;">
                        The functionality of this module has been migrated to Accept. This section is currently a placeholder undergoing redevelopment.
                    </p>
                </div>
            </div>
        </div>

    </main>

</div>

@endsection

@push('scripts')
<script>
</script>
@endpush

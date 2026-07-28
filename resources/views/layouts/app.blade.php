<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AccessHub — PT Telkom Infrastruktur Indonesia internal portal">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AccessHub') — PT Telkom Infrastruktur Indonesia</title>

    {{-- Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ══════════════════════════════════════════════
           DESIGN TOKENS
        ══════════════════════════════════════════════ */
        :root {
            /* Brand */
            --primary:          #E31E24;
            --primary-dark:     #b81519;
            --primary-light:    #fde8e9;
            --primary-glow:     rgba(227,30,36,.25);
            --secondary:        #0B2E6D;
            --secondary-dark:   #071f4d;
            --secondary-light:  #eef1f8;

            /* Sidebar */
            --sidebar-bg:       #0d1b3e;
            --sidebar-border:   rgba(255,255,255,.06);
            --sidebar-text:     rgba(255,255,255,.55);
            --sidebar-hover:    rgba(255,255,255,.07);
            --sidebar-active-from: #E31E24;
            --sidebar-active-to:   #ff5c62;
            --sidebar-w:        240px;
            --sidebar-w-collapsed: 64px;
            --sidebar-transition: 250ms cubic-bezier(0.4,0,0.2,1);
            --topbar-h:         56px;

            /* Surfaces */
            --bg:               #f0f2f8;
            --surface:          #ffffff;
            --surface-alt:      #f7f8fc;
            --glass:            rgba(255,255,255,.72);
            --glass-border:     rgba(255,255,255,.35);

            /* Text */
            --text:             #111827;
            --text-muted:       #6b7280;
            --text-light:       #9ca3af;

            /* Borders & Shadows */
            --border:           #e5e7eb;
            --card-shadow:      0 1px 3px rgba(0,0,0,.06), 0 8px 24px rgba(11,46,109,.08);
            --card-shadow-hover:0 4px 12px rgba(0,0,0,.08), 0 20px 40px rgba(11,46,109,.14);
            --glow-blue:        0 0 24px rgba(11,46,109,.18);

            /* Radius */
            --card-radius:      18px;
            --input-radius:     12px;
            --pill-radius:      100px;

            /* Transition */
            --transition:       0.2s cubic-bezier(0.4,0,0.2,1);
            --transition-slow:  0.35s cubic-bezier(0.4,0,0.2,1);
        }

        /* ══════════════════════════════════════════════
           BASE RESET
        ══════════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-light); }

        /* Hide native password reveal (Edge/IE) */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }

        /* ══════════════════════════════════════════════
           AUTH PAGES — Full-screen split layout
        ══════════════════════════════════════════════ */
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* Left decorative panel */
        .auth-panel {
            width: 42%;
            background: linear-gradient(155deg, #071f4d 0%, #0B2E6D 50%, #1a4d9e 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .auth-panel::before {
            content: '';
            position: absolute;
            width: 480px; height: 480px;
            background: radial-gradient(circle, rgba(227,30,36,.2) 0%, transparent 70%);
            border-radius: 50%;
            top: -120px; right: -120px;
        }
        .auth-panel::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            background: rgba(255,255,255,.04);
            border-radius: 50%;
            bottom: -80px; left: -80px;
        }

        /* Auth panel floating shapes */
        .auth-panel-shape {
            position: absolute;
            border-radius: 50%;
            animation: floatShape 6s ease-in-out infinite;
        }

        @keyframes floatShape {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-16px); }
        }

        .auth-panel-logo {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .auth-panel-logo .brand-badge {
            width: 76px; height: 76px;
            background: linear-gradient(135deg, var(--primary), #ff5c62);
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 32px var(--primary-glow), 0 2px 8px rgba(0,0,0,.2);
            transition: transform var(--transition-slow), box-shadow var(--transition-slow);
        }

        .auth-panel-logo .brand-badge:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 20px 48px var(--primary-glow), 0 4px 16px rgba(0,0,0,.3);
        }

        .auth-panel-logo .brand-badge i {
            font-size: 2.1rem;
            color: #fff;
        }

        .auth-panel-logo h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            margin-bottom: .35rem;
            transition: transform var(--transition-slow), text-shadow var(--transition-slow);
        }

        .auth-panel-logo h1:hover {
            transform: translateY(-4px);
            text-shadow: 0 12px 32px rgba(227,30,36,.4);
        }

        .auth-panel-logo p {
            font-size: .83rem;
            color: rgba(255,255,255,.55);
            font-weight: 500;
            margin: 0;
        }

        .auth-panel-features {
            position: relative;
            z-index: 1;
            margin-top: 3rem;
            width: 100%;
        }

        .auth-panel-features .feature-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .8rem 1.1rem;
            border-radius: 14px;
            background: rgba(255,255,255,.06);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.08);
            margin-bottom: .6rem;
            transition: background var(--transition), transform var(--transition);
        }

        .auth-panel-features .feature-item:hover {
            background: rgba(255,255,255,.1);
            transform: translateX(4px);
        }

        .auth-panel-features .feature-item i {
            font-size: 1.05rem;
            color: var(--primary);
            flex-shrink: 0;
            filter: drop-shadow(0 0 6px var(--primary-glow));
        }

        .auth-panel-features .feature-item span {
            font-size: .82rem;
            color: rgba(255,255,255,.8);
            font-weight: 500;
        }

        /* Right content panel */
        .auth-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: var(--bg);
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
        }

        .auth-card-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: .3rem;
            letter-spacing: -0.5px;
        }

        .auth-card-subtitle {
            font-size: .88rem;
            color: var(--text-muted);
            margin-bottom: 1.75rem;
        }

        /* ══════════════════════════════════════════════
           FORM CONTROLS
        ══════════════════════════════════════════════ */
        .form-label {
            font-size: .8rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: .4rem;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .form-control, .form-select {
            border-radius: var(--input-radius);
            border: 1.5px solid var(--border);
            padding: .7rem 1.1rem;
            font-size: .92rem;
            color: var(--text);
            background: var(--surface);
            transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(11,46,109,.08);
            outline: none;
            background: #fff;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(227,30,36,.07);
        }

        /* Keep left prefix icon neutral — error state is shown via border + right icon only */
        .form-control.is-invalid ~ span,
        .form-control.is-invalid + span,
        span:has(+ .form-control.is-invalid),
        span:has(~ .form-control.is-invalid) {
            color: var(--text-muted) !important;
        }

        .invalid-feedback { font-size: .79rem; }

        /* Password toggle wrapper */
        .input-password-wrapper { position: relative; }
        .input-password-wrapper .form-control { padding-right: 2.8rem; }

        .password-toggle {
            position: absolute;
            right: .8rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            transition: color var(--transition);
        }

        .password-toggle:hover { color: var(--secondary); }

        /* ══════════════════════════════════════════════
           BUTTONS
        ══════════════════════════════════════════════ */
        .btn-primary-custom {
            background: linear-gradient(135deg, #0B2E6D 0%, #071f4d 100%);
            color: #fff;
            border: none;
            border-radius: var(--input-radius);
            padding: .78rem 1.5rem;
            font-weight: 700;
            font-size: .92rem;
            width: 100%;
            letter-spacing: .2px;
            transition: transform var(--transition), box-shadow var(--transition), filter var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.12) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(11,46,109,.35);
            filter: brightness(1.1);
        }

        .btn-primary-custom:active { transform: translateY(0); filter: brightness(.97); }

        .btn-danger-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            border: none;
            border-radius: var(--input-radius);
            padding: .78rem 1.5rem;
            font-weight: 700;
            font-size: .92rem;
            width: 100%;
            letter-spacing: .2px;
            transition: transform var(--transition), box-shadow var(--transition), filter var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-danger-custom::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.12) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px var(--primary-glow);
            filter: brightness(1.1);
        }

        .btn-danger-custom:active { transform: translateY(0); filter: brightness(.97); }

        /* ══════════════════════════════════════════════
           LINKS & ALERTS
        ══════════════════════════════════════════════ */
        .auth-link {
            color: var(--secondary);
            font-weight: 600;
            text-decoration: none;
            transition: color var(--transition);
        }
        .auth-link:hover { color: var(--primary); text-decoration: underline; }

        .alert-custom {
            border-radius: 12px;
            font-size: .875rem;
            padding: .8rem 1.1rem;
            border-width: 0;
            border-left: 4px solid;
        }
        .alert-custom-danger {
            background: var(--primary-light);
            border-color: var(--primary);
            color: #7b0d0f;
        }
        .alert-custom-success {
            background: #e8f5e9;
            border-color: #2e7d32;
            color: #1b5e20;
        }

        .auth-divider {
            display: flex; align-items: center; gap: 1rem;
            margin: 1.5rem 0;
            color: var(--text-muted);
            font-size: .78rem;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        @media (max-width: 768px) {
            .auth-panel { display: none; }
            .auth-content { padding: 1.5rem 1rem; }
        }

        /* ══════════════════════════════════════════════
           APP SHELL — Full-height sidebar layout
        ══════════════════════════════════════════════ */

        /* App shell body — only when sidebar is present */
        body:has(.app-sidebar) {
            display: flex;
            min-height: 100vh;
        }

        /* ─── Sidebar ────────────────────────────────────────────────── */
        .app-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #0d1b3e 0%, #0a1628 100%);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 400;
            transition: width var(--sidebar-transition);
            will-change: width;
        }

        /* Sidebar decorative glow blobs */
        .app-sidebar::before {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(227,30,36,.12) 0%, transparent 70%);
            border-radius: 50%;
            top: -60px; right: -60px;
            pointer-events: none;
        }
        .app-sidebar::after {
            content: '';
            position: absolute;
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(11,46,109,.2) 0%, transparent 70%);
            border-radius: 50%;
            bottom: 40px; left: -40px;
            pointer-events: none;
        }

        /* ─── Brand Header (inside sidebar) ──────────────────────────── */
        .sidebar-brand {
            display: flex;
            align-items: center;
            height: var(--topbar-h);
            padding: 0 1rem;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .sidebar-brand-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            text-decoration: none;
            white-space: nowrap;
        }

        .sidebar-brand-icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--primary), #ff5c62);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px var(--primary-glow);
            transition: transform var(--sidebar-transition), box-shadow var(--sidebar-transition);
        }
        .sidebar-brand-icon i { color: #fff; font-size: 1rem; }
        .sidebar-brand-link:hover .sidebar-brand-icon {
            transform: scale(1.08) rotate(-4deg);
            box-shadow: 0 6px 20px var(--primary-glow);
        }

        .sidebar-brand-text {
            font-size: .95rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.3px;
            transition: opacity var(--sidebar-transition), width var(--sidebar-transition);
            overflow: hidden;
            white-space: nowrap;
        }

        /* ─── Scrollable nav area ─────────────────────────────────────── */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .75rem 0;
            position: relative;
            z-index: 1;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 99px; }

        /* ─── Section labels ──────────────────────────────────────────── */
        .sidebar-section-label {
            font-size: .6rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,.28);
            padding: 1rem 1.2rem .35rem;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity var(--sidebar-transition), height var(--sidebar-transition),
                        padding var(--sidebar-transition);
            position: relative;
            z-index: 1;
        }

        /* ─── Nav items ───────────────────────────────────────────────── */
        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .62rem 1.1rem;
            margin: 2px .7rem;
            font-size: .84rem;
            font-weight: 500;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 12px;
            border-left: none;
            transition: color var(--sidebar-transition), background var(--sidebar-transition);
            position: relative;
            z-index: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-icon {
            font-size: .95rem;
            flex-shrink: 0;
            transition: color var(--sidebar-transition), transform var(--sidebar-transition);
            min-width: 1rem;
        }

        .sidebar-item-text {
            transition: opacity var(--sidebar-transition), width var(--sidebar-transition);
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-chevron {
            font-size: .68rem;
            flex-shrink: 0;
            transition: transform var(--sidebar-transition), opacity var(--sidebar-transition);
        }

        .sidebar-nav-item[data-bs-toggle="collapse"] .sidebar-chevron { transform: rotate(180deg); }
        .sidebar-nav-item[data-bs-toggle="collapse"].collapsed .sidebar-chevron { transform: rotate(0deg); }

        .sidebar-nav-item:hover {
            color: rgba(255,255,255,.9);
            background: var(--sidebar-hover);
        }
        .sidebar-nav-item:hover .sidebar-icon { color: rgba(255,255,255,.9); transform: translateX(2px); }

        .sidebar-nav-item.active {
            background: linear-gradient(135deg, var(--sidebar-active-from), var(--sidebar-active-to));
            color: #fff;
            font-weight: 700;
            box-shadow: 0 4px 16px rgba(227,30,36,.35);
        }
        .sidebar-nav-item.active .sidebar-icon { color: #fff; }

        /* ─── Sub-menu ────────────────────────────────────────────────── */
        .sidebar-sub-group { padding: .25rem 0; }

        .sidebar-sub-item {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .5rem 1.1rem .5rem 3rem;
            margin: 1px .7rem;
            font-size: .8rem;
            font-weight: 500;
            color: rgba(255,255,255,.4);
            text-decoration: none;
            border-radius: 10px;
            transition: color var(--sidebar-transition), background var(--sidebar-transition);
            position: relative;
            z-index: 1;
            white-space: nowrap;
        }
        .sidebar-sub-item::before {
            content: '';
            position: absolute;
            left: 2rem;
            width: 4px; height: 4px;
            border-radius: 50%;
            background: currentColor;
        }
        .sidebar-sub-item:hover { color: rgba(255,255,255,.85); background: rgba(255,255,255,.06); }
        .sidebar-sub-item.active { color: #fff; background: rgba(255,255,255,.1); font-weight: 700; }
        .sidebar-sub-item.active::before { background: var(--sidebar-active-from); box-shadow: 0 0 6px var(--primary-glow); }

        /* ─── Footer ──────────────────────────────────────────────────── */
        .sidebar-footer {
            flex-shrink: 0;
            padding: .75rem;
            border-top: 1px solid rgba(255,255,255,.07);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .sidebar-footer-inner {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .55rem .65rem;
            border-radius: 12px;
            background: rgba(255,255,255,.05);
            white-space: nowrap;
        }
        .sidebar-user-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #ff5c62);
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
            overflow: hidden;
        }
        .sidebar-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .sidebar-user-info { min-width: 0; overflow: hidden; transition: opacity var(--sidebar-transition), width var(--sidebar-transition); }
        .sidebar-user-name { font-size: .76rem; font-weight: 700; color: rgba(255,255,255,.85); overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-role { font-size: .64rem; color: rgba(255,255,255,.38); text-transform: capitalize; }

        /* ══════════════════════════════════════════════
           CONTENT WRAPPER — shifts right of sidebar
        ══════════════════════════════════════════════ */
        .app-content-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            flex: 1;
            transition: margin-left var(--sidebar-transition);
            min-width: 0;
        }

        /* ══════════════════════════════════════════════
           TOPBAR — lives inside .app-content-wrapper
        ══════════════════════════════════════════════ */
        .app-topbar {
            height: var(--topbar-h);
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid rgba(255,255,255,.6);
            box-shadow: 0 1px 0 rgba(0,0,0,.05), 0 4px 16px rgba(11,46,109,.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 300;
            flex-shrink: 0;
        }

        .topbar-left { display: flex; align-items: center; gap: .75rem; }

        /* Hamburger toggle button */
        .btn-sidebar-toggle {
            width: 36px; height: 36px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: none;
            color: var(--text-muted);
            font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: border-color var(--transition), color var(--transition), background var(--transition), transform var(--transition);
            flex-shrink: 0;
        }
        .btn-sidebar-toggle:hover {
            border-color: var(--secondary);
            color: var(--secondary);
            background: var(--secondary-light);
        }
        .btn-sidebar-toggle:active { transform: scale(.93); }

        /* ══════════════════════════════════════════════
           COLLAPSED STATE
        ══════════════════════════════════════════════ */
        body.sidebar-collapsed .app-sidebar        { width: var(--sidebar-w-collapsed); }
        body.sidebar-collapsed .app-content-wrapper { margin-left: var(--sidebar-w-collapsed); }

        /* Hide text elements when collapsed */
        body.sidebar-collapsed .sidebar-brand-text  { opacity: 0; width: 0; pointer-events: none; }
        body.sidebar-collapsed .sidebar-item-text   { opacity: 0; width: 0; overflow: hidden; }
        body.sidebar-collapsed .sidebar-chevron     { opacity: 0; width: 0; margin: 0; }
        body.sidebar-collapsed .sidebar-section-label { opacity: 0; height: 0; padding: 0; overflow: hidden; }
        body.sidebar-collapsed .sidebar-user-info   { opacity: 0; width: 0; }
        /* Hide sub-menus when collapsed */
        body.sidebar-collapsed .collapse.show       { display: none !important; }
        /* Center elements when collapsed */
        body.sidebar-collapsed .sidebar-brand       { justify-content: center; padding: 0; }
        body.sidebar-collapsed .sidebar-brand-link  { gap: 0; justify-content: center; width: 100%; }
        body.sidebar-collapsed .sidebar-nav-item    { justify-content: center; padding: 0; margin: 4px auto; width: 46px; height: 46px; border-radius: 12px; }
        body.sidebar-collapsed .sidebar-icon        { font-size: 1.1rem; margin: 0; }
        body.sidebar-collapsed .sidebar-footer-inner { justify-content: center; padding: .55rem 0; }

        /* Tooltip shown via JS on collapsed icons */
        body.sidebar-collapsed .sidebar-nav-item[data-sidebar-tooltip]:hover::after {
            content: attr(data-sidebar-tooltip);
            position: absolute;
            left: calc(var(--sidebar-w-collapsed) + 8px);
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: #fff;
            font-size: .75rem;
            font-weight: 600;
            padding: .35rem .75rem;
            border-radius: 8px;
            white-space: nowrap;
            z-index: 999;
            box-shadow: 0 4px 16px rgba(0,0,0,.25);
            pointer-events: none;
        }

        /* ══════════════════════════════════════════════
           PAGE CONTENT
        ══════════════════════════════════════════════ */
        .page-content {
            padding: 2.5rem 0;
            flex: 1;
        }

        /* ══════════════════════════════════════════════
           CARDS & CONTENT
        ══════════════════════════════════════════════ */
        .card-premium {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-slow), box-shadow var(--transition-slow), border-color var(--transition);
        }

        .card-premium:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }

        .card-header-premium {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, var(--secondary-light), #fff);
            border-radius: var(--card-radius) var(--card-radius) 0 0;
        }

        /* ══════════════════════════════════════════════
           ANIMATIONS
        ══════════════════════════════════════════════ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-16px); }
            to   { opacity: 1; transform: translateX(0);     }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(.94); }
            to   { opacity: 1; transform: scale(1);   }
        }

        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0;  }
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 var(--primary-glow); }
            50%       { box-shadow: 0 0 0 8px transparent; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }

        .animate-in {
            animation: fadeInUp .42s cubic-bezier(0.4,0,0.2,1) both;
        }

        .animate-in-delay-1 { animation-delay: .06s; }
        .animate-in-delay-2 { animation-delay: .12s; }
        .animate-in-delay-3 { animation-delay: .18s; }
        .animate-in-delay-4 { animation-delay: .24s; }
        .animate-in-delay-5 { animation-delay: .30s; }
        .animate-in-delay-6 { animation-delay: .36s; }

        .animate-scale { animation: scaleIn .38s cubic-bezier(0.4,0,0.2,1) both; }
        .animate-left  { animation: fadeInLeft .38s cubic-bezier(0.4,0,0.2,1) both; }

        /* ══════════════════════════════════════════════
           UTILITY
        ══════════════════════════════════════════════ */
        .badge-status {
            display: inline-flex; align-items: center; gap: .3rem;
            border-radius: var(--pill-radius);
            padding: .28rem .75rem;
            font-size: .72rem; font-weight: 700;
            letter-spacing: .2px;
        }

        .badge-status-active  { background: #dcfce7; color: #166534; }
        .badge-status-pending { background: #fef3c7; color: #92400e; }
        .badge-status-danger  { background: var(--primary-light); color: var(--primary-dark); }
        .badge-status-info    { background: #dbeafe; color: #1e3a8a; }

        .divider-vertical {
            width: 1px; height: 24px;
            background: var(--border);
        }

        /* Glass card utility */
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(12px) saturate(160%);
            -webkit-backdrop-filter: blur(12px) saturate(160%);
            border: 1px solid var(--glass-border);
            border-radius: var(--card-radius);
        }

        /* Gradient text */
        .text-gradient-primary {
            background: linear-gradient(135deg, var(--primary), #ff7c82);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-gradient-secondary {
            background: linear-gradient(135deg, var(--secondary), #1a4d9e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Icon badge */
        .icon-badge {
            width: 46px; height: 46px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .icon-badge-red    { background: linear-gradient(135deg,#fde8e9,#ffc1c2); color: var(--primary); }
        .icon-badge-blue   { background: linear-gradient(135deg,#dbeafe,#bfdbfe); color: #1e40af; }
        .icon-badge-green  { background: linear-gradient(135deg,#dcfce7,#bbf7d0); color: #166534; }
        .icon-badge-orange { background: linear-gradient(135deg,#fef3c7,#fde68a); color: #92400e; }
        .icon-badge-purple { background: linear-gradient(135deg,#ede9fe,#ddd6fe); color: #5b21b6; }
        .icon-badge-teal   { background: linear-gradient(135deg,#ccfbf1,#99f6e4); color: #115e59; }

        /* Hover lift */
        .hover-lift {
            transition: transform var(--transition-slow), box-shadow var(--transition-slow);
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }

        /* Sidebar toggle button (already visible always) */

        /* Mobile: hide sidebar by default, show as overlay */
        @media (max-width: 992px) {
            .app-sidebar {
                transform: translateX(-100%);
                transition: transform var(--sidebar-transition), width var(--sidebar-transition);
            }
            .app-sidebar.mobile-open {
                transform: translateX(0);
            }
            .app-content-wrapper {
                margin-left: 0 !important;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.4);
                z-index: 399;
                backdrop-filter: blur(2px);
            }
            .sidebar-overlay.active { display: block; }
        }

        @media (max-width: 576px) {
            .page-content { padding: 1.5rem 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

    @yield('content')

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <x-toast />

    @stack('scripts')

    @if(Auth::check() && Auth::user()->requires_onboarding && !request()->routeIs('profile.*'))
        <!-- Mandatory Onboarding Modal -->
        <div class="modal fade" id="onboardingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="onboardingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
                    <div class="modal-body text-center" style="padding: 3rem 2rem;">
                        <div style="width: 72px; height: 72px; background: linear-gradient(135deg, var(--primary-light), #ffc1c2); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem; box-shadow: 0 8px 24px var(--primary-glow);">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <h4 style="font-weight: 800; color: var(--text); margin-bottom: 1rem; letter-spacing: -.3px;">Welcome to AccessHub!</h4>
                        <p style="color: var(--text-muted); font-size: .93rem; margin-bottom: 2rem; line-height: 1.6;">
                            Before you can access the system, you must complete your profile information and change your temporary password.
                        </p>
                        <a href="{{ route('profile.index') }}" style="display: inline-flex; align-items: center; gap: .5rem; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; border: none; padding: .8rem 2rem; border-radius: 12px; font-weight: 700; font-size: .93rem; text-decoration: none; box-shadow: 0 6px 20px var(--primary-glow); transition: transform var(--transition), box-shadow var(--transition);"
                            onmouseenter="this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 28px var(--primary-glow)'"
                            onmouseleave="this.style.transform='';this.style.boxShadow='0 6px 20px var(--primary-glow)'">
                            <i class="bi bi-arrow-right-circle-fill"></i> Complete Setup
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var onboardingModal = new bootstrap.Modal(document.getElementById('onboardingModal'));
                onboardingModal.show();
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Toggle Logic
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        // Mobile behavior
                        sidebar.classList.toggle('mobile-open');
                        if(overlay) overlay.classList.toggle('active');
                    } else {
                        // Desktop behavior
                        document.body.classList.toggle('sidebar-collapsed');
                        
                        // Close any open collapse menus in sidebar when collapsing
                        if (document.body.classList.contains('sidebar-collapsed')) {
                            const openCollapses = sidebar.querySelectorAll('.collapse.show');
                            openCollapses.forEach(c => {
                                const bsCollapse = bootstrap.Collapse.getInstance(c);
                                if (bsCollapse) bsCollapse.hide();
                            });
                        }
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                });
            }

            // Initialize tooltips for sidebar (they appear when collapsed)
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html>

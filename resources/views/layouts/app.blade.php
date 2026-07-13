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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ──────────────────────────────────────
           Design Tokens
        ────────────────────────────────────── */
        :root {
            --primary:        #E31E24;
            --primary-dark:   #b81519;
            --primary-light:  #fde8e9;
            --secondary:      #0B2E6D;
            --secondary-dark: #071f4d;
            --secondary-light:#e8edf7;
            --bg:             #F8F9FA;
            --text:           #1A1A1A;
            --text-muted:     #6c757d;
            --border:         #dee2e6;
            --card-shadow:    0 4px 24px rgba(11,46,109,.10), 0 1px 4px rgba(0,0,0,.06);
            --card-radius:    16px;
            --input-radius:   10px;
            --transition:     0.22s ease;
        }

        /* ──────────────────────────────────────
           Base
        ────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ──────────────────────────────────────
           Auth Pages — Full-screen split layout
        ────────────────────────────────────── */
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* Left decorative panel */
        .auth-panel {
            width: 42%;
            background: linear-gradient(145deg, var(--secondary-dark) 0%, var(--secondary) 60%, #1a4d9e 100%);
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
            width: 400px; height: 400px;
            background: rgba(227,30,36,.18);
            border-radius: 50%;
            top: -100px; right: -100px;
        }
        .auth-panel::after {
            content: '';
            position: absolute;
            width: 260px; height: 260px;
            background: rgba(255,255,255,.06);
            border-radius: 50%;
            bottom: -60px; left: -60px;
        }

        .auth-panel-logo {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .auth-panel-logo .brand-badge {
            width: 72px; height: 72px;
            background: var(--primary);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 24px rgba(227,30,36,.4);
        }

        .auth-panel-logo .brand-badge i {
            font-size: 2rem;
            color: #fff;
        }

        .auth-panel-logo h1 {
            font-size: 1.55rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
            margin-bottom: .35rem;
        }

        .auth-panel-logo p {
            font-size: .85rem;
            color: rgba(255,255,255,.65);
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
            padding: .75rem 1rem;
            border-radius: 12px;
            background: rgba(255,255,255,.07);
            backdrop-filter: blur(4px);
            margin-bottom: .65rem;
        }

        .auth-panel-features .feature-item i {
            font-size: 1.1rem;
            color: var(--primary);
            flex-shrink: 0;
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
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
        }

        .auth-card-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: .3rem;
        }

        .auth-card-subtitle {
            font-size: .88rem;
            color: var(--text-muted);
            margin-bottom: 1.75rem;
        }

        /* ──────────────────────────────────────
           Form Controls
        ────────────────────────────────────── */
        .form-label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: .35rem;
            letter-spacing: .3px;
        }

        .form-control {
            border-radius: var(--input-radius);
            border: 1.5px solid var(--border);
            padding: .65rem 1rem;
            font-size: .92rem;
            color: var(--text);
            background: #fff;
            transition: border-color var(--transition), box-shadow var(--transition);
        }

        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(11,46,109,.1);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(227,30,36,.08);
        }

        .invalid-feedback { font-size: .79rem; }

        /* Password toggle wrapper */
        .input-password-wrapper {
            position: relative;
        }

        .input-password-wrapper .form-control {
            padding-right: 2.8rem;
        }

        .password-toggle {
            position: absolute;
            right: .75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.05rem;
            line-height: 1;
            transition: color var(--transition);
        }

        .password-toggle:hover { color: var(--secondary); }

        /* ──────────────────────────────────────
           Buttons
        ────────────────────────────────────── */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            border: none;
            border-radius: var(--input-radius);
            padding: .72rem 1.5rem;
            font-weight: 700;
            font-size: .95rem;
            width: 100%;
            letter-spacing: .2px;
            transition: transform var(--transition), box-shadow var(--transition), filter var(--transition);
            cursor: pointer;
        }

        .btn-primary-custom:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(227,30,36,.35);
        }

        .btn-primary-custom:active {
            transform: translateY(0);
        }

        /* ──────────────────────────────────────
           Links
        ────────────────────────────────────── */
        .auth-link {
            color: var(--secondary);
            font-weight: 600;
            text-decoration: none;
            transition: color var(--transition);
        }

        .auth-link:hover { color: var(--primary); text-decoration: underline; }

        /* ──────────────────────────────────────
           Alerts
        ────────────────────────────────────── */
        .alert-custom {
            border-radius: 10px;
            font-size: .875rem;
            padding: .75rem 1rem;
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

        /* ──────────────────────────────────────
           Divider
        ────────────────────────────────────── */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: var(--text-muted);
            font-size: .78rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ──────────────────────────────────────
           Responsive — hide panel on small screens
        ────────────────────────────────────── */
        @media (max-width: 768px) {
            .auth-panel { display: none; }

            .auth-content {
                padding: 1.5rem 1rem;
            }
        }

        /* ──────────────────────────────────────
           Navbar (Dashboard)
        ────────────────────────────────────── */
        .app-navbar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: .75rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        .app-navbar .navbar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: .65rem;
            text-decoration: none;
        }

        .app-navbar .brand-dot {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }

        .app-navbar .brand-dot i { color: #fff; font-size: 1rem; }

        .app-navbar .brand-text-main {
            font-size: .92rem;
            font-weight: 800;
            color: var(--secondary);
            line-height: 1.1;
        }

        .app-navbar .brand-text-sub {
            font-size: .68rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .btn-logout {
            background: none;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: .42rem .95rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: all var(--transition);
        }

        .btn-logout:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        /* ──────────────────────────────────────
           Dashboard Content
        ────────────────────────────────────── */
        .page-content {
            padding: 2.5rem 0;
        }

        /* Sidebar placeholder for future nav items */
        .sidebar {
            background: #fff;
            border-right: 1px solid var(--border);
            min-height: calc(100vh - 58px);
            padding: 1.5rem 0;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .65rem 1.25rem;
            font-size: .85rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all var(--transition);
        }

        .sidebar-nav-item:hover,
        .sidebar-nav-item.active {
            color: var(--secondary);
            background: var(--secondary-light);
            border-left-color: var(--secondary);
        }

        .sidebar-nav-item i { font-size: 1rem; }
        
        .sidebar-nav-item[data-bs-toggle="collapse"] .bi-chevron-down {
            transform: rotate(180deg);
        }
        .sidebar-nav-item[data-bs-toggle="collapse"].collapsed .bi-chevron-down {
            transform: rotate(0deg);
        }

        .sidebar-section-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--text-muted);
            padding: 1rem 1.25rem .4rem;
        }

        /* ──────────────────────────────────────
           Animations
        ────────────────────────────────────── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        .animate-in {
            animation: fadeInUp .45s ease both;
        }

        .animate-in-delay-1 { animation-delay: .06s; }
        .animate-in-delay-2 { animation-delay: .12s; }
        .animate-in-delay-3 { animation-delay: .18s; }
        .animate-in-delay-4 { animation-delay: .24s; }
        .animate-in-delay-5 { animation-delay: .30s; }
    </style>

    @stack('styles')
</head>
<body>

    @yield('content')

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistemas - Universidad PAT')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f0f0; color: #1a1a1a; min-height: 100vh; }

        .topbar {
            background: #111;
            color: #fff;
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar-logo { display: flex; align-items: center; gap: 10px; }
        .topbar-badge { background: #e30613; padding: 6px 10px; font-weight: 800; font-size: 0.9rem; }
        .topbar-title { font-size: 0.95rem; font-weight: 600; }
        .topbar a { color: #ccc; font-size: 0.85rem; text-decoration: none; }
        .topbar a:hover { color: #fff; }

        .layout { display: flex; min-height: calc(100vh - 56px); }
        .sidebar {
            width: 240px;
            background: #1a1a1a;
            padding: 1.5rem 0;
            flex-shrink: 0;
        }
        .sidebar-label {
            color: #666;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1.5rem;
            margin-bottom: 0.75rem;
        }
        .sidebar a {
            display: block;
            color: #ccc;
            padding: 10px 1.5rem;
            font-size: 0.9rem;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all .2s;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background: rgba(227,6,19,.15);
            border-left-color: #e30613;
        }

        .main { flex: 1; padding: 2rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-size: 1.6rem; font-weight: 800; }
        .page-header p { color: #666; margin-top: 4px; font-size: 0.9rem; }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e30613;
        }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            font-family: inherit;
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #e30613;
            box-shadow: 0 0 0 3px rgba(227,6,19,.12);
        }
        .form-group .error { color: #e30613; font-size: 0.8rem; margin-top: 4px; }
        .file-input {
            border: 2px dashed #ddd;
            border-radius: 6px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s;
        }
        .file-input:hover { border-color: #e30613; }
        .file-input input { display: none; }
        .file-input label { cursor: pointer; color: #555; font-size: 0.9rem; }
        .file-input strong { color: #e30613; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 22px;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background .2s;
        }
        .btn-red { background: #e30613; color: #fff; }
        .btn-red:hover { background: #c00510; }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; padding: 10px 12px; background: #f8f8f8; font-weight: 600; border-bottom: 2px solid #eee; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; vertical-align: top; }
        tr:hover td { background: #fafafa; }
        .badge-pdf {
            display: inline-block;
            background: #e30613;
            color: #fff;
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: 600;
        }
        .badge-pdf a { color: #fff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-logo">
            <div class="topbar-badge">PAT</div>
            <span class="topbar-title">Panel de Sistemas</span>
        </div>
        <a href="{{ route('publico.index') }}">← Ver sitio público</a>
    </div>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-label">Administración</div>
            <a href="{{ route('sistemas.carreras.index') }}" class="{{ request()->routeIs('sistemas.carreras.*') ? 'active' : '' }}">
                📚 Carreras
            </a>
            <a href="{{ route('sistemas.ia.index') }}" class="{{ request()->routeIs('sistemas.ia.*') ? 'active' : '' }}">
                🤖 Sistema Inteligente
            </a>
        </aside>

        <main class="main">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>

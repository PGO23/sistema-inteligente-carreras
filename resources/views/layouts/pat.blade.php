<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Universidad PAT')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; color: #1a1a1a; background: #f5f5f5; }
        a { text-decoration: none; color: inherit; }

        .header {
            background: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo-badge {
            background: #e30613;
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            padding: 8px 12px;
            letter-spacing: 1px;
        }
        .logo-text { color: #fff; font-size: 0.85rem; line-height: 1.3; }
        .logo-text strong { display: block; font-size: 1rem; }
        .nav { display: flex; gap: 1.5rem; }
        .nav a { color: #fff; font-size: 0.9rem; font-weight: 500; opacity: 0.9; transition: opacity .2s; }
        .nav a:hover { opacity: 1; }
        .nav a.active { color: #e30613; opacity: 1; }

        .hero {
            position: relative;
            height: 420px;
            background: linear-gradient(135deg, #1a1a1a 0%, #333 50%, #e30613 100%);
            display: flex;
            align-items: flex-end;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1400&q=80') center/cover;
            opacity: 0.35;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            padding: 2.5rem 2rem;
            width: 100%;
        }
        .hero-slogan {
            display: inline-block;
            background: #e30613;
            color: #fff;
            font-weight: 800;
            font-size: 1.6rem;
            padding: 12px 24px;
            letter-spacing: 0.5px;
        }
        .hero-sub {
            background: #111;
            color: #fff;
            font-size: 0.95rem;
            padding: 10px 24px;
            display: inline-block;
            max-width: 500px;
        }

        .section { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        .section-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #111;
        }
        .section-title span { color: #e30613; }
        .section-sub { color: #666; margin-bottom: 2rem; }

        .carreras-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        .carrera-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            transition: transform .2s, box-shadow .2s;
        }
        .carrera-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .carrera-img {
            height: 180px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .carrera-img::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 60px;
            background: linear-gradient(transparent, rgba(0,0,0,.5));
        }
        .carrera-body { padding: 1.25rem; }
        .carrera-body h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; }
        .carrera-body p { color: #555; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem; }
        .carrera-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border: none;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
            font-family: inherit;
        }
        .btn:active { transform: scale(0.98); }
        .btn-red { background: #e30613; color: #fff; }
        .btn-red:hover { background: #c00510; }
        .btn-dark { background: #111; color: #fff; }
        .btn-dark:hover { background: #333; }
        .btn-outline { background: transparent; color: #e30613; border: 2px solid #e30613; }
        .btn-outline:hover { background: #e30613; color: #fff; }

        .floating-bar {
            position: fixed;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            z-index: 90;
        }
        .float-btn {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
            border: none;
        }
        .float-red { background: #e30613; }
        .float-green { background: #25d366; }
        .float-dark { background: #111; }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.6);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #fff;
            border-radius: 8px;
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            animation: slideUp .3s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            background: #e30613;
            color: #fff;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 { font-size: 1.2rem; font-weight: 700; }
        .modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            line-height: 1;
        }
        .modal-body { padding: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #e30613;
            box-shadow: 0 0 0 3px rgba(227,6,19,.15);
        }
        .form-group input[readonly] { background: #f0f0f0; color: #555; }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .footer {
            background: #111;
            color: #aaa;
            text-align: center;
            padding: 1.5rem;
            font-size: 0.85rem;
            margin-top: 2rem;
        }
        .footer strong { color: #e30613; }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
        .empty-state h3 { font-size: 1.2rem; margin-bottom: 0.5rem; color: #555; }

        .ml-recommendations {
            background: linear-gradient(135deg, #111 0%, #1f1f1f 100%);
            border-radius: 12px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 2rem;
            color: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
        }
        .ml-rec-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 1.25rem;
        }
        .ml-rec-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #e30613;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .ml-rec-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .ml-rec-subtitle {
            font-size: 0.85rem;
            color: #bbb;
            line-height: 1.4;
        }
        .ml-rec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
        }
        .ml-rec-card {
            background: #fff;
            color: #1a1a1a;
            border-radius: 10px;
            padding: 1rem 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ml-rec-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .ml-rec-card h4 {
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
            margin: 0;
        }
        .ml-rec-badge {
            background: #fce8ea;
            color: #e30613;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .ml-rec-bar-wrap {
            height: 8px;
            background: #eee;
            border-radius: 999px;
            overflow: hidden;
        }
        .ml-rec-bar {
            height: 100%;
            background: linear-gradient(90deg, #e30613, #ff4d58);
            border-radius: 999px;
            transition: width .4s ease;
        }
        .ml-rec-meta {
            font-size: 0.78rem;
            color: #666;
        }
        .ml-rec-metodo {
            display: inline-block;
            margin-top: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 999px;
            background: #f0f0f0;
            color: #444;
        }
        .ml-rec-metodo--hibrido {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .ml-rec-metodo--arbol {
            background: #e3f2fd;
            color: #1565c0;
        }
        .ml-rec-metodo--patrones {
            background: #fff3e0;
            color: #e65100;
        }
        .ml-rec-metodo--experto {
            background: #e8eaf6;
            color: #283593;
        }
        .ml-rec-metodo--logistico {
            background: #e0f2f1;
            color: #00695c;
        }
        .ml-rec-metodo--mlp {
            background: #fce4ec;
            color: #ad1457;
        }
        .ml-rec-metodo--kmeans {
            background: #f1f8e9;
            color: #558b2f;
        }
        .ml-rec-metodo--multi_agente {
            background: #f3e5f5;
            color: #6a1b9a;
        }
        .ml-rec-btn {
            margin-top: auto;
            width: 100%;
            padding: 9px 12px;
            border: none;
            border-radius: 6px;
            background: #111;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background .2s;
        }
        .ml-rec-btn:hover { background: #e30613; }
        .ml-rec-footer {
            margin-top: 1rem;
            font-size: 0.78rem;
            color: #999;
            text-align: center;
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="header">
        <a href="{{ route('publico.index') }}" class="logo">
            <div class="logo-badge">PAT</div>
            <div class="logo-text">
                <strong>Universidad PAT</strong>
                Perú · Formación profesional
            </div>
        </a>
        <nav class="nav">
            <a href="{{ route('publico.index') }}" class="{{ request()->routeIs('publico.*') ? 'active' : '' }}">Carreras</a>
            <a href="#">Admisión</a>
            <a href="#">Campus</a>
            <a href="{{ route('sistemas.carreras.index') }}">Sistemas</a>
        </nav>
    </header>

    @yield('content')

    <div class="floating-bar">
        <button class="float-btn float-red" onclick="openModal()" title="Recibir información">✉</button>
        <a href="https://wa.me/51999999999" target="_blank" class="float-btn float-green" title="WhatsApp">💬</a>
        <button class="float-btn float-dark" title="Menú">☰</button>
    </div>

    <footer class="footer">
        © {{ date('Y') }} <strong>Universidad PAT</strong> — Todos los derechos reservados
    </footer>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cacafly Interview')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f2f5;
            color: #1c1e21;
            min-height: 100vh;
        }

        nav {
            background: #fff;
            border-bottom: 1px solid #dddfe2;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }

        nav .brand { font-weight: 700; font-size: 1.4rem; color: #1877f2; }

        nav .nav-links { display: flex; align-items: center; gap: 16px; }

        nav a { text-decoration: none; color: #606770; font-size: 0.95rem; }
        nav a:hover { color: #1877f2; }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 18px; border-radius: 6px; border: none;
            font-size: 0.95rem; font-weight: 600; cursor: pointer;
            text-decoration: none; transition: filter .15s;
        }
        .btn:hover { filter: brightness(0.93); }
        .btn-primary   { background: #1877f2; color: #fff; }
        .btn-secondary { background: #e4e6eb; color: #1c1e21; }
        .btn-danger    { background: #fa3e3e; color: #fff; }
        .btn-sm        { padding: 5px 12px; font-size: 0.85rem; }

        .container { max-width: 900px; margin: 0 auto; padding: 32px 16px; }

        .card {
            background: #fff; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08); padding: 28px;
        }

        .card + .card { margin-top: 24px; }

        .alert {
            padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;
            font-size: 0.95rem;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
    @stack('styles')
</head>
<body>

@auth
<nav>
    <span class="brand">Cacafly Demo</span>
    <div class="nav-links">
        <a href="{{ route('dashboard') }}">個人頁面</a>
        <a href="{{ route('upload.index') }}">圖片上傳</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm">登出</button>
        </form>
    </div>
</nav>
@endauth

<div class="container">
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @yield('content')
</div>

@stack('scripts')
<script>
    // Remove Facebook's legacy #_=_ fragment from URL
    if (window.location.hash === '#_=_') {
        history.replaceState(null, '', window.location.pathname + window.location.search);
    }
</script>
</body>
</html>

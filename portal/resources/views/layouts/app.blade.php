<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Traitor.dev')</title>
    <link rel="stylesheet" href="/css/portal.css">
</head>
<body>
    <header class="portal-header">
        <div class="container">
            <a href="/" class="logo">traitor<span>.dev</span></a>
            <nav>
                <a href="/sites">Sites</a>
            </nav>
        </div>
    </header>

    <main class="portal-main">
        <div class="container">
            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="portal-footer">
        <div class="container">
            <p>Traitor.dev — websites managed by AI</p>
        </div>
    </footer>

    <script src="/js/portal.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en" class="{{ (request()->has('embedded') || request()->has('embed')) ? 'is-embedded' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NRCA Projects')</title>

    <!-- Dynamic embed detection (executes before paint to prevent layout flash) -->
    <script>
        (function() {
            try {
                var params = new URLSearchParams(window.location.search);
                if (params.has('standalone')) {
                    document.documentElement.classList.remove('is-embedded');
                    document.documentElement.classList.add('is-standalone');
                } else if (params.has('embedded') || params.has('embed') || window.self !== window.top) {
                    document.documentElement.classList.add('is-embedded');
                    document.documentElement.classList.remove('is-standalone');
                } else {
                    document.documentElement.classList.add('is-standalone');
                }
            } catch (e) {
                document.documentElement.classList.add('is-embedded');
            }
        })();
    </script>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- App CSS (Original styles) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.6"></script>
</head>
<body>
    <header class="site-header standalone-only mb-3">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('projects.index') }}" class="site-title-link text-decoration-none">
                <h1 class="site-title">NRCA Environmental Action Projects</h1>
            </a>
            @auth
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white-50 small d-none d-md-inline">{{ auth()->user()->email ?? auth()->user()->name }}</span>
                    <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size: 0.8rem;">Log Out</a>
                </div>
            @endauth
        </div>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

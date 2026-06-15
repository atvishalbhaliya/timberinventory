<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Timber Inventory')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components.css') }}" rel="stylesheet">
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
</head>
<body>
    <main class="container py-5">
        @yield('content')
    </main>
    @vite('resources/js/app.js')

    <script>
        // If user is already authenticated, redirect to dashboard
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token');
            if (token) {
                window.location.href = '/dashboard';
            }
        });
    </script>
</body>
</html>

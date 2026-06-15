<!doctype html>
<html lang="en" data-theme-color="purple" data-sidebar-theme="dark" data-header-theme="light" data-dark-mode="light" data-layout-mode="standard" data-card-style="material" data-sidebar-state="expanded">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Timber Inventory')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|poppins:500,600,700|nunito:400,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="erp-shell">
        @include('partials.sidebar')
        @include('partials.header')
        <main class="erp-main">
            <div class="erp-content">
                @yield('content')
            </div>
            @include('partials.footer')
        </main>
    </div>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @stack('scripts')

    <script>
        // Check authentication on page load
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '/login';
            }
        });
    </script>
</body>
</html>

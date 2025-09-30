<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Employee Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/employee-ess-clean.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/simple-modal-fix.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
</head>
<body>

    <!-- Employee Topbar -->
    @include('employee_ess_modules.partials.employee_topbar')

    <!-- Employee Sidebar -->
    @include('employee_ess_modules.partials.employee_sidebar')

    <!-- Overlay for mobile -->
    <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

    <!-- Main Content -->
    <main id="main-content">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/simple-modal-fix.js') }}"></script>
    @yield('scripts')
</body>
</html>

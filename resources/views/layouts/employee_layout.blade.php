<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Employee Portal') - Jetlouge Travels</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/employee_style.css') }}" rel="stylesheet">
    
    <style>
        
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 250px; background: #2c3e50; color: white; transform: translateX(-100%); transition: transform 0.3s ease; z-index: 1000; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; transition: margin-left 0.3s ease; }
        .main-content.shifted { margin-left: 250px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; display: none; }
        .overlay.active { display: block; }
        @media (min-width: 768px) { .sidebar { transform: translateX(0); } .main-content { margin-left: 250px; } .overlay { display: none !important; } }
    </style>

  @stack('styles')
</head>
<body style="background-color: #f8f9fa !important;">

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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'HR System')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Custom HR Styles -->
  <link href="{{ asset('assets/css/hr-style.css') }}" rel="stylesheet">
  <!-- Universal Modal Fix -->
  <link href="{{ asset('assets/css/modal-universal-fix.css') }}" rel="stylesheet">
  
  @stack('styles')
  
</head>
<body style="background-color: #ffffff !important;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--jetlouge-primary);">
    <div class="container-fluid">
      <button class="sidebar-toggle desktop-toggle me-3" id="desktop-toggle" title="Toggle Sidebar">
        <i class="bi bi-list fs-5"></i>
      </button>
      <a class="navbar-brand fw-bold" href="/admin/dashboard">
        <i class="bi bi-airplane me-2"></i>Jetlouge Travels
      </a>
      <div class="d-flex align-items-center gap-3">
        <!-- HR System Settings Dropdown -->
        <div class="dropdown">
          <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="hrSettingsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-cog me-2"></i>
            <span>Settings</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="hrSettingsDropdown">
            <li>
              <h6 class="dropdown-header">
                <i class="fas fa-user me-2"></i>{{ Auth::check() ? Auth::user()->name : 'Admin' }}
              </h6>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="#" id="system-settings-link">
                <i class="fas fa-cogs me-2"></i>System Settings
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="#" id="hr-preferences-link">
                <i class="fas fa-sliders-h me-2"></i>HR Preferences
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="#" id="notifications-settings-link">
                <i class="fas fa-bell me-2"></i>Notifications
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to logout?')">
                  <i class="fas fa-sign-out-alt me-2"></i>Log Out
                </button>
              </form>
            </li>
          </ul>
        </div>
        <button class="sidebar-toggle mobile-toggle" id="menu-btn" title="Open Menu">
          <i class="bi bi-list fs-5"></i>
        </button>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside id="sidebar" class="bg-white border-end p-3 shadow-sm">
    <!-- Profile Section -->
    <div class="profile-section text-center">
      <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face"
           alt="Admin Profile" class="profile-img mb-2">
      <h6 class="fw-semibold mb-1">{{ Auth::user()->name ?? 'John Anderson' }}</h6>
      <small class="text-muted">HR Administrator</small>
    </div>

    <!-- Navigation Menu -->
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link text-dark {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('timesheet.index') }}" class="nav-link text-dark {{ request()->routeIs('timesheet.*') ? 'active' : '' }}">
          <i class="fas fa-clock me-2"></i> Timesheet
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('shift-schedule-management') }}" class="nav-link text-dark {{ request()->routeIs('shift-schedule-management') ? 'active' : '' }}">
          <i class="fas fa-calendar-alt me-2"></i> Shift & Schedule
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('leave.index') }}" class="nav-link text-dark {{ request()->routeIs('leave.*') ? 'active' : '' }}">
          <i class="fas fa-umbrella-beach me-2"></i> Leave Management
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('claims.index') }}" class="nav-link text-dark {{ request()->routeIs('claims.*') ? 'active' : '' }}">
          <i class="fas fa-file-invoice-dollar me-2"></i> Claims & Reimbursement
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('employees') }}" class="nav-link text-dark {{ request()->routeIs('employees.*') ? 'active' : '' }}">
          <i class="bi bi-people me-2"></i> Employees
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link text-dark">
          <i class="bi bi-gear me-2"></i> Settings
        </a>
      </li>
      <li class="nav-item mt-3">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-link text-danger btn btn-link p-0 w-100 text-start" style="text-decoration:none;">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </button>
        </form>
      </li>
    </ul>
  </aside>

  <!-- Overlay for mobile -->
  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1030; display: none;"></div>

  <!-- Main Content -->
  <main id="main-content">
    @yield('content')
  </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Universal Modal Initialization -->
    <script src="{{ asset('assets/js/modal-universal-init.js') }}"></script>
    <!-- HR Database Integration -->
    <script src="{{ asset('assets/js/hr-database-integration.js') }}"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      const desktopToggle = document.getElementById('desktop-toggle');
      const mainContent = document.getElementById('main-content');

      // Desktop toggle
      if (desktopToggle) {
        desktopToggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          sidebar.classList.toggle('collapsed');
          mainContent.classList.toggle('expanded');
          
          // Show/hide overlay for mobile
          if (window.innerWidth <= 768) {
            if (overlay) {
              if (sidebar.classList.contains('collapsed')) {
                overlay.style.display = 'none';
              } else {
                overlay.style.display = 'block';
              }
            }
          }
        });
      }

      // Close sidebar when clicking overlay
      if (overlay) {
        overlay.addEventListener('click', function() {
          sidebar.classList.add('collapsed');
          mainContent.classList.add('expanded');
          overlay.style.display = 'none';
        });
      }

      // Handle window resize
      window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
          if (overlay) {
            overlay.style.display = 'none';
          }
        }
      });
    });
  </script>
  
  @stack('scripts')
</body>
</html>
